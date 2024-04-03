<?php

namespace Placetopay\Cerberus\Tests\Tasks;

use Placetopay\Cerberus\Models\Tenant;
use Placetopay\Cerberus\Tests\TestCase;

class ResetInstanceTaskTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->tenant = factory(Tenant::class)->create([
            'app' => config('multitenancy.identifier'),
            'name' => 'tenant_1',
            'config' => [
                'app' => [
                    'key' => 'base64:JBa5rX3XEf6VuV+YqKGlPvgEJfa7ZzqbUV6TQWRwnG4=',
                ],
            ],
        ]);

        $this->anotherTenant = factory(Tenant::class)->create([
            'app' => config('multitenancy.identifier'),
            'name' => 'tenant_2',
            'config' => [
                'app' => [
                    'key' => 'base64:atprG7MqSOqUfRuK7NRIDQve0oYbrUzcIivlW2bLP+0=',
                ],
            ],
        ]);
    }

    /** @test */
    public function it_can_reset_encrypt_container_ok(): void
    {
        config()->set('multitenancy.forget_instances', ['encrypter']);

        $this->tenant->makeCurrent();
        $firstEncrypt = app('encrypter')->getKey();

        $this->anotherTenant->makeCurrent();
        $secondEncrypt = app('encrypter')->getKey();

        $this->assertNotEquals($firstEncrypt, $secondEncrypt);
    }
}
