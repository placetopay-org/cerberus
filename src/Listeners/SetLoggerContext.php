<?php

namespace Placetopay\Cerberus\Listeners;

use Illuminate\Log\Logger;
use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\Log;
use Spatie\Multitenancy\Events\MadeTenantCurrentEvent;

class SetLoggerContext
{
    private LogManager $logger;

    public function __construct(LogManager $logger)
    {
        $this->logger = $logger;
    }

    public function handle(MadeTenantCurrentEvent $event)
    {
        if (method_exists($this->logger->driver(), 'withContext')) {
            $this->logger->withContext([
                'TENANT_DOMAIN' => $event->tenant->domain,
            ]);
        }
    }
}
