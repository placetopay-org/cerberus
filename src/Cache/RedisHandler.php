<?php

namespace Placetopay\Cerberus\Cache;

use Illuminate\Support\Facades\Redis;

class RedisHandler implements CacheHandler
{
    public function clear(string $prefix = ''): void
    {
        $generalPrefix = config('database.redis.options.prefix', '');

        $redis = Redis::connection('cache');

        $prefix = ltrim($prefix, $generalPrefix);
        $keys = $redis->keys("$prefix*");

        foreach ($keys as $key) {
            $redis->del(ltrim($key, $generalPrefix));
        }
    }
}
