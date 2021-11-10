<?php

namespace Placetopay\Cerberus\Tasks;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Storage;
use Spatie\Multitenancy\Models\Tenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

class FilesystemSuffixedTask implements SwitchTenantTask
{
    private Application $app;

    private array $originalPaths = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
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
            $this->app->useStoragePath($this->originalPaths['storage'] . "/{$suffix}");
        }

        // Storage facade
        foreach (config('multitenancy.filesystems_disks') as $disk) {
            if (!$this->canSuffixS3Driver($disk)) {
                continue;
            }

            /** @var FilesystemAdapter $filesystemDisk */
            if ($filesystemDisk = Storage::disk($disk)) {
                $this->originalPaths['disks'][$disk] = $filesystemDisk->getAdapter()->getPathPrefix();

                $root = $this->app['config']["filesystems.disks.{$disk}.root"];
                $filesystemDisk->getAdapter()->setPathPrefix($finalPrefix = $root . "/{$suffix}");
                $this->app['config']["filesystems.disks.{$disk}.root"] = $finalPrefix;
            }
        }
    }

    public function forgetCurrent(): void
    {
        // storage_path()
        if (config('multitenancy.suffix_storage_path')) {
            $this->app->useStoragePath($this->originalPaths['storage']);
        }

        // Storage facade
        foreach (config('multitenancy.filesystems_disks') as $disk) {
            if (!$this->canSuffixS3Driver($disk) || !array_key_exists($disk, $this->originalPaths['disks'])) {
                continue;
            }

            /** @var FilesystemAdapter $filesystemDisk */
            $filesystemDisk = Storage::disk($disk);

            $root = $this->originalPaths['disks'][$disk];

            $filesystemDisk->getAdapter()->setPathPrefix($root);
            $this->app['config']["filesystems.disks.{$disk}.root"] = $root;
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
