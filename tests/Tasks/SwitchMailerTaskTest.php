<?php

namespace Placetopay\Cerberus\Tests\Tasks;

use Illuminate\Mail\Transport\ArrayTransport;
use Illuminate\Mail\Transport\LogTransport;
use PHPUnit\Framework\Attributes\Test;
use Placetopay\Cerberus\Models\Tenant;
use Placetopay\Cerberus\Tests\TestCase;
use Spatie\Multitenancy\Contracts\IsTenant;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;

class SwitchMailerTaskTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->tenant = factory(Tenant::class)->create([
            'app' => config('multitenancy.identifier'),
            'name' => 'tenant_1',
            'config' => ['mail' => ['default' => 'array']],
        ]);

        $this->smtpTenant = factory(Tenant::class)->create([
            'app' => config('multitenancy.identifier'),
            'name' => 'tenant_2',
            'config' => ['mail' => ['default' => 'smtp']],
        ]);

        $this->anotherTenant = factory(Tenant::class)->create([
            'app' => config('multitenancy.identifier'),
            'name' => 'tenant_2',
            'config' => ['mail' => ['default' => 'log']],
        ]);
    }

    #[Test]
    public function it_resets_mailer_on_every_tenant(): void
    {
        // Default mail driver is 'log'
        $this->assertInstanceOf(LogTransport::class, app('mailer')->getSymfonyTransport());

        // First tenant mail driver is 'array'
        $this->tenant->makeCurrent();
        $this->assertInstanceOf(ArrayTransport::class, app('mailer')->getSymfonyTransport());

        // Smtp tenant mail driver is 'smtp'
        $this->smtpTenant->makeCurrent();
        $this->assertInstanceOf(SmtpTransport::class, app('mailer')->getSymfonyTransport());

        // Going back to default, mail driver is 'log'
        app(IsTenant::class)::forgetCurrent();
        $this->assertInstanceOf(LogTransport::class, app('mailer')->getSymfonyTransport());

        // Switch to another tenant, mail driver is 'log'
        $this->anotherTenant->makeCurrent();
        $this->assertInstanceOf(LogTransport::class, app('mailer')->getSymfonyTransport());
    }
}
