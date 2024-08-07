<?php

namespace Placetopay\Cerberus\TenantFinder;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Spatie\Multitenancy\Landlord;
use Spatie\Multitenancy\Models\Concerns\UsesTenantModel;
use Spatie\Multitenancy\Models\Tenant;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

class DomainTenantFinder extends TenantFinder
{
    use UsesTenantModel;

    public function findForRequest(Request $request): ?Tenant
    {
        $domain = $request->getHost().str_replace('/index.php', '', $request->getBaseUrl());
        $vanityUrl = $_ENV['APP_VANITY_URL'] ?? '';

        if ('https://'.$domain === $vanityUrl) {
            return null;
        }

        return $this->getTenant($domain);
    }

    public function getTenant($domain)
    {
        return Landlord::execute(function () use ($domain) {
            return Cache::rememberForever("tenant_{$domain}", function () use ($domain) {
                return $this->getTenantModel()::query()->whereDomain($domain)->first();
            });
        });
    }
}
