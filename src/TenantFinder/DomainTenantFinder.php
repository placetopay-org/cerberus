<?php

namespace Placetopay\Cerberus\TenantFinder;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Spatie\Multitenancy\Models\Concerns\UsesTenantModel;
use Spatie\Multitenancy\Models\Tenant;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

class DomainTenantFinder extends TenantFinder
{
    use UsesTenantModel;

    public function findForRequest(Request $request): ?Tenant
    {
        $host = $request->getHost();

        return Cache::rememberForever("tenant_{$host}", function () use ($host) {
            return $this->getTenantModel()::query()->whereDomain($host)->first();
        });
    }
}
