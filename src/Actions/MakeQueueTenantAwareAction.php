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
