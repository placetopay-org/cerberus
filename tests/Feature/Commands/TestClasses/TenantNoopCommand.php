<?php

namespace Placetopay\Cerberus\Tests\Feature\Commands\TestClasses;

use Illuminate\Console\Command;
use Placetopay\Cerberus\Commands\Concerns\TenantAware;
use Placetopay\Cerberus\Models\Tenant;

class TenantNoopCommand extends Command
{
    use TenantAware;

    protected $signature = 'tenant:noop {--tenant=*}';

    protected $description = 'Execute noop for tenant(s)';

    public function handle()
    {
        $tenant = Tenant::current();

        $this->line('Tenant ID is ' . $tenant->id);
        $this->line('Tenant Config is ' . json_encode($tenant->config));
    }
}
