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
        $suffix = $tenant->name;

        // storage_path()
        $this->app->useStoragePath($this->originalPaths['storage'] . "/tenant/{$suffix}");

        // asset()
        if ($this->originalPaths['asset_url']) {
            $this->app['config']['app.asset_url'] = ($this->originalPaths['asset_url'] ?? $this->app['config']['app.url']) . "/$suffix";
            $this->app['url']->setAssetRoot($this->app['config']['app.asset_url']);
        }

        // Storage facade
        foreach (config('multitenancy.filesystems_disks') as $disk) {
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
        $this->app->useStoragePath($this->originalPaths['storage']);

        // asset()
        $this->app['config']['app.asset_url'] = $this->originalPaths['asset_url'];
        $this->app['url']->setAssetRoot($this->app['config']['app.asset_url']);

        // Storage facade
        foreach (config('multitenancy.filesystems_disks') as $disk) {
            /** @var FilesystemAdapter $filesystemDisk */
            $filesystemDisk = Storage::disk($disk);

            $root = $this->originalPaths['disks'][$disk];

            $filesystemDisk->getAdapter()->setPathPrefix($root);
            $this->app['config']["filesystems.disks.{$disk}.root"] = $root;
        }
    }
}
