<?php

namespace Placetopay\Cerberus\Tests\Feature\Commands;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Placetopay\Cerberus\Models\Tenant;
use Placetopay\Cerberus\Tests\TestCase;

class TenantsSkeletonStorageCommandTest extends TestCase
{
    protected Tenant $tenant;

    protected Tenant $anotherTenant;

    public function setUp(): void
    {
        parent::setUp();

        config()->set('multitenancy.suffix_storage_path', true);

        $this->tenant = factory(Tenant::class)->create([
            'app' => config('multitenancy.identifier'),
            'name' => 'co',
            'domain' => 'co.domain.com',
            'config' => $this->getConfigStructure('laravel_mt_tenant_1'),
        ]);

        $this->anotherTenant = factory(Tenant::class)->create([
            'app' => config('multitenancy.identifier'),
            'name' => 'pr',
            'domain' => 'pr.domain.com',
            'config' => $this->getConfigStructure('laravel_mt_tenant_2'),
        ]);

        File::deleteDirectory(storage_path('tenants'));
    }

    #[Test]
    public function it_create_the_folder_for_the_correct_tenant()
    {
        $path = storage_path('tenants/co/app/public');

        $this
            ->artisan('tenants:skeleton-storage --tenant=co.domain.com')
            ->assertExitCode(0)
            ->expectsOutput("[Tenant: {$this->tenant->domain}] {$path} folder created.");

        $this->assertTrue(File::exists($path));
    }

    #[Test]
    public function it_fails_with_a_not_existent_tenant()
    {
        $this
            ->artisan('tenants:skeleton-storage --tenant=unknown')
            ->assertExitCode(-1)
            ->expectsOutput('No tenant(s) found.');
    }

    #[Test]
    public function it_works_with_no_tenant_parameters()
    {
        $coPath = storage_path('tenants/co/app/public');
        $prPath = storage_path('tenants/pr/app/public');

        $this
            ->artisan('tenants:skeleton-storage')
            ->assertExitCode(0)
            ->expectsOutput("[Tenant: {$this->tenant->domain}] {$coPath} folder created.")
            ->expectsOutput("[Tenant: {$this->anotherTenant->domain}] {$prPath} folder created.");

        $this->assertTrue(File::exists($coPath));
        $this->assertTrue(File::exists($prPath));
    }

    #[Test]
    public function it_fails_creating_folders_if_the_option_is_not_enabled()
    {
        config()->set('multitenancy.suffix_storage_path', false);

        $this
            ->artisan('tenants:skeleton-storage --tenant=co.domain.com')
            ->assertExitCode(1)
            ->expectsOutput('Storage tenancies are not enabled.');
    }

    #[Test]
    public function fails_to_create_existing_folders()
    {
        $path = storage_path('tenants/co/app/public');

        File::makeDirectory($path, 0755, true);

        $this
            ->artisan('tenants:skeleton-storage --tenant=co.domain.com')
            ->assertExitCode(0)
            ->expectsOutput("[Tenant: {$this->tenant->domain}] No folder to create.");
    }
}
