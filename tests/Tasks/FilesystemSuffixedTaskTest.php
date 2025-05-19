<?php

namespace Placetopay\Cerberus\Tests\Tasks;

use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Placetopay\Cerberus\Models\Tenant;
use Placetopay\Cerberus\Tests\TestCase;

class FilesystemSuffixedTaskTest extends TestCase
{
    protected Tenant $tenant;

    protected Tenant $anotherTenant;

    public function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create([
            'app' => config('multitenancy.identifier'),
            'name' => 'tenant_1',
            'config' => $this->getConfigStructure('laravel_mt_tenant_1'),
        ]);

        $this->anotherTenant = Tenant::factory()->create([
            'app' => config('multitenancy.identifier'),
            'name' => 'tenant_2',
            'config' => $this->getConfigStructure('laravel_mt_tenant_2'),
        ]);
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
    #[DataProvider('filesystemDisksUrlDataProvider')]
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

    #[Test]
    public function it_forget_filesystem_disk_url_suffix()
    {
        $originalAppUrl = 'https://tenant.test';
        $config = array_merge($this->tenant->config, ['app' => ['url' => $originalAppUrl]]);
        $originalDiskUrl = $originalAppUrl.'/storage/';
        $this->tenant->update(['config' => $config]);
        $disks = [
            'public' => ['driver' => 'local', 'url' => $originalDiskUrl, 'root' => 'fake/storage'],
        ];
        config()->set('filesystems.disks', $disks);
        $this->tenant->makeCurrent();

        $this->assertSame('https://tenant.test/storage/tenants/tenant_1/', Storage::disk('public')->url(''));

        /** Forget current tenant */
        $this->tenant->forget();
        $this->assertSame($originalDiskUrl, Storage::disk('public')->url(''));
    }

    public static function filesystemDisksUrlDataProvider(): array
    {
        return [
            'tenant_without_trailing_slash' => [
                'appUrl' => 'https://tenant.test',
                'storageUrl' => 'https://tenant_1.test/storage',
                'expectedUrl' => 'https://tenant.test/storage/tenants/tenant_1/',
            ],
            'tenant_with_trailing_slash' => [
                'appUrl' => 'https://tenant_2.test/',
                'storageUrl' => 'https://tenant_2.test/storage/',
                'expectedUrl' => 'https://tenant_2.test/storage/tenants/tenant_1/',
            ],
        ];
    }
}
