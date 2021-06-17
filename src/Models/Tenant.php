<?php

namespace Placetopay\Cerberus\Models;

use Placetopay\Cerberus\scopes\appScope;
use Spatie\Multitenancy\Models\Tenant as TenantSpatie;

class Tenant extends TenantSpatie
{
    protected $casts = [
        'config' => 'array',
    ];

    public function getDatabase(): ?array
    {
        return $this->database;
    }

    public function getConfig(): ?array
    {
        return $this->config;
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AppScope());
    }
}
