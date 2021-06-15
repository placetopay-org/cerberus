<?php

namespace Placetopay\Cerberus\Tasks;

use Illuminate\Support\Facades\DB;
use Spatie\Multitenancy\Concerns\UsesMultitenancyConfig;
use Spatie\Multitenancy\Exceptions\InvalidConfiguration;
use Spatie\Multitenancy\Models\Tenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

class SwitchTenantDatabaseTask implements SwitchTenantTask
{
    use UsesMultitenancyConfig;

    /**
     * @param \Spatie\Multitenancy\Models\Tenant $tenant
     * @throws \Spatie\Multitenancy\Exceptions\InvalidConfiguration
     */
    public function makeCurrent(Tenant $tenant): void
    {
        $this->setTenantConnectionDatabase($tenant->getDatabase());
    }

    /**
     * @throws \Spatie\Multitenancy\Exceptions\InvalidConfiguration
     */
    public function forgetCurrent(): void
    {
        $this->setTenantConnectionDatabase(null);
    }

    /**
     * @param array|null $database
     * @throws \Spatie\Multitenancy\Exceptions\InvalidConfiguration
     */
    protected function setTenantConnectionDatabase(?array $database): void
    {
        $tenantConnectionName = $this->tenantDatabaseConnectionName();

        if ($tenantConnectionName === $this->landlordDatabaseConnectionName()) {
            throw InvalidConfiguration::tenantConnectionIsEmptyOrEqualsToLandlordConnection();
        }

        if (is_null(config("database.connections.{$tenantConnectionName}"))) {
            throw InvalidConfiguration::tenantConnectionDoesNotExist($tenantConnectionName);
        }

        $keyConnection = "database.connections.{$tenantConnectionName}";

        $connection = config($keyConnection);

        if (is_null($database)) {
            config([$keyConnection . '.database' => null]);
        } else {
            config([$keyConnection => array_merge($connection, $database)]);
        }

        DB::purge($tenantConnectionName);

        DB::setDefaultConnection($tenantConnectionName);
    }
}
