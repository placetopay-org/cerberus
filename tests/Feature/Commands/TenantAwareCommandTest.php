<?php

namespace Placetopay\Cerberus\Tests\Feature\Commands;

use Placetopay\Cerberus\Models\Tenant;
use Placetopay\Cerberus\Tasks\SwitchTenantDatabaseTask;
use Placetopay\Cerberus\Tests\TestCase;

class TenantAwareCommandTest extends TestCase
{
    protected Tenant $tenant;

    protected Tenant $anotherTenant;

    public function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'tenant']);

        config()->set('multitenancy.switch_tenant_tasks', [SwitchTenantDatabaseTask::class]);

        $this->tenant = factory(Tenant::class)->create([
            'app' => config('multitenancy.identifier'),
            'name' => 'tenant_1',
            'config' => $this->getConfigStructure('laravel_mt_tenant_1'),
        ]);

        $this->tenant->makeCurrent();

        $this->anotherTenant = factory(Tenant::class)->create([
            'app' => config('multitenancy.identifier'),
            'name' => 'tenant_2',
            'config' => $this->getConfigStructure('laravel_mt_tenant_2'),
        ]);
        $this->anotherTenant->makeCurrent();

        Tenant::forgetCurrent();
    }

    /** @test */
    public function it_fails_with_a_not_existent_tenant()
    {
        $this
            ->artisan('tenant:noop --tenant=unknown')
            ->assertExitCode(-1)
            ->expectsOutput('No tenant(s) found.');
    }

    /** @test */
    public function it_prints_the_right_tenant()
    {
        $this
            ->artisan('tenant:noop --tenant=tenant_1')
            ->assertExitCode(0)
            ->expectsOutput('Tenant ID is ' . $this->tenant->id);
    }

    /** @test */
    public function it_works_with_no_tenant_parameters()
    {
        $this
            ->artisan('tenant:noop')
            ->assertExitCode(0)
            ->expectsOutput('Tenant ID is ' . $this->tenant->id)
            ->expectsOutput('Tenant ID is ' . $this->anotherTenant->id);
    }
}
