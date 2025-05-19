<?php

namespace Placetopay\Cerberus\Tests\Support;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\Multitenancy\Jobs\NotTenantAware;

class NonTenantAwareJob implements ShouldQueue, NotTenantAware
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
    }
}
