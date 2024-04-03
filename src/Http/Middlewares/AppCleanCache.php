<?php

namespace Placetopay\Cerberus\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Placetopay\Cerberus\Http\Exceptions\UnAuthorizedActionException;

class AppCleanCache
{
    protected array $allowedActions = [
        'cache:clear',
    ];

    public const EMPTY_CONFIG_KEY = "You must configure the variable 'multitenancy.middleware_key' to perform this action";

    public const UN_AUTHORIZED = 'You are not authorized to perform this action';

    public function handle(Request $request, Closure $next)
    {
        if (! config('multitenancy.middleware_key')) {
            $this->unAuthorized(self::EMPTY_CONFIG_KEY);
        }

        if (! $this->canClearCache($request) || ! $this->allowedAction($request)) {
            $this->unAuthorized();
        }

        return $next($request);
    }

    private function canClearCache(Request $request): bool
    {
        $data = [
            'action' => $request->input('action'),
        ];

        $signature = hash_hmac('sha256', json_encode($data), config('multitenancy.middleware_key'));

        return $signature == $request->header('Signature');
    }

    /**
     * @throws UnAuthorizedActionException
     */
    private function unAuthorized(string $message = null)
    {
        throw new UnAuthorizedActionException($message ?? self::UN_AUTHORIZED, 401);
    }

    private function allowedAction(Request $request): bool
    {
        return in_array($request->input('action'), $this->allowedActions);
    }
}
