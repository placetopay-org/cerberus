<?php

namespace Placetopay\Cerberus\Tests\Tasks;

use Illuminate\Support\Facades\DB;
use Placetopay\Cerberus\Models\Tenant;
use Placetopay\Cerberus\Tests\TestCase;
use Spatie\Multitenancy\Exceptions\InvalidConfiguration;

class SwitchTenantDatabaseTest extends TestCase
{
    protected Tenant $tenant;

    protected Tenant $anotherTenant;

    public function setUp(): void
    {
        parent::setUp();

        $this->tenant = factory(Tenant::class)->create([
            'app' => config('multitenancy.identifier'),
            'name' => 'tenant_1',
            'config' => $this->getConfigStructure('laravel_mt_tenant_1'),
        ]);

        $this->anotherTenant = factory(Tenant::class)->create([
            'app' => config('multitenancy.identifier'),
            'name' => 'tenant_2',
            'config' => $this->getConfigStructure('laravel_mt_tenant_2'),
        ]);
    }

    /** @test */
    public function switch_fails_if_tenant_database_connection_name_equals_to_landlord_connection_name()
    {
        config()->set('multitenancy.tenant_database_connection_name', null);

        $this->expectException(InvalidConfiguration::class);

        $this->tenant->makeCurrent();
    }

    /** @test */
    public function when_making_a_tenant_current_it_will_perform_the_tasks()
    {
        $this->assertNull(DB::connection('tenant')->getDatabaseName());

        $this->tenant->makeCurrent();

        $this->assertEquals('laravel_mt_tenant_1', DB::connection('tenant')->getDatabaseName());

        $this->anotherTenant->makeCurrent();

        $this->assertEquals('laravel_mt_tenant_2', DB::connection('tenant')->getDatabaseName());

        Tenant::forgetCurrent();

        $this->assertNull(DB::connection('tenant')->getDatabaseName());
    }
}
