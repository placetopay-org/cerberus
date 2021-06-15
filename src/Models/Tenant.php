<?php

namespace Placetopay\Cerberus\Models;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Multitenancy\Models\Tenant as TenantSpatie;

class Tenant extends TenantSpatie
{
    protected $casts = [
        'database' => 'array',
    ];

    public function getDatabase(): ?array
    {
        return $this->database;
    }

    public static function query(): Builder
    {
        return parent::query()->where('app', config('multitenancy.identifier'));
    }
}
