<?php

use Illuminate\Broadcasting\BroadcastEvent;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Mail\SendQueuedMailable;
use Illuminate\Notifications\SendQueuedNotifications;
use Placetopay\Cerberus\Actions\MakeQueueTenantAwareAction;
use Placetopay\Cerberus\Models\Tenant;
use Placetopay\Cerberus\Tasks\FilesystemSuffixedTask;
use Placetopay\Cerberus\Tasks\ResetInstancesTask;
use Placetopay\Cerberus\Tasks\SwitchMailerTask;
use Placetopay\Cerberus\Tasks\SwitchTenantTask;
use Placetopay\Cerberus\TenantFinder\DomainTenantFinder;
use Spatie\Multitenancy\Actions\ForgetCurrentTenantAction;
use Spatie\Multitenancy\Actions\MakeTenantCurrentAction;
use Spatie\Multitenancy\Actions\MigrateTenantAction;
use Spatie\Multitenancy\Tasks\PrefixCacheTask;

return [
    /**
     * This name identifies the project for a central database that uses multiple tenants.
     */
    'identifier' => env('APP_IDENTIFIER', 'main'),

    /**
     * Array of drivers that will be suffixed with tenant_name.
     */
    'filesystems_disks' => [
        'local',
        'public',
        //s3
    ],

    /**
     * suffix storage_path
     * Note: Disable this if you're using an external file storage service like S3.
     *
     * If you need to overwrite the storage_path() you must ensure that you have the folder structure for the application
     * cache storage in the storage folder, you can create this structure by executing the command ...
     */
    'suffix_storage_path' => false,

    /*
     * This class is responsible for determining which tenant should be current
     * for the given request.
     *
     * This class should extend `Spatie\Multitenancy\TenantFinder\TenantFinder`
     *
     */
    'tenant_finder' => DomainTenantFinder::class,

    /*
     * These tasks will be performed when switching tenants.
     *
     * A valid task is any class that implements Spatie\Multitenancy\Tasks\SwitchTenantTask
     */
    'switch_tenant_tasks' => [
        FilesystemSuffixedTask::class,
        SwitchTenantTask::class,
        PrefixCacheTask::class,
        SwitchMailerTask::class,
        ResetInstancesTask::class,
    ],

    /*
     * This class is the model used for storing configuration on tenants.
     *
     * It must be or extend `Spatie\Multitenancy\Models\Tenant::class`
     */
    'tenant_model' => Tenant::class,

    /*
     * If there is a current tenant when dispatching a job, the id of the current tenant
     * will be automatically set on the job. When the job is executed, the set
     * tenant on the job will be made current.
     */
    'queues_are_tenant_aware_by_default' => true,

    /*
     * The connection name to reach the tenant database.
     *
     * Set to `null` to use the default connection.
     */
    'tenant_database_connection_name' => 'tenant',

    /*
     * The connection name to reach the landlord database
     */
    'landlord_database_connection_name' => env('DB_LANDLORD_CONNECTION', 'landlord'),

    /*
     * This key will be used to bind the current tenant in the container.
     */
    'current_tenant_container_key' => 'currentTenant',

    /*
     * You can customize some of the behavior of this package by using our own custom action.
     * Your custom action should always extend the default one.
     */
    'actions' => [
        'make_tenant_current_action' => MakeTenantCurrentAction::class,
        'forget_current_tenant_action' => ForgetCurrentTenantAction::class,
        'make_queue_tenant_aware_action' => MakeQueueTenantAwareAction::class,
        'migrate_tenant' => MigrateTenantAction::class,
    ],
    /*
     * You can customize the way in which the package resolves the queuable to a job.
     *
     * For example, using the package laravel-actions (by Loris Leiva), you can
     * resolve JobDecorator to getAction() like so: JobDecorator::class => 'getAction'
     */
    'queueable_to_job' => [
        SendQueuedMailable::class => 'mailable',
        SendQueuedNotifications::class => 'notification',
        CallQueuedListener::class => 'class',
        BroadcastEvent::class => 'event',
    ],

    /**
     * You need to set up this key to use in the middleware to validate when someone application wants to clear cache remotely.
     */
    'middleware_key' => '',

    /**
     * You can add the container to reset when switching a tenant
     */
    'forget_instances' => [
    ],
];
