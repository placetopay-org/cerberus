<?php

namespace Placetopay\Cerberus\Cache;

class CacheHandlerFactory
{
    public static function getHandler(string $driver): CacheHandler|null
    {
        return match ($driver) {
            'redis' => app(RedisHandler::class),
            default => null
        };
    }
}
