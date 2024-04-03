<?php

namespace Placetopay\Cerberus;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Event;
use Placetopay\Cerberus\Commands\TenantsArtisanCommand;
use Placetopay\Cerberus\Commands\TenantsListCommand;
use Placetopay\Cerberus\Commands\TenantsSkeletonStorageCommand;
use Placetopay\Cerberus\Http\Controllers\TenantController;
use Placetopay\Cerberus\Http\Middlewares\AppCleanCache;
use Placetopay\Cerberus\Listeners\SetLoggerContext;
use Spatie\LaravelPackageTools\Package;
use Spatie\Multitenancy\Events\MadeTenantCurrentEvent;
use Spatie\Multitenancy\MultitenancyServiceProvider;

class TenancyServiceProvider extends MultitenancyServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-multitenancy')
            ->hasConfigFile()
            ->hasMigrations([
                'landlord/create_landlord_tenants_table',
                'landlord/create_landlord_jobs_table',
                'landlord/create_landlord_failed_jobs_table',
            ])
            ->hasCommands([
                TenantsArtisanCommand::class,
                TenantsListCommand::class,
                TenantsSkeletonStorageCommand::class,
            ]);
    }

    public function packageBooted(): void
    {
        Event::listen(
            MadeTenantCurrentEvent::class,
            [SetLoggerContext::class, 'handle']
        );

        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('clean-cache', AppCleanCache::class);
        $this->app['router']->post('/clean-cache', ['uses' =>  TenantController::class.'@clean', 'as' => 'app.clean']);

        parent::packageBooted();
    }
}
