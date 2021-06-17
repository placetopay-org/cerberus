<?php

namespace Placetopay\Cerberus\Tests;

use Illuminate\Console\Application as Artisan;
use Illuminate\Support\Facades\DB;
use Orchestra\Testbench\Concerns\WithLaravelMigrations;
use Orchestra\Testbench\TestCase as Orchestra;
use Placetopay\Cerberus\Models\Tenant;
use Placetopay\Cerberus\TenancyServiceProvider;
use Placetopay\Cerberus\Tests\Feature\Commands\TestClasses\TenantNoopCommand;

abstract class TestCase extends Orchestra
{
    use WithLaravelMigrations;

    public function setUp(): void
    {
        parent::setUp();

        $this->withFactories(__DIR__ . '/database/factories');

        $this->migrateDb();

        Tenant::truncate();

        DB::table('jobs')->truncate();
    }

    protected function getPackageProviders($app): array
    {
        $this->bootCommands();

        return [
            TenancyServiceProvider::class,
        ];
    }

    protected function bootCommands() : self
    {
        Artisan::starting(function ($artisan) {
            $artisan->resolveCommands([
                TenantNoopCommand::class,
            ]);
        });

        return $this;
    }

    protected function migrateDb(): self
    {
        $landLordMigrationsPath = realpath(__DIR__ . '/database/migrations/landlord');
        $landLordMigrationsPath = str_replace('\\', '/', $landLordMigrationsPath);

        $this
            ->artisan("migrate --database=landlord --path={$landLordMigrationsPath} --realpath")
            ->assertExitCode(0);

        return $this;
    }

    public function getEnvironmentSetUp($app): void
    {
        config(['database.default' => 'landlord']);

        config([
            'database.connections.landlord' => [
                'driver' => 'mysql',
                'username' => env('DB_USERNAME', 'root'),
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'password' => env('DB_PASSWORD'),
                'database' => 'laravel_mt_landlord',
            ],

            'database.connections.tenant' => [
                'driver' => 'mysql',
                'username' => env('DB_USERNAME', 'root'),
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'password' => env('DB_PASSWORD'),
                'database' => null,
            ],
        ]);

        config()->set('queue.default', 'database');

        config()->set('queue.connections.database', [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 90,
            'connection' => 'landlord',
        ]);
    }

    protected function getConfigStructure(string $dbName): array
    {
        return [
            'database' => [
                'connections' => [
                    config('multitenancy.tenant_database_connection_name') => [
                        'database' => $dbName,
                    ],
                ],
            ],
        ];
    }
}
