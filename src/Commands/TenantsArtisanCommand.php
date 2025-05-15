<?php

namespace Placetopay\Cerberus\Commands;

use Illuminate\Support\Facades\Artisan;
use Placetopay\Cerberus\Commands\Concerns\TenantAware;
use Spatie\Multitenancy\Commands\TenantsArtisanCommand as TenantsArtisanParentCommand;
use Spatie\Multitenancy\Models\Tenant;

class TenantsArtisanCommand extends TenantsArtisanParentCommand
{
    use TenantAware;

    protected $signature = 'tenants:artisan {artisanCommand} {--tenant=*} {--no-slashes}';

    /**
     * This is meant to be a copy of the parent command except for the `--no-slashes` option, which allows
     * me to send parameters that have many spaces in its arguments and the consumer will be responsible
     * for adding the slashes.
     *
     * @return void
     */
    public function handle(): void
    {
        if (! $artisanCommand = $this->argument('artisanCommand')) {
            $artisanCommand = $this->ask('Which artisan command do you want to run for all tenants?');
        }

        if (! $this->option('no-slashes')) {
            $artisanCommand = addslashes($artisanCommand);
        }

        $tenant = Tenant::current();

        $this->line('');
        $this->info("Running command for tenant `{$tenant->name}` (id: {$tenant->getKey()})...");
        $this->line('---------------------------------------------------------');

        Artisan::call($artisanCommand, [], $this->output);
    }
}
