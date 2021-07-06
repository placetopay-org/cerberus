<?php

namespace Placetopay\Cerberus\Tests\Feature\Commands;

use Illuminate\Support\Facades\Schema;
use Placetopay\Cerberus\Models\Tenant;
use Placetopay\Cerberus\Tests\TestCase;

class TenantsArtisanCommandTest extends TestCase
{
    protected Tenant $tenant;

    protected Tenant $anotherTenant;

    public function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'tenant']);

        $this->tenant = factory(Tenant::class)->create([
            'app' => config('multitenancy.identifier'),
            'name' => 'tenant_1',
            'domain' => 'co.domain.com',
            'config' => $this->getConfigStructure('laravel_mt_tenant_1'),
        ]);
        $this->tenant->makeCurrent();

        Schema::connection('tenant')->dropIfExists('migrations');

        $this->anotherTenant = factory(Tenant::class)->create([
            'app' => config('multitenancy.identifier'),
            'name' => 'tenant_2',
            'domain' => 'pr.domain.com',
            'config' => $this->getConfigStructure('laravel_mt_tenant_2'),
        ]);
        $this->anotherTenant->makeCurrent();
        Schema::connection('tenant')->dropIfExists('migrations');

        Tenant::forgetCurrent();
    }

    /** @test */
    public function it_can_migrate_all_tenant_databases()
    {
        $this
            ->artisan('tenants:artisan migrate')
            ->assertExitCode(0);

        $this
            ->assertTenantDatabaseHasTable($this->tenant, 'migrations')
            ->assertTenantDatabaseHasTable($this->anotherTenant, 'migrations');
    }

    /** @test */
    public function it_can_migrate_a_specific_tenant()
    {
        $this->artisan('tenants:artisan migrate --tenant=' . $this->anotherTenant->domain . '"')
            ->assertExitCode(0);

        $this
            ->assertTenantDatabaseDoesNotHaveTable($this->tenant, 'migrations')
            ->assertTenantDatabaseHasTable($this->anotherTenant, 'migrations');
    }

    /** @test */
    public function it_cant_migrate_a_specific_tenant_id_when_search_by_domain()
    {
        $this->artisan('tenants:artisan migrate --tenant=' . $this->anotherTenant->name . '"')
            ->expectsOutput('No tenant(s) found.');
    }

    /** @test */
    public function it_can_migrate_a_specific_tenant_by_domain()
    {
        $this->artisan('tenants:artisan migrate --tenant=' . $this->anotherTenant->domain . '"')->assertExitCode(0);

        $this
            ->assertTenantDatabaseDoesNotHaveTable($this->tenant, 'migrations')
            ->assertTenantDatabaseHasTable($this->anotherTenant, 'migrations');
    }

    protected function assertTenantDatabaseHasTable(Tenant $tenant, string $tableName): self
    {
        $tenantHasDatabaseTable = $this->tenantHasDatabaseTable($tenant, $tableName);

        $this->assertTrue($tenantHasDatabaseTable, "Tenant database does not have table  `{$tableName}`");

        return $this;
    }

    protected function assertTenantDatabaseDoesNotHaveTable(Tenant $tenant, string $tableName): self
    {
        $tenantHasDatabaseTable = $this->tenantHasDatabaseTable($tenant, $tableName);

        $this->assertFalse($tenantHasDatabaseTable, "Tenant database has unexpected table  `{$tableName}`");

        return $this;
    }

    protected function tenantHasDatabaseTable(Tenant $tenant, string $tableName): bool
    {
        $tenant->makeCurrent();

        $tenantHasDatabaseTable = Schema::connection('tenant')->hasTable($tableName);

        Tenant::forgetCurrent();

        return $tenantHasDatabaseTable;
    }
}
