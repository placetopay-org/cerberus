<?php

namespace Placetopay\Cerberus\Cache;

use Illuminate\Support\Facades\Redis;

class RedisHandler implements CacheHandler
{
    public function clear(string $prefix = ''): int
    {
        $generalPrefix = config('database.redis.options.prefix', '');

        $redis = Redis::connection('cache');

        $prefix = str_replace($generalPrefix, '', $prefix);
        $keys = $redis->keys("$prefix*");

        foreach ($keys as $key) {
            $redis->del(str_replace($generalPrefix, '', $key));
        }

        return count($keys);
    }
}
