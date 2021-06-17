<?php

namespace Placetopay\Cerberus\Tasks;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Spatie\Multitenancy\Models\Tenant;

class SwitchTenantTask implements \Spatie\Multitenancy\Tasks\SwitchTenantTask
{
    private array $originalValues;

    public function __construct()
    {
        $this->originalValues = config()->all();
    }

    public function makeCurrent(Tenant $tenant): void
    {
        $dataMapping = Arr::dot($tenant->config ?? []);

        config($dataMapping);

        DB::purge(config('multitenancy.tenant_database_connection_name'));
        DB::setDefaultConnection(config('multitenancy.tenant_database_connection_name'));
    }

    public function forgetCurrent(): void
    {
        config($this->originalValues);
    }
}