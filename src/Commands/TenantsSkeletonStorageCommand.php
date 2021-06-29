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
        if (!config('multitenancy.suffix_storage_path')) {
            $this->error('Storage tenancies are not enabled.');
            return 1;
        }

        $domain = Tenant::current()->domain;

        $path = storage_path('app/public');
        if (File::exists($path)) {
            $this->info("[Tenant: {$domain}] No folder to create.");
            return 0;
        }

        if (File::makeDirectory($path, 0755, true)) {
            $this->info("[Tenant: {$domain}] {$path} folder created.");
            return 0;
        }

        $this->info("[Tenant: {$domain}] {$path} folder could not be created.");
        return 1;
    }
}
