<?php

namespace Placetopay\Cerberus\Tests\Unit;

use Illuminate\Http\Request;
use Placetopay\Cerberus\Http\Exceptions\UnAuthorizedActionException;
use Placetopay\Cerberus\Http\Middlewares\AppCache;
use Placetopay\Cerberus\Tests\TestCase;

class AppCacheMiddlewareTest extends TestCase
{
    /** @test */
    public function it_can_access_to_clean_cache_ok()
    {
        config(['multitenancy.cache_middleware_key' => 'app-key123234']);

        $data = [
            'key' => config('multitenancy.cache_middleware_key'),
        ];
        $this->get(route('app.clean', $data))
            ->assertOk()
            ->assertJson([
                'message' => 'cache cleared',
            ]);
    }

    /** @test */
    public function conf_key_is_not_configured()
    {
        config(['multitenancy.cache_middleware_key' => '']);

        $request = new Request();

        $middleware = (new AppCache());
        $this->expectException(UnAuthorizedActionException::class);
        $this->expectExceptionMessage($middleware::EMPTY_CONFIG_KEY);
        $middleware->handle($request, fn ($request) => $request);
    }

    /** @test */
    public function it_can_not_authenticate()
    {
        config(['multitenancy.cache_middleware_key' => 'app-key123234']);
        $request = new Request();

        $middleware = (new AppCache());
        $this->expectException(UnAuthorizedActionException::class);
        $this->expectExceptionMessage($middleware::UN_AUTHORIZED);
        $middleware->handle($request, fn ($request) => $request);
    }
}
