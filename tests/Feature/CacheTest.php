<?php

namespace Placetopay\Cerberus\Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Placetopay\Cerberus\Models\Tenant;
use Placetopay\Cerberus\Tasks\SwitchTenantTask;
use Placetopay\Cerberus\TenantFinder\DomainTenantFinder;
use Placetopay\Cerberus\Tests\TestCase;

class CacheTest extends TestCase
{
    private DomainTenantFinder $tenantFinder;

    protected Tenant $tenant;

    public function setUp(): void
    {
        parent::setUp();

        $this->tenantFinder = new DomainTenantFinder();

        config(['database.default' => 'tenant']);

        config()->set('multitenancy.switch_tenant_tasks', [SwitchTenantTask::class]);

        $this->tenant = factory(Tenant::class)->create([
            'app' => config('multitenancy.identifier'),
            'name' => 'tenant_1',
            'domain' => 'co.domain.com',
            'config' => $this->getConfigStructure('laravel_mt_tenant_1'),
        ]);

        $this->tenant->makeCurrent();

        Tenant::forgetCurrent();
    }

    /** @test */
    public function it_print_the_correct_tenant_checking_cache()
    {
        $this
            ->artisan('tenant:noop --tenant=co.domain.com')
            ->assertExitCode(0)
            ->expectsOutput('Tenant ID is ' . $this->tenant->id)
            ->expectsOutput('Tenant Config is ' . $this->tenant->getRawOriginal('config'));

        $this->assertHasCache();

        Tenant::query()->find($this->tenant->id)->update(['config' => $this->getConfigStructure('laravel_mt_tenant_3')]);

        $this
            ->artisan('tenant:noop --tenant=co.domain.com')
            ->assertExitCode(0)
            ->expectsOutput('Tenant ID is ' . $this->tenant->id)
            ->expectsOutput('Tenant Config is ' . $this->tenant->getRawOriginal('config'));
    }

    /** @test */
    public function it_can_find_a_tenant_for_the_current_domain_checking_cache()
    {
        $request = Request::create(sprintf('https://%s', $this->tenant->domain));

        $this->assertEquals($this->tenant->id, $this->tenantFinder->findForRequest($request)->id);

        $this->assertHasCache();

        Tenant::query()->find($this->tenant->id)->update(['config' => $this->getConfigStructure('laravel_mt_tenant_2')]);

        $this->assertEquals($this->tenant->config, $this->tenantFinder->findForRequest($request)->config);
    }

    /** @test */
    public function it_can_cache_domain_via_tenant_finder()
    {
        $request = Request::create(sprintf('https://%s', $this->tenant->domain));

        $this->assertNotNull($this->tenantFinder->findForRequest($request));

        $this->assertHasCache();

        Tenant::query()->find($this->tenant->id)->update(['config' => $this->getConfigStructure('laravel_mt_tenant_2')]);

        $this
            ->artisan('tenant:noop --tenant=co.domain.com')
            ->assertExitCode(0)
            ->expectsOutput('Tenant ID is ' . $this->tenant->id)
            ->expectsOutput('Tenant Config is ' . $this->tenant->getRawOriginal('config'));
    }

    /** @test */
    public function it_can_cache_domain_via_tenant_aware()
    {
        $this
            ->artisan('tenant:noop --tenant=co.domain.com')
            ->assertExitCode(0)
            ->expectsOutput('Tenant ID is ' . $this->tenant->id)
            ->expectsOutput('Tenant Config is ' . $this->tenant->getRawOriginal('config'));

        $this->assertHasCache();

        Tenant::query()->find($this->tenant->id)->update(['config' => $this->getConfigStructure('laravel_mt_tenant_2')]);

        $request = Request::create(sprintf('https://%s', $this->tenant->domain));
        $this->assertEquals($this->tenant->config, $this->tenantFinder->findForRequest($request)->config);
    }

    private function assertHasCache(): void
    {
        $identifier = config('multitenancy.identifier');
        $tenantCache = Cache::tags(["multitenancy_{$identifier}"]);

        $this->assertTrue($tenantCache->has("tenant_{$this->tenant->domain}"));
    }
}
