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
        $this->line('Tenant ID is ' . Tenant::current()->id);
    }
}
