<?php

namespace Placetopay\Cerberus\Tests\Feature\Commands;

use PHPUnit\Framework\Attributes\Test;
use Placetopay\Cerberus\Models\Tenant;
use Placetopay\Cerberus\Tasks\SwitchTenantTask;
use Placetopay\Cerberus\Tests\TestCase;
use Spatie\Multitenancy\Contracts\IsTenant;

class TenantAwareCommandTest extends TestCase
{
    protected Tenant $tenant;

    protected Tenant $anotherTenant;

    public function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'tenant']);

        config()->set('multitenancy.switch_tenant_tasks', [SwitchTenantTask::class]);

        $this->tenant = Tenant::factory()->create([
            'app' => config('multitenancy.identifier'),
            'name' => 'tenant_1',
            'domain' => 'co.domain.com',
            'config' => $this->getConfigStructure('laravel_mt_tenant_1'),
        ]);

        $this->tenant->makeCurrent();

        $this->anotherTenant = Tenant::factory()->create([
            'app' => config('multitenancy.identifier'),
            'name' => 'tenant_2',
            'domain' => 'pr.domain.com',
            'config' => $this->getConfigStructure('laravel_mt_tenant_2'),
        ]);
        $this->anotherTenant->makeCurrent();

        app(IsTenant::class)::forgetCurrent();
    }

    #[Test]
    public function it_fails_with_a_not_existent_tenant()
    {
        $this
            ->artisan('tenant:noop --tenant=unknown')
            ->assertExitCode(-1)
            ->expectsOutput('No tenant(s) found.');
    }

    #[Test]
    public function it_prints_the_right_tenant()
    {
        $this
            ->artisan('tenant:noop --tenant=co.domain.com')
            ->assertExitCode(0)
            ->expectsOutput('Tenant ID is '.$this->tenant->id);
    }

    #[Test]
    public function it_works_with_no_tenant_parameters()
    {
        $this
            ->artisan('tenant:noop')
            ->assertExitCode(0)
            ->expectsOutput('Tenant ID is '.$this->tenant->id)
            ->expectsOutput('Tenant ID is '.$this->anotherTenant->id);
    }
}
