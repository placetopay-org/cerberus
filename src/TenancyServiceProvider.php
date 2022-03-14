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
use Spatie\Multitenancy\Actions\MakeQueueTenantAwareAction;
use Spatie\Multitenancy\Events\MadeTenantCurrentEvent;
use Spatie\Multitenancy\Exceptions\InvalidConfiguration;
use Spatie\Multitenancy\MultitenancyServiceProvider;
use Spatie\Multitenancy\Tasks\TasksCollection;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

class TenancyServiceProvider extends MultitenancyServiceProvider
{
    protected function bootCommands(): self
    {
        $this->commands([
            TenantsArtisanCommand::class,
            TenantsListCommand::class,
            TenantsSkeletonStorageCommand::class,
        ]);

        return $this;
    }

    public function boot(): void
    {
        Event::listen(
            MadeTenantCurrentEvent::class,
            [SetLoggerContext::class, 'handle']
        );

        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('clean-cache', AppCleanCache::class);
        $this->app['router']->post('/clean-cache', ['uses' =>  TenantController::class.'@clean', 'as' => 'app.clean']);

        if ($this->app->runningInConsole()) {
            $this->registerPublishables();
        }

        $this
            ->bootCommands()
            ->registerTenantFinder()
            ->registerTasksCollection()
            ->configureRequests()
            ->configureQueue();
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/multitenancy.php', 'multitenancy');
    }

    protected function registerPublishables(): self
    {
        $this->publishes([
            __DIR__.'/../config/multitenancy.php' => config_path('multitenancy.php'),
        ], 'config');

        if (! class_exists('CreateLandlordTenantsTable')) {
            $this->publishes([
                __DIR__.'/../database/migrations/landlord/create_landlord_tenants_table.php.stub' => database_path('migrations/landlord/'.date('Y_m_d_His', time()).'_create_landlord_tenants_table.php'),
                __DIR__.'/../database/migrations/landlord/create_landlord_jobs_table.php.stub' => database_path('migrations/landlord/'.date('Y_m_d_His', time()).'_create_landlord_jobs_table.php'),
                __DIR__.'/../database/migrations/landlord/create_landlord_failed_jobs_table.php.stub' => database_path('migrations/landlord/'.date('Y_m_d_His', time()).'_create_landlord_failed_jobs_table.php'),
            ], 'migrations');
        }

        return $this;
    }

    protected function registerTenantFinder(): self
    {
        if (config('multitenancy.tenant_finder')) {
            $this->app->bind(TenantFinder::class, config('multitenancy.tenant_finder'));
        }

        return $this;
    }

    protected function registerTasksCollection(): self
    {
        $this->app->singleton(TasksCollection::class, function () {
            $taskClassNames = config('multitenancy.switch_tenant_tasks');

            return new TasksCollection($taskClassNames);
        });

        return $this;
    }

    protected function configureRequests(): self
    {
        if (! $this->app->runningInConsole()) {
            $this->determineCurrentTenant();
        }

        return $this;
    }

    protected function determineCurrentTenant(): void
    {
        if (! config('multitenancy.tenant_finder')) {
            return;
        }

        /** @var TenantFinder $tenantFinder */
        $tenantFinder = app(TenantFinder::class);

        $tenant = $tenantFinder->findForRequest(request());

        optional($tenant)->makeCurrent();
    }

    /**
     * @throws InvalidConfiguration
     */
    protected function configureQueue(): self
    {
        $this
            ->getMultitenancyActionClass(
                'make_queue_tenant_aware_action',
                MakeQueueTenantAwareAction::class
            )
            ->execute();

        return $this;
    }
}
