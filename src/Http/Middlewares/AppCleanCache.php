<?php

namespace Placetopay\Cerberus\Http\Middlewares;

use Closure;
use Placetopay\Cerberus\Http\Exceptions\UnAuthorizedActionException;

class AppCleanCache
{
    public const EMPTY_CONFIG_KEY = "You must configure the variable 'multitenancy.cache_middleware_key' to perform this action";
    public const UN_AUTHORIZED = 'You are not authorized to perform this action';

    public function handle($request, Closure $next)
    {
        if (!config('multitenancy.cache_middleware_key')) {
            $this->unAuthorized(self::EMPTY_CONFIG_KEY);
        }

        if (!$this->canClearCache($request->input('key'))) {
            $this->unAuthorized();
        }

        return $next($request);
    }

    private function canClearCache($key): bool
    {
        return config('multitenancy.cache_middleware_key') == $key;
    }

    /**
     * @throws UnAuthorizedActionException
     */
    private function unAuthorized(string $message = null)
    {
        throw new UnAuthorizedActionException($message ?? self::UN_AUTHORIZED, 401);
    }
}
