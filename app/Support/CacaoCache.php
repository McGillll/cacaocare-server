<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

class CacaoCache
{
    private const VERSION_KEY = 'cacao_cache_version';

    public static function remember(string $key, int $ttlSeconds, Closure $callback)
    {
        return Cache::remember(self::versionedKey($key), $ttlSeconds, $callback);
    }

    public static function flush(): void
    {
        Cache::forever(self::VERSION_KEY, self::version() + 1);
    }

    private static function version(): int
    {
        return Cache::get(self::VERSION_KEY, 1);
    }

    private static function versionedKey(string $key): string
    {
        return 'cacao:v'.self::version().':'.$key;
    }
}
