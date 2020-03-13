<?php
/**
 * 交易场次
 *
 * -
 */

namespace App\Models;

use App\Server\Util;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class Scene
{
    /** 状态：交易开放 */
    const STATUS_OPEN = 1;

    /** 状态：交易关闭 */
    const STATUS_CLOSE = 2;

    /** 状态：进行中 */
    const STATUS_START = 3;

    /** 配置：场次数量 */
    const CONFIG_COUNT = 3;

    /** 配置：场次间隔（分钟） */
    const CONFIG_INTERVAL = 6;

    /**
     * 获取所有场次
     *
     * @return \Illuminate\Support\Collection
     */
    public static function all()
    {
        $interval = self::CONFIG_INTERVAL;
        $count = self::CONFIG_COUNT;
        $data = collect();
        $time = Carbon::now()->startOfMinute();
        $time->minute = floor($time->minute / $interval) * $interval;
        for ($i = 0; $i < $count; $i++) {
            $price = '';
            $type = 0;
            $sceneTime = $time->copy();

            // 判断当前场次状态
            if ($sceneTime->lte(Carbon::now())) {
                $status = self::STATUS_START;
                $key = Util::CACHE_KEY_FIRST_SCENE . $sceneTime->timestamp;
                if (Cache::has($key)) {
                    $tmp_data = Cache::get($key);
                    $price = $tmp_data['price'];
                    $type = $tmp_data['type'];
                }
            } elseif ($sceneTime->lte(Carbon::now()->addMinute())) {
                $status = self::STATUS_CLOSE;
            } else {
                $status = self::STATUS_OPEN;
            }

            $data->push([
                'time' => $sceneTime->toDateTimeString(),
                'timestamp' => $sceneTime->timestamp,
                'status' => $status,
                'long' => $sceneTime->diff(Carbon::now())->format('%i:%S'),
                'price' => $price,
                'type' => $type,
            ]);
            $time->addMinute($interval);
        }
        return $data;
    }

}
