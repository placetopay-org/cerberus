<?php

namespace Placetopay\Cerberus\Models;

use Placetopay\Cerberus\Scopes\AppScope;
use Spatie\Multitenancy\Models\Tenant as TenantSpatie;

class Tenant extends TenantSpatie
{
    protected $fillable = ['config'];

    protected $casts = [
        'config' => 'array',
    ];

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
