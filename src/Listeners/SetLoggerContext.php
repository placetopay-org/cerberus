<?php

namespace Placetopay\Cerberus\Listeners;

use Illuminate\Log\LogManager;
use Spatie\Multitenancy\Events\MadeTenantCurrentEvent;

class SetLoggerContext
{
    public function __construct(private readonly LogManager $logger)
    {
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
