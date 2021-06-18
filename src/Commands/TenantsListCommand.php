<?php

namespace Placetopay\Cerberus\Commands;

use Illuminate\Console\Command;
use Placetopay\Cerberus\Models\Tenant;
use Spatie\Multitenancy\Models\Concerns\UsesTenantModel;

class TenantsListCommand extends Command
{
    use UsesTenantModel;

    protected $signature = 'tenants:list';

    protected $description = 'List tenants.';

    public function handle(): void
    {
        $tenantQuery = $this->getTenantModel()::query();

        $this->info("Listing all tenants ({$tenantQuery->count()}).");

        $tenantQuery->cursor()
            ->each(function (Tenant $tenant) {
                $this->line("[Tenant] domain: {$tenant['domain']} @ " . $tenant['name']);
            });
    }
}
