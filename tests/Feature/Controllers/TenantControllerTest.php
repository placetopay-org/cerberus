<?php

namespace Placetopay\Cerberus\Tests\Feature\Controllers;

use Illuminate\Support\Facades\Cache;
use Placetopay\Cerberus\Models\Tenant;
use Placetopay\Cerberus\Tests\TestCase;

class TenantControllerTest extends TestCase
{
    /** @test */
    public function it_can_access_to_clean_cache_ok()
    {
        $key = 'app-key123234';
        config(['multitenancy.middleware_key' => $key]);

        $data = [
            'action' => 'cache:clear',
        ];

        $signature = hash_hmac('sha256', json_encode($data), $key);

        $tenant = factory(Tenant::class)->create([
            'app' => config('multitenancy.identifier'),
            'name' => 'tenant_1',
            'domain' => 'co.domain.com',
        ]);

        $tenant->makeCurrent();

        Cache::put("tenant_{$tenant->domain}", $tenant);

        $this->post(route('app.clean'), $data, ['Signature' => $signature])
            ->assertOk()
            ->assertJson([
                'message' => 'cache cleared',
            ]);
    }
}
