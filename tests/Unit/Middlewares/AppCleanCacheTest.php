<?php

namespace Placetopay\Cerberus\Tests\Unit\Middlewares;

use Illuminate\Http\Request;
use Placetopay\Cerberus\Http\Exceptions\UnAuthorizedActionException;
use Placetopay\Cerberus\Http\Middlewares\AppCleanCache;
use Placetopay\Cerberus\Tests\TestCase;

class AppCleanCacheTest extends TestCase
{
    /** @test */
    public function conf_key_is_not_configured()
    {
        $signature = $this->getSignature('');

        $request = new Request();

        $middleware = (new AppCleanCache());
        $this->expectException(UnAuthorizedActionException::class);
        $this->expectExceptionMessage($middleware::EMPTY_CONFIG_KEY);
        $middleware->handle($request, fn ($request) => $request);
    }

    /** @test */
    public function it_can_not_authenticate()
    {
        $signature = $this->getSignature('app-key123234');

        $request = new Request();
        $request->headers->set('Signature', $signature);

        $middleware = (new AppCleanCache());
        $this->expectException(UnAuthorizedActionException::class);
        $this->expectExceptionMessage($middleware::UN_AUTHORIZED);
        $middleware->handle($request, fn ($request) => $request);
    }

    private function getSignature($key): string
    {
        config(['multitenancy.middleware_key' => $key]);

        $data = [
            'action' => 'cache:clear',
        ];

        return hash_hmac('sha256', json_encode($data), (string) $key);
    }
}
