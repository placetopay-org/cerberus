<?php

namespace Placetopay\Cerberus\Listeners;

use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Log;
use Spatie\Multitenancy\Events\MadeTenantCurrentEvent;

class SetLoggerContext
{
    private Logger $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function handle(MadeTenantCurrentEvent $event)
    {
        if (method_exists($this->logger, 'withContext')) {
            Log::withContext([
                'TENANT_DOMAIN' => $event->tenant->domain,
            ]);
        }
    }
}
