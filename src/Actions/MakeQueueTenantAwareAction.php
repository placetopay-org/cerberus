<?php

namespace Placetopay\Cerberus\Actions;

use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobRetryRequested;
use Placetopay\Cerberus\Models\Tenant;
use Placetopay\Cerberus\TenantFinder\DomainTenantFinder;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Exceptions\CurrentTenantCouldNotBeDeterminedInTenantAwareJob;

class MakeQueueTenantAwareAction extends \Spatie\Multitenancy\Actions\MakeQueueTenantAwareAction
{
    protected function listenForJobsBeingProcessed(): static
    {
        app('events')->listen(JobProcessing::class, function (JobProcessing $event) {
            if (! array_key_exists('tenantDomain', $event->job->payload())) {
                return;
            }

            $this->findTenant($event)->makeCurrent();
        });

        return $this;
    }

    protected function listenForJobsRetryRequested(): static
    {
        app('events')->listen(JobRetryRequested::class, function (JobRetryRequested $event) {
            if (! array_key_exists('tenantDomain', $event->payload())) {
                return;
            }

            $this->findTenant($event)->makeCurrent();
        });

        return $this;
    }

    protected function findTenant(JobProcessing|JobRetryRequested $event): IsTenant
    {
        $tenantDomain = $this->getEventPayload($event)['tenantDomain'] ?? null;

        if (! $tenantDomain) {
            $event->job->delete();

            throw CurrentTenantCouldNotBeDeterminedInTenantAwareJob::noTenantFound($event);
        }

        /** @var Tenant $tenant */
        if (! $tenant = (new DomainTenantFinder)->getTenant($tenantDomain)) {
            $event->job->delete();

            throw CurrentTenantCouldNotBeDeterminedInTenantAwareJob::noTenantFound($event);
        }

        return $tenant;
    }
}
