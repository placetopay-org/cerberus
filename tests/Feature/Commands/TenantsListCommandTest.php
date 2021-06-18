<?php

namespace Placetopay\Cerberus\Tests\Feature\Commands;

use Placetopay\Cerberus\Models\Tenant;
use Placetopay\Cerberus\Tests\TestCase;

class TenantsListCommandTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        factory(Tenant::class)->create([
            'app' => config('multitenancy.identifier'),
            'name' => 'tenant_1',
            'domain' => 'co.domain.com',
            'config' => $this->getConfigStructure('laravel_mt_tenant_1'),
        ]);

        factory(Tenant::class)->create([
            'app' => 'other',
            'name' => 'tenant_2',
            'domain' => 'pr.domain.com',
            'config' => $this->getConfigStructure('laravel_mt_tenant_2'),
        ]);
    }

    /** @test */
    public function it_can_list_the_tenencies_of_the_app()
    {
        $this
            ->artisan('tenants:list')
            ->assertExitCode(0)
            ->expectsOutput('Listing all tenants (1).')
            ->expectsOutput('[Tenant] domain: co.domain.com @ tenant_1');
    }
}
