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
        config(['multitenancy.cache_middleware_key' => '']);

        $request = new Request();

        $middleware = (new AppCleanCache());
        $this->expectException(UnAuthorizedActionException::class);
        $this->expectExceptionMessage($middleware::EMPTY_CONFIG_KEY);
        $middleware->handle($request, fn ($request) => $request);
    }

    /** @test */
    public function it_can_not_authenticate()
    {
        config(['multitenancy.cache_middleware_key' => 'app-key123234']);
        $request = new Request();

        $middleware = (new AppCleanCache());
        $this->expectException(UnAuthorizedActionException::class);
        $this->expectExceptionMessage($middleware::UN_AUTHORIZED);
        $middleware->handle($request, fn ($request) => $request);
    }
}
