<?php

namespace Placetopay\Cerberus\Tasks;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Spatie\Multitenancy\Concerns\UsesMultitenancyConfig;
use Spatie\Multitenancy\Exceptions\InvalidConfiguration;
use Spatie\Multitenancy\Models\Tenant;

class SwitchTenantTask implements \Spatie\Multitenancy\Tasks\SwitchTenantTask
{
    use UsesMultitenancyConfig;

    private array $originalValues;

    public function __construct()
    {
        $this->originalValues = config()->all();
    }

    /**
     * @param Tenant $tenant
     * @throws InvalidConfiguration
     */
    public function makeCurrent(Tenant $tenant): void
    {
        $dataMapping = Arr::dot($tenant->config ?? []);
        config($dataMapping);

        $this->purgeConnectionDatabase();
    }

    /**
     * @throws InvalidConfiguration
     */
    public function forgetCurrent(): void
    {
        config($this->originalValues);
        $this->purgeConnectionDatabase();
    }

    protected function purgeConnectionDatabase(): void
    {
        $tenantConnectionName = $this->tenantDatabaseConnectionName();

        if ($tenantConnectionName === $this->landlordDatabaseConnectionName()) {
            throw InvalidConfiguration::tenantConnectionIsEmptyOrEqualsToLandlordConnection();
        }

        if (is_null(config("database.connections.{$tenantConnectionName}"))) {
            throw InvalidConfiguration::tenantConnectionDoesNotExist($tenantConnectionName);
        }

        DB::purge($tenantConnectionName);
    }
}
