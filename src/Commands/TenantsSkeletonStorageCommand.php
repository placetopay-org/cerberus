<?php

namespace Placetopay\Cerberus\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Placetopay\Cerberus\Commands\Concerns\TenantAware;
use Placetopay\Cerberus\Models\Tenant;

class TenantsSkeletonStorageCommand extends Command
{
    use TenantAware;

    protected $signature = 'tenants:skeleton-storage {--tenant=*}';

    protected $description = 'Create required folders for tenant(s).';

    public function handle(): int
    {
        if (! config('multitenancy.suffix_storage_path')) {
            $this->error('Storage tenancies are not enabled.');

            return 1;
        }

        $domain = Tenant::current()->domain;

        $paths = [
            'app/public',
            'framework/cache',
            'framework/sessions',
            'framework/testing',
            'framework/views',
        ];

        foreach ($paths as $originalPath) {
            $path = storage_path($originalPath);

            if (File::exists($path)) {
                $this->info("[Tenant: {$domain}] No folder to create.");
                continue;
            }

            if (File::makeDirectory($path, 0755, true)) {
                $this->info("[Tenant: {$domain}] {$path} folder created.");
                continue;
            }

            $this->alert("[Tenant: {$domain}] {$path} folder could not be created.");
        }

        return 0;
    }
}
