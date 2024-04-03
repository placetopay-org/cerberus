<?php

namespace Placetopay\Cerberus\Tests\Tasks;

use Illuminate\Mail\Transport\ArrayTransport;
use Illuminate\Mail\Transport\LogTransport;
use Placetopay\Cerberus\Models\Tenant;
use Placetopay\Cerberus\Tests\TestCase;

class SwitchMailerTaskTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->tenant = factory(Tenant::class)->create([
            'app' => config('multitenancy.identifier'),
            'name' => 'tenant_1',
            'config' => [
                'mail' => [
                    'default' => 'array',
                ],
            ],
        ]);

        $this->anotherTenant = factory(Tenant::class)->create([
            'app' => config('multitenancy.identifier'),
            'name' => 'tenant_2',
            'config' => [
                'mail' => [
                    'default' => 'log',
                ],
            ],
        ]);
    }

    /** @test */
    public function it_resets_mailer_on_every_tenant(): void
    {
        // Default mail driver is 'smtp'
        $this->assertInstanceOf(\Swift_SmtpTransport::class, app('mailer')->getSwiftMailer()->getTransport());

        // First tenant mail driver is 'array'
        $this->tenant->makeCurrent();
        $this->assertInstanceOf(ArrayTransport::class, app('mailer')->getSwiftMailer()->getTransport());

        // Going back to default, mail driver is 'smtp'
        Tenant::forgetCurrent();
        $this->assertInstanceOf(\Swift_SmtpTransport::class, app('mailer')->getSwiftMailer()->getTransport());

        // Switch to another tenant, mail driver is 'log'
        $this->anotherTenant->makeCurrent();
        $this->assertInstanceOf(LogTransport::class, app('mailer')->getSwiftMailer()->getTransport());
    }
}
