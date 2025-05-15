<?php

namespace Placetopay\Cerberus\Tasks;

use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

class ResetInstancesTask implements SwitchTenantTask
{
    public function makeCurrent(IsTenant $tenant): void
    {
    }

    public function forgetCurrent(): void
    {
        foreach (config('multitenancy.forget_instances', []) as $abstract) {
            app()->forgetInstance($abstract);
        }
    }
}
