<?php

namespace Placetopay\Cerberus\Tasks;

use Illuminate\Support\Facades\App;
use Spatie\Multitenancy\Models\Tenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

class ResetInstancesTask implements SwitchTenantTask
{
    public function makeCurrent(Tenant $tenant): void
    {
    }

    public function forgetCurrent(): void
    {
        foreach (config('multitenancy.forget_instances', []) as $abstract) {
            app()->forgetInstance($abstract);
        }
    }
}
