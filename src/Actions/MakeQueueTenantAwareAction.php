<?php

namespace Placetopay\Cerberus\Actions;

use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobRetryRequested;
use Illuminate\Support\Arr;
use Placetopay\Cerberus\TenantFinder\DomainTenantFinder;
use Spatie\Multitenancy\Exceptions\CurrentTenantCouldNotBeDeterminedInTenantAwareJob;
use Spatie\Multitenancy\Jobs\NotTenantAware;
use Spatie\Multitenancy\Jobs\TenantAware;
use Spatie\Multitenancy\Models\Concerns\UsesTenantModel;
use Spatie\Multitenancy\Models\Tenant;

class MakeQueueTenantAwareAction extends \Spatie\Multitenancy\Actions\MakeQueueTenantAwareAction
{
    use UsesTenantModel;

    public function execute()
    {
        $this
            ->listenForJobsBeingQueued()
            ->listenForJobsBeingProcessed()
            ->listenForJobsRetryRequested();
    }

    protected function listenForJobsBeingQueued(): self
    {
        app('queue')->createPayloadUsing(function ($connectionName, $queue, $payload) {
            $queueable = $payload['data']['command'];

            if (!$this->isTenantAware($queueable)) {
                return [];
            }

            return ['tenantDomain' => optional(Tenant::current())->domain];
        });

        return $this;
    }

    protected function listenForJobsBeingProcessed(): self
    {
        app('events')->listen(JobProcessing::class, function (JobProcessing $event) {
            if (!array_key_exists('tenantDomain', $event->job->payload())) {
                return;
            }

            $this->findTenant($event)->makeCurrent();
        });

        return $this;
    }

    protected function listenForJobsRetryRequested(): self
    {
        app('events')->listen(JobRetryRequested::class, function (JobRetryRequested $event) {
            if (!array_key_exists('tenantDomain', $event->payload())) {
                return;
            }

            $this->findTenant($event)->makeCurrent();
        });

        return $this;
    }

    protected function isTenantAware(object $queueable): bool
    {
        $reflection = new \ReflectionClass($this->getJobFromQueueable($queueable));

        if ($reflection->implementsInterface(TenantAware::class)) {
            return true;
        }

        if ($reflection->implementsInterface(NotTenantAware::class)) {
            return false;
        }

        return config('multitenancy.queues_are_tenant_aware_by_default') === true;
    }

    protected function findTenant(JobProcessing|JobRetryRequested $event): Tenant
    {
        $tenantDomain = $this->getEventPayload($event)['tenantDomain'] ?? null;

        if (!$tenantDomain) {
            $event->job->delete();

            throw CurrentTenantCouldNotBeDeterminedInTenantAwareJob::noTenantFound($event);
        }

        /** @var \Placetopay\Cerberus\Models\Tenant $tenant */
        if (!$tenant = (new DomainTenantFinder)->getTenant($tenantDomain)) {
            $event->job->delete();

            throw CurrentTenantCouldNotBeDeterminedInTenantAwareJob::noTenantFound($event);
        }

        return $tenant;
    }

    protected function getJobFromQueueable(object $queueable)
    {
        $job = Arr::get(config('multitenancy.queueable_to_job'), $queueable::class);

        if (!$job) {
            return $queueable;
        }

        if (method_exists($queueable, $job)) {
            return $queueable->{$job}();
        }

        return $queueable->$job;
    }
}
