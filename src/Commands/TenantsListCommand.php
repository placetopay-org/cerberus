<?php

namespace Placetopay\Cerberus\Commands;

use Illuminate\Console\Command;
use Placetopay\Cerberus\Models\Tenant;
use Spatie\Multitenancy\Contracts\IsTenant;

class TenantsListCommand extends Command
{
    protected $signature = 'tenants:list';

    protected $description = 'List tenants.';

    public function handle(): void
    {
        $tenantQuery = app(IsTenant::class)::query();

        $this->info("Listing all tenants ({$tenantQuery->count()}).");

        $tenantQuery->cursor()
            ->each(function (Tenant $tenant) {
                $this->line("[Tenant] domain: {$tenant['domain']} @ ".$tenant['name']);
            });
    }
}
