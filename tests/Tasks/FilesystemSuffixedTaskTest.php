<?php

namespace Placetopay\Cerberus\Tests\Tasks;

use Illuminate\Support\Facades\Storage;
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
        $originalStoragePath = Storage::path('tests');
        $this->assertStringNotContainsString($this->tenant->name, $originalStoragePath);
        $this->assertStringNotContainsString($this->anotherTenant->name, $originalStoragePath);

        $this->tenant->makeCurrent();
        $this->assertStringContainsString($this->tenant->name, Storage::path('tests'));

        $this->anotherTenant->makeCurrent();
        $this->assertStringContainsString($this->anotherTenant->name, Storage::path('tests'));
    }

    /** @test */
    public function it_forget_suffix_by_tenant_ok()
    {
        $this->tenant->makeCurrent();
        $this->assertStringContainsString($this->tenant->name, Storage::path('tests'));
        $this->tenant->forget();

        $this->assertStringNotContainsString($this->tenant->name, Storage::path('tests'));

        $this->anotherTenant->makeCurrent();
        $this->assertStringContainsString($this->anotherTenant->name, Storage::path('tests'));
        $this->anotherTenant->forget();

        $this->assertStringNotContainsString($this->anotherTenant->name, Storage::path('tests'));
    }

    /** @test */
    public function it_overwrite_storage_path()
    {
        config()->set('multitenancy.suffix_storage_path', true);
        $originalStoragePath = storage_path();

        $this->assertStringNotContainsString($this->tenant->name, $originalStoragePath);

        $this->tenant->makeCurrent();
        $this->assertStringContainsString($this->tenant->name, storage_path());
        $this->tenant->forget();

        $this->assertEquals($originalStoragePath, storage_path());
        $this->assertStringNotContainsString($this->tenant->name, $originalStoragePath);
    }

    /**
     * @test
     * @dataProvider filesystemDisksUrlDataProvider
     */
    public function it_overwrite_filesystem_disks_url($appUrl, $storageUrl, $expectedUrl)
    {
        $config = array_merge($this->tenant->config, ['app' => ['url' => $appUrl]]);
        $this->tenant->update(['config' => $config]);

        $disks = [
            'local' => ['driver' => 'local', 'url' => $storageUrl, 'root' => 'fake/storage'],
            'public' => ['driver' => 'local', 'url' => $storageUrl, 'root' => 'fake/storage'],
        ];

        config()->set('filesystems.disks', $disks);
        $this->tenant->makeCurrent();

        $this->assertSame($expectedUrl, Storage::disk('local')->url(''));
        $this->assertSame($expectedUrl, Storage::disk('public')->url(''));
    }

    public static function filesystemDisksUrlDataProvider(): array
    {
        return [
            'tenant_without_trailing_slash' => [
                'app_url' => 'https://tenant.test',
                'storage_url' => 'https://tenant_1.test/storage',
                'expected_url' => 'https://tenant.test/storage/tenants/tenant_1/',
            ],
            'tenant_with_trailing_slash' => [
                'app_url' => 'https://tenant_2.test/',
                'storage_url' => 'https://tenant_2.test/storage/',
                'expected_url' => 'https://tenant_2.test/storage/tenants/tenant_1/',
            ],
        ];
    }
}
