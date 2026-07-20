<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * key/value ဆက်တင် သိုလှောင်မှု (ဆိုင်နာမည်၊ logo၊ လိပ်စာ စသည်)။
 * အားလုံးကို cache တစ်ခုတည်းဖြင့် ဖတ်သည်။
 */
class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    private const CACHE_KEY = 'app_settings';

    /** key => value array တစ်ခုလုံး (cache) */
    public static function cached(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return static::query()->pluck('value', 'key')->toArray();
        });
    }

    public static function get(string $key, $default = null)
    {
        $val = static::cached()[$key] ?? null;

        return ($val === null || $val === '') ? $default : $val;
    }

    public static function put(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget(self::CACHE_KEY);
    }

    /** key => value array တစ်ခုလုံး တစ်ကြိမ်တည်း သိမ်း */
    public static function putMany(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        Cache::forget(self::CACHE_KEY);
    }
}
