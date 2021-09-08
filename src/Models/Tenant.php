<?php

namespace Placetopay\Cerberus\Models;

use Placetopay\Cerberus\Scopes\AppScope;
use Spatie\Multitenancy\Models\Tenant as TenantSpatie;

/**
 * Class Tenant.
 * @property string name
 * @property string app
 * @property string domain
 * @property array config
 */
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

    public function translate(string $key): string
    {
        $locale = $this->normalizeLocale($key);
        return $this->getTranslations($key)[$locale] ?? '';
    }

    private function getTranslations(string $key): array
    {
        return config("tenant.{$key}", []);
    }

    private function normalizeLocale(string $key): ?string
    {
        $translatedLocales = array_keys($this->getTranslations($key));
        $locale = app()->getLocale();

        if (in_array($locale, $translatedLocales)) {
            return $locale;
        }

        if (in_array($this->shortLocale($locale), $translatedLocales)) {
            return $this->shortLocale($locale);
        }

        if (!is_null(app()->getFallbackLocale())) {
            return app()->getFallbackLocale();
        }

        return $locale;
    }

    private function shortLocale($locale): string
    {
        return strtolower(substr($locale, 0, 2));
    }
}
