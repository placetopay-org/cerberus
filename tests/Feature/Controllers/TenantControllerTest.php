<?php

namespace Placetopay\Cerberus\Tests\Feature\Controllers;

use Placetopay\Cerberus\Tests\TestCase;

class TenantControllerTest extends TestCase
{
    /** @test */
    public function it_can_access_to_clean_cache_ok()
    {
        config(['multitenancy.cache_middleware_key' => 'app-key123234']);

        $data = [
            'key' => config('multitenancy.cache_middleware_key'),
        ];
        $this->post(route('app.clean', $data))
            ->assertOk()
            ->assertJson([
                'message' => 'cache cleared',
            ]);
    }
}
