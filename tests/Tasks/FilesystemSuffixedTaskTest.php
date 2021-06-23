<?php

namespace Placetopay\Cerberus\Tests\Tasks;

use Placetopay\Cerberus\Models\Tenant;
use Placetopay\Cerberus\Tests\TestCase;

class FilesystemSuffixedTaskTest extends TestCase
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
    public function it_set_suffix_by_tenant_ok()
    {
        $originalStoragePath = storage_path();
        $this->assertStringNotContainsString($this->tenant->name, $originalStoragePath);
        $this->assertStringNotContainsString($this->anotherTenant->name, $originalStoragePath);

        $this->tenant->makeCurrent();
        $this->assertStringContainsString($this->tenant->name, storage_path());

        $this->anotherTenant->makeCurrent();
        $this->assertStringContainsString($this->anotherTenant->name, storage_path());
    }

    /** @test */
    public function it_forget_suffix_by_tenant_ok()
    {
        $this->tenant->makeCurrent();
        $this->assertStringContainsString($this->tenant->name, storage_path());
        $this->tenant->forget();

        $this->assertStringNotContainsString($this->tenant->name, storage_path());

        $this->anotherTenant->makeCurrent();
        $this->assertStringContainsString($this->anotherTenant->name, storage_path());
        $this->anotherTenant->forget();

        $this->assertStringNotContainsString($this->anotherTenant->name, storage_path());
    }
}
