<?php

namespace Placetopay\Cerberus\Tests\Unit;

use Placetopay\Cerberus\Models\Tenant;
use Placetopay\Cerberus\Tests\TestCase;

class TenantTest extends TestCase
{
    protected Tenant $tenant;

    protected Tenant $anotherTenant;

    public function setUp(): void
    {
        parent::setUp();

        $config = [
           'tenant' => [
               'terms_and_privacy' => [
                   'es_CO' => 'terminos y condiciones',
                   'en' => 'terms and conditions',
                   'es_CL' => 'terminos y condiciones chile',
               ],
           ],
            'app' => [
                'locales' => [
                    'es',
                    'en',
                    'fr',
                ],
            ],
        ];

        $this->tenant = factory(Tenant::class)->create([
            'app' => config('multitenancy.identifier'),
            'name' => 'tenant_1',
            'config' => $config,
        ]);

        config()->set('app.fallback_locale', 'es_CL');
    }

    /** @test */
    public function it_return_translation_by_local()
    {
        config()->set('app.locale', 'es_CO');
        $this->tenant->makeCurrent();

        $this->assertEquals(
            $this->tenant->config['tenant']['terms_and_privacy']['es_CO'],
            app('currentTenant')->translate('terms_and_privacy')
        );
    }

    /** @test */
    public function it_return_translation_by_short()
    {
        config()->set('app.locale', 'en_US');
        $this->tenant->makeCurrent();

        $this->assertEquals(
            $this->tenant->config['tenant']['terms_and_privacy']['en'],
            app('currentTenant')->translate('terms_and_privacy')
        );
    }

    /** @test */
    public function it_returns_translation_by_fallback()
    {
        config()->set('app.locale', null);

        $this->tenant->makeCurrent();

        $this->assertEquals(
            $this->tenant->config['tenant']['terms_and_privacy']['es_CL'],
            app('currentTenant')->translate('terms_and_privacy')
        );
    }

    /** @test */
    public function it_return_empty_if_does_not_exist()
    {
        config()->set('app.locale', null);
        config()->set('app.fallback_locale', null);

        $this->tenant->makeCurrent();

        $this->assertEmpty(app('currentTenant')->translate('terms_and_privacy'));
    }

    /** @test */
    public function overwrite_array_keys_config_values_successfully(): void
    {
        config()->set('app.locales', ['en', 'es', 'fr', 'it', 'pt']);

        $this->tenant->makeCurrent();

        $this->assertEquals(['es', 'en', 'fr'], config('app.locales'));
    }
}
