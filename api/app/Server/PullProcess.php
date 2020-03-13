<?php
/**
 * 拉取数据进程
 */

namespace App\Server;

use App\AccountRecord;
use App\ForexData;
use App\Models\FhConfig;
use App\Models\Scene;
use App\Order;
use Carbon\Carbon;
use Hhxsv5\LaravelS\Swoole\Process\CustomProcessInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Swoole\Http\Server;
use Swoole\Process;

class PullProcess implements CustomProcessInterface
{
    /**
     * The name of process
     *
     * @return string
     */
    public static function getName()
    {
        return 'pull forex';
    }

    /**
     * The run callback of process
     *
     * @param Server $swoole
     * @param Process $process
     *
     * @return void
     */
    public static function callback(Server $swoole, Process $process)
    {
        $pull_interval = config('server.pull_interval');

        // 刷新配置缓存
        FhConfig::reset();

        while (true) {
            $start = microtime(true) * 1000;

            // 获取数据
            $data = Util::getData();

            [$first, $last] = $data;

            if (!Util::isForex($last)) {
                Log::channel('pull')->error('data error', ['data' => $data]);
                self::sleep($start);
                continue;
            }

            $time = Carbon::createFromTimestamp($last['time']);
            // 如果超过两分钟没有数据，则判定为休市
            if ($time->lte(Carbon::now()->subMinutes(2))) {
                self::sleep($start);
                continue;
            }

            // 缓存实时数据，第二条数据为当前的实时数据，缓存到redis
            Cache::put(Util::CACHE_KEY_DATA . Util::TYPE_GBPAUD, [
                'time' => Carbon::createFromTimestamp($last['time'])->toDateTimeString(),
                'open' => sprintf('%.5f', $last['open']),
                'close' => sprintf('%.5f', $last['close']),
                'high' => sprintf('%.5f', $last['high']),
                'low' => sprintf('%.5f', $last['low']),
            ], 10);

            // 保存外汇数据，第一条为前一分钟的历史数据，原则上不会变动，但实际会有更新。结算之后就算有更新也不更新，以免结算出错
            if (Util::isForex($first)) {
                $time = Carbon::createFromTimestamp($first['time']);
                if ($time->timestamp !== Util::getLastTime()) {
                    Cache::put(Util::CACHE_KEY_LAST_TIME, $time->timestamp, 10);
                    $first['type'] = Util::TYPE_GBPAUD;
                    DB::transaction(function () use ($first, $time) {
                        ForexData::firstOrCreate(['time' => $first['time']], $first);
                        self::scene();
                        self::settle();
                        Log::channel('pull')->info('inserted', ['time' => $time->toDateTimeString()]);
                    });
                }
            }

            $long = (int) (microtime(true) * 1000 - $start);
            Log::channel('pull')->info($long . ' ms.');
            if ($long < $pull_interval) {
                usleep(($pull_interval - $long) * 1000);
            }
        }

        // 如果拉取进程意外停止，则停止所有服务，等待重启
        $swoole->shutdown();
    }

    /**
     * Whether redirect stdin/stdout
     *
     * @return bool
     */
    public static function isRedirectStdinStdout()
    {
        return false;
    }

    /**
     * The type of pipeline
     * 0: no pipeline
     * 1: \SOCK_STREAM
     * 2: \SOCK_DGRAM
     *
     * @return int
     */
    public static function getPipeType()
    {
        return 0;
    }

    /**
     * Trigger this method on receiving the signal SIGUSR1
     *
     * @param Server $swoole
     * @param Process $process
     *
     */
    public static function onReload(Server $swoole, Process $process)
    {
    }

    /**
     * 休市
     *
     * @param int $start
     */
    private static function sleep($start)
    {
        Cache::put(Util::CACHE_KEY_DATA . Util::TYPE_GBPAUD, [
            'time' => '',
            'open' => '',
            'close' => '',
            'high' => '',
            'low' => '',
        ], 10);

        Log::channel('pull')->info('sleep');

        $pull_interval = config('server.pull_interval');
        $long = (int) (microtime(true) * 1000 - $start);
        if ($long < $pull_interval) {
            usleep(($pull_interval - $long) * 1000);
        }
    }

    /**
     * 更新进行中场次信息
     */
    private static function scene()
    {
        $need_put = false; // 是否需要存储缓存
        $scene = Scene::all()->first();
        $scene_time = Carbon::createFromTimestamp($scene['timestamp']);
        $key = Util::CACHE_KEY_FIRST_SCENE . $scene_time->timestamp;

        if (!Cache::has($key)) {
            /** @var ForexData $forex */
            $forex = ForexData::where('time', $scene_time)->first();
            if (!$forex) {
                return;
            }
            $data = [
                'price' => $forex->open,
                'type' => 0, // 0.无结果 其他同Order::TYPE_*
            ];
            $need_put = true;
        } else {
            $data = Cache::get($key);
        }

        if ($data['type'] == 0) {
            $forexDatas = ForexData::where('time', '>=', $scene_time)->orderBy('time')->get();
            if ($forexDatas->isNotEmpty()) {
                $range = fhget('trade.range', 0.0004);
                $isBuy = false;
                $isSell = false;
                /** @var ForexData $forexData */
                foreach ($forexDatas as $forexData) {
                    $prices = [$forexData->open, $forexData->close, $forexData->high, $forexData->low];
                    $high = max($prices);
                    $low = min($prices);

                    if ($high - $data['price'] >= $range) {
                        $isBuy = true;
                    }

                    if ($data['price'] - $low >= $range) {
                        $isSell = true;
                    }

                    if ($isBuy || $isSell) {
                        break;
                    }
                }

                if ($isBuy && $isSell) {
                    $type = Order::TYPE_CANCEL;
                } elseif ($isBuy) {
                    $type = Order::TYPE_BUY;
                } elseif ($isSell) {
                    $type = Order::TYPE_SELL;
                }

                if (isset($type)) {
                    $need_put = true;
                    $data['type'] = $type;
                }
            }
        }

        $need_put && Cache::put($key, $data, 10);
    }

    /**
     * 结算
     */
    private static function settle()
    {
        $orders = Order::where('status', Order::STATUS_PENDING)
            ->where('scene_time', '<=', Carbon::now())
            ->orderBy('scene_time')
            ->get();
        if ($orders->isEmpty()) {
            return;
        }

        // 获取需要的汇率数据
        /** @var Order $firstOrder */
        $firstOrder = $orders->first();
        $forexDatas = ForexData::where('time', '>=', $firstOrder->scene_time)->orderBy('time')->get();
        if ($forexDatas->isEmpty()) {
            return;
        }

        $forexList = [];
        /** @var ForexData $forexData */
        foreach ($forexDatas as $forexData) {
            $forexList[$forexData->time->timestamp] = $forexData;
        }

        // 处理开盘价
        $needOpenOrders = $orders->filter(function (Order $order) {
            return !$order->open;
        });
        if ($needOpenOrders->isNotEmpty()) {
            $needOpenOrders->map(function (Order $order) use ($forexList) {
                if (isset($forexList[$order->scene_time->timestamp])) {
                    /** @var ForexData $forexData */
                    $forexData = $forexList[$order->scene_time->timestamp];
                    $order->open = $forexData->open;
                    $order->save();
                    Log::channel('pull')->info('save open price', ['id' => $order->id, 'price' => $forexData->open]);
                }
            });
        }

        // 计算结果
        $range = fhget('trade.range', 0.0004);
        $orders->map(function (Order $order) use ($forexList, $range) {
            $isBuy = false;
            $isSell = false;
            $settleForexData = null;
            /** @var ForexData $forexData */
            foreach ($forexList as $forexData) {
                if ($forexData->time->lt($order->scene_time) || !$order->open) {
                    continue;
                }

                $prices = [$forexData->open, $forexData->close, $forexData->high, $forexData->low];
                $high = max($prices);
                $low = min($prices);

                if ($high - $order->open >= $range) {
                    $isBuy = true;
                }

                if ($order->open - $low >= $range) {
                    $isSell = true;
                }

                if ($isSell || $isBuy) {
                    $settleForexData = $forexData;
                    break;
                }
            }

            if ($isBuy && $isSell) {
                $type = Order::TYPE_CANCEL;
            } elseif ($isBuy) {
                $type = Order::TYPE_BUY;
            } elseif ($isSell) {
                $type = Order::TYPE_SELL;
            } else {
                return;
            }

            if ($type === $order->type) {
                // 赢
                $order->status = Order::STATUS_WIN;

                // 更新客户余额
                $amount = round($order->amount * $order->rate, 2);
                $member = $order->member;
                $member->balance += $amount;
                $member->save();

                // 生成交易记录
                $record = new AccountRecord([
                    'amount' => $amount,
                    'type' => AccountRecord::TYPE_INCOME,
                    'balance' => $member->balance,
                    'status' => AccountRecord::STATUS_COMPLETED,
                ]);
                $record->member()->associate($member);
                $record->save();
                Log::channel('pull')->info('settled', ['id' => $order->id, 'result' => 'win']);
            } else {
                // 输
                $order->status = Order::STATUS_LOSE;
                Log::channel('pull')->info('settled', ['id' => $order->id, 'result' => 'lose']);
            }
            $order->forex_data()->associate($settleForexData);
            $order->save();
        });
    }
}
