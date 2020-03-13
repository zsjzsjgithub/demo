<?php
/**
 * mock数据
 */

namespace App\Server;

use Carbon\Carbon;
use Hhxsv5\LaravelS\Swoole\Timer\CronJob;
use Illuminate\Support\Facades\Cache;

class MockCronJob extends CronJob
{
    const CACHE_KEY = 'forex:mock_price_';

    protected $isImmediate = true;

    protected $interval = 1000;

    public function run()
    {
        // 模拟价格变化
        if (!Cache::has(self::CACHE_KEY)) {
            $price = rand(181000, 187000);
        } else {
            $price = Cache::get(self::CACHE_KEY);
        }
        $price += rand(-4, 4);
        Cache::put(self::CACHE_KEY, $price, 10);

        // 生成mock数据
        $time = Carbon::now()->startOfMinute()->timestamp;
        $key = self::CACHE_KEY . $time;
        $float_price = $price / 100000;
        if (!Cache::has($key)) {
            $data = [
                'time' => $time,
                'low' => $float_price,
                'high' => $float_price,
                'open' => $float_price,
                'close' => $float_price,
            ];
        } else {
            $data = Cache::get($key);
            $data['low'] = min($data['low'], $float_price);
            $data['high'] = max($data['high'], $float_price);
            $data['close'] = $float_price;
        }

        Cache::put($key, $data, 3);
    }
}
