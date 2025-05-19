<?php

namespace Placetopay\Cerberus\Tests\Support;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Spatie\Multitenancy\Concerns\UsesMultitenancyConfig;
use Spatie\Multitenancy\Jobs\TenantAware;

class TestTenantDomainJob implements ShouldQueue, TenantAware
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use UsesMultitenancyConfig;

    public function __construct(public string $tenantName)
    {
    }

    public function handle(): void
    {
        Log::info("Tenant $this->tenantName: TestTenantDomainJob started");
    }
}
