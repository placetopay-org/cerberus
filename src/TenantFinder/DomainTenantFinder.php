<?php

namespace Placetopay\Cerberus\TenantFinder;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Landlord;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

class DomainTenantFinder extends TenantFinder
{
    public function findForRequest(Request $request): ?IsTenant
    {
        $domain = $request->getHost().str_replace('/index.php', '', $request->getBaseUrl());
        $vanityUrl = $_ENV['APP_VANITY_URL'] ?? '';

        if ('https://'.$domain === $vanityUrl) {
            return null;
        }

        return $this->getTenant($domain);
    }

    public function getTenant($domain): ?IsTenant
    {
        return Landlord::execute(function () use ($domain) {
            return Cache::rememberForever("tenant_{$domain}", function () use ($domain) {
                return app(IsTenant::class)::query()->whereDomain($domain)->first();
            });
        });
    }
}
