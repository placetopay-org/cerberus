<?php

namespace Placetopay\Cerberus\Commands\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Spatie\Multitenancy\Concerns\UsesMultitenancyConfig;
use Spatie\Multitenancy\Contracts\IsTenant;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait TenantAware
{
    use UsesMultitenancyConfig;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tenant = Arr::wrap($this->option('tenant'));

        if (empty($tenant)) {
            $tenant = app(IsTenant::class)::query()->pluck('domain')->toArray();
        }

        $tenants = collect($tenant)->map(
            fn ($domain) => Cache::rememberForever("tenant_$domain", function () use ($domain) {
                return app(IsTenant::class)::query()->whereDomain($domain)->first();
            })
        )->filter();

        if ($tenants->count() === 0) {
            $this->error('No tenant(s) found.');

            return -1;
        }

        return $tenants
            ->map(fn (IsTenant $tenant) => $tenant->execute(fn () => (int) $this->laravel->call([$this, 'handle'])))
            ->sum();
    }
}
