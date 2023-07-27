<?php

namespace Placetopay\Cerberus\Tasks;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Storage;
use Spatie\Multitenancy\Models\Tenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

class FilesystemSuffixedTask implements SwitchTenantTask
{
    private array $originalPaths;

    public function __construct(private Application $app)
    {
        $this->originalPaths = [
            'disks' => [],
            'storage' => $this->app->storagePath(),
            'asset_url' => $this->app['config']['app.asset_url'],
        ];

        $this->app['url']->macro('setAssetRoot', function ($root) {
            $this->assetRoot = $root;

            return $this;
        });
    }

    public function makeCurrent(Tenant $tenant): void
    {
        $suffix = "tenants/$tenant->name";

        // storage_path()
        if (config('multitenancy.suffix_storage_path')) {
            $this->app->useStoragePath($this->originalPaths['storage']."/$suffix");
        }

        Storage::forgetDisk(config('multitenancy.filesystems_disks'));
        // Storage facade
        foreach (config('multitenancy.filesystems_disks') as $disk) {
            if (! $this->canSuffixS3Driver($disk)) {
                continue;
            }

            $originalRoot = config("filesystems.disks.$disk.root");
            $this->originalPaths['disks'][$disk] = $originalRoot;

            $finalPrefix = $originalRoot
                    ? rtrim((string) $originalRoot, '/').'/'.$suffix
                    : $suffix;

            $this->app['config']["filesystems.disks.$disk.root"] = $finalPrefix;
        }
    }

    public function forgetCurrent(): void
    {
        // storage_path()
        if (config('multitenancy.suffix_storage_path')) {
            $this->app->useStoragePath($this->originalPaths['storage']);
        }

        Storage::forgetDisk(config('multitenancy.filesystems_disks'));
        // Storage facade
        foreach (config('multitenancy.filesystems_disks') as $disk) {
            if (! $this->canSuffixS3Driver($disk) || ! array_key_exists($disk, $this->originalPaths['disks'])) {
                continue;
            }

            $root = $this->originalPaths['disks'][$disk];
            $this->app['config']["filesystems.disks.$disk.root"] = $root;
        }
    }

    public function canSuffixS3Driver(string $driver): bool
    {
        if ($driver == 's3' && config('filesystems.disks.s3.region') == null) {
            return false;
        }

        return true;
    }
}
