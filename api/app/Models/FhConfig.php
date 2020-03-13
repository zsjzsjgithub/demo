<?php
/**
 * 业务配置
 */

namespace App\Models;

use App\Setting;
use Illuminate\Support\Facades\Cache;

class FhConfig
{
    /** 缓存前缀 */
    const CACHE_KEY = 'fhconfig';

    public static function reset()
    {
        $setting = Setting::where('id', 1)->first();
        if ($setting) {
            Cache::forever(self::CACHE_KEY, $setting->values);
            return $setting->values;
        }

        return [];
    }

    /**
     * @param string|array $key
     * @param $value
     */
    public static function set($key, $value = null)
    {
        if (is_array($key)) {
            $values = $key;
        } else {
            $values = self::get();
            $keys = explode('.', $key);
            $tmp = &$values;
            foreach ($keys as $k) {
                $tmp = &$tmp[$k];
            }
            $tmp = $value;
        }

        Setting::updateOrCreate(['id' => 1], ['values' => $values]);

        self::reset();
    }

    public static function get(string $key = '', $default = null)
    {
        if (Cache::has(self::CACHE_KEY)) {
            $settings = Cache::get(self::CACHE_KEY);
        } else {
            $settings = self::reset();
        }

        if (!$settings) {
            return $default;
        }

        if (empty($key)) {
            return $settings;
        }

        $keys = explode('.', $key);

        $value = $settings;
        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $default;
            }
        }

        return $value;
    }

}
