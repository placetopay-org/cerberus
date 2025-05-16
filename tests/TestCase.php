<?php

namespace Placetopay\Cerberus\Tests;

use Illuminate\Console\Application as Artisan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Event;
use Orchestra\Testbench\Concerns\CreatesApplication;
use Placetopay\Cerberus\TenancyServiceProvider;
use Placetopay\Cerberus\Tests\Feature\Commands\TestClasses\EchoArgumentCommand;
use Placetopay\Cerberus\Tests\Feature\Commands\TestClasses\TenantNoopCommand;
use Spatie\Multitenancy\Concerns\UsesMultitenancyConfig;
use Spatie\Multitenancy\Events\MadeTenantCurrentEvent;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, DatabaseTransactions, UsesMultitenancyConfig;

    protected function setUp(): void
    {
        parent::setUp();

        Event::listen(MadeTenantCurrentEvent::class, function () {
            $this->beginDatabaseTransaction();
        });

        $this->migrateDb();
    }

    protected function tearDown(): void
    {
        config()->set('database.default', 'landlord');
        parent::tearDown();
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
                EchoArgumentCommand::class,
            ]);
        });

        return $this;
    }

    protected function migrateDb(): self
    {
        $this
            ->artisan('migrate', [
                '--database' => 'landlord',
                '--path' => base_path('database/migrations/landlord'),
                '--realpath' => true,
            ])
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
