<?php
/**
 * 工具方法
 */

namespace App\Server;

use App\AccountRecord;
use App\ForexData;
use App\Message;
use App\User;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Util
{
    /** 缓存前缀 */
    const CACHE_PREFIX = 'forex:';

    /** 缓存Key：实时数据 */
    const CACHE_KEY_DATA = self::CACHE_PREFIX . 'real_time_data_';

    /** 缓存Key：当前连接用户列表 */
    const CACHE_KEY_USER = self::CACHE_PREFIX . 'socket_user';

    /** 缓存Key：最后记录数据时间 */
    const CACHE_KEY_LAST_TIME = self::CACHE_PREFIX . 'timestamp';

    /** 缓存Key：进行中的场次信息 */
    const CACHE_KEY_FIRST_SCENE = self::CACHE_PREFIX . 'first_scene_';

    /** 汇率类型：GBPAUD */
    const TYPE_GBPAUD = 1;

    /** 汇率类型信息 */
    const TYPE_INFO = [
        self::TYPE_GBPAUD => [
            'tag' => 'GBPAUD',
            'label' => 'GBP/AUD',
        ],
    ];

    private static $guzzleClient;

    /**
     * 获取在线用户列表
     *
     * @param int $user_type
     *
     * @return array
     */
    public static function userList(int $user_type = 0)
    {
        if (Cache::has(self::CACHE_KEY_USER)) {
            $data = Cache::get(self::CACHE_KEY_USER);
        }

        // 如果列表数据不合法则重置
        if (!isset($data) || !is_array($data)) {
            isset($data) && Log::channel('ws')->error('用户列表数据不合法，已经重置', ['data' => $data]);
            Cache::forever(self::CACHE_KEY_USER, []);
            return [];
        }

        if ($user_type) {
            return array_filter($data, function ($d) use ($user_type) {
                return $d['user_type'] == $user_type;
            });
        }

        return $data;
    }

    /**
     * 获取在线用户fd
     *
     * @param int $user_id
     * @return int
     */
    public static function userFd(int $user_id)
    {
        if (Cache::has(self::CACHE_KEY_USER)) {
            $data = Cache::get(self::CACHE_KEY_USER);
        }

        // 如果列表数据不合法则重置
        if (!isset($data) || !is_array($data)) {
            isset($data) && Log::channel('ws')->error('用户列表数据不合法，已经重置', ['data' => $data]);
            Cache::forever(self::CACHE_KEY_USER, []);
            return 0;
        }

        foreach ($data as $fd => $datum) {
            if ($datum['id'] == $user_id) {
                return (int) $fd;
            }
        }

        return 0;
    }

    /**
     * 获取实时汇率
     *
     * @return array
     */
    public static function forex()
    {
        $key = Util::CACHE_KEY_DATA . Util::TYPE_GBPAUD;
        if (Cache::has($key)) {
            $data = Cache::get($key);
            return $data ?: [];
        }
        return [];
    }

    /**
     * 判断数据合法性
     *
     * @param $data
     *
     * @return bool
     */
    public static function isForex($data)
    {
        return $data && isset($data['time'], $data['open'], $data['close'], $data['high'], $data['low']);
    }

    /**
     * 获取最后记录时间
     *
     * @return int
     */
    public static function getLastTime()
    {
        return (int) Cache::remember(self::CACHE_KEY_LAST_TIME, 2, function () {
            $forexData = ForexData::orderByDesc('id')->first();
            if ($forexData) {
                return $forexData->time->timestamp;
            }
            return 0;
        });
    }

    public static function getData()
    {
        // 获取mock数据
        if (config('server.pull_mock')) {
            $last_key = MockCronJob::CACHE_KEY . Carbon::now()->startOfMinute()->timestamp;
            // 判断是否有当前分钟数据
            if (!Cache::has($last_key)) {
                // 如果当前分钟没有数据，则获取前一分钟的数据
                $last_key = MockCronJob::CACHE_KEY . Carbon::now()->subMinute()->startOfMinute()->timestamp;
                $first_key = MockCronJob::CACHE_KEY . Carbon::now()->subMinutes(2)->startOfMinute()->timestamp;
            } else {
                $first_key = MockCronJob::CACHE_KEY . Carbon::now()->subMinute()->startOfMinute()->timestamp;
            }

            if (Cache::has($first_key)) {
                $first = Cache::get($first_key);
            } else {
                $first = [];
            }

            if (Cache::has($last_key)) {
                $last = Cache::get($last_key);
            } else {
                $last = [];
            }

            return [$first, $last];
        }

        if (!self::$guzzleClient) {
            self::$guzzleClient = new Client();
        }

        try {
            $response = self::$guzzleClient->get(config('server.pull_url'));
            $data = json_decode($response->getBody()->getContents(), true);
        } catch (Exception $e) {
            Log::channel('pull')->error('Failed to get data', ['errMsg' => $e->getMessage()]);
            return [[], []];
        }

        if (count($data) !== 2) {
            Log::channel('pull')->error('Failed to get data', ['data' => $data]);
            return [[], []];
        }

        return $data;
    }

    public static function getTopData()
    {
        // 存款
        $record = AccountRecord::where('type', AccountRecord::TYPE_DEPOSIT)
            ->whereIn('status', [AccountRecord::STATUS_PENDING, AccountRecord::STATUS_WAITING])
            ->selectRaw('COUNT(id) num,status')
            ->groupBy('status')
            ->pluck('num', 'status');
        $deposit = [
            'pending' => $record[1] ?? 0,
            'waitting' => $record[4] ?? 0,
            'completed' => AccountRecord::where('type', AccountRecord::TYPE_DEPOSIT)
                ->where('status', AccountRecord::STATUS_COMPLETED)
                ->whereDate('updated_at', Carbon::today())
                ->count(),
        ];

        // 取款
        $record = AccountRecord::where('type', AccountRecord::TYPE_WITHDRAWAL)
            ->whereIn('status', [AccountRecord::STATUS_PENDING, AccountRecord::STATUS_WAITING])
            ->selectRaw('COUNT(id) num,status')
            ->groupBy('status')
            ->pluck('num', 'status');
        $withdrawal = [
            'pending' => $record[1] ?? 0,
            'waitting' => $record[4] ?? 0,
            'completed' => AccountRecord::where('type', AccountRecord::TYPE_WITHDRAWAL)
                ->where('status', AccountRecord::STATUS_COMPLETED)
                ->whereDate('updated_at', Carbon::today())
                ->count(),
        ];

        // 提问
        $question = Message::where('type', Message::TYPE_SERVICE)
            ->where('has_question', true)
            ->count();

        // 新会员
        $member = [
            'new' => User::where('type', User::TYPE_MEMBER)
                ->whereDate('created_at', Carbon::today())
                ->count(),
            'online' => count(array_unique(array_column(Util::userList(User::TYPE_MEMBER), 'id'))),
        ];

        return compact('deposit', 'withdrawal', 'question', 'member');
    }
}
