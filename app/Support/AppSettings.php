<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class AppSettings
{
    public const CACHE_KEY = 'app.settings';

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = static::all();

        return data_get($all, $key, $default);
    }

    public static function set(string $key, mixed $value): void
    {
        Setting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value],
        );

        static::flush();
    }

    /**
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        return Cache::remember(static::CACHE_KEY, 3600, function () {
            return Setting::query()
                ->pluck('value', 'key')
                ->all();
        });
    }

    public static function flush(): void
    {
        Cache::forget(static::CACHE_KEY);
    }
}
