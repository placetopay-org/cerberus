<?php

namespace Feature\Actions;

use Illuminate\Queue\CallQueuedHandler;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Placetopay\Cerberus\Models\Tenant;
use Placetopay\Cerberus\Tests\Support\NonTenantAwareJob;
use Placetopay\Cerberus\Tests\Support\TestTenantDomainJob;
use Placetopay\Cerberus\Tests\Support\Traits\InteractsWithLogs;
use Placetopay\Cerberus\Tests\TestCase;

class MakeQueueTenantAwareActionTest extends TestCase
{
    use InteractsWithLogs;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake(JobFailed::class);

        $this->tenant = Tenant::factory()->create([
            'app' => config('multitenancy.identifier'),
            'domain' => 'tenant.cerberus.test',
            'name' => 'Cerberus Colombia',
        ]);

        Event::assertNotDispatched(JobFailed::class);
    }

    #[Test]
    public function it_processes_job_with_tenant_domain_and_sets_context()
    {
        $this->fakeLogs();

        $this->tenant->makeCurrent();
        dispatch(new TestTenantDomainJob($this->tenant->name));

        $this->assertEmpty($this->getAllLogMessages());

        $tenantJob = \DB::table('jobs')->first();

        $this->assertIsInt($tenantJob->id);
        $this->assertSame('default', $tenantJob->queue);
        $payload = json_decode($tenantJob->payload, true);

        $this->assertCount(14, $payload);

        $this->assertIsString($payload['uuid']);
        $this->assertSame(TestTenantDomainJob::class, $payload['displayName']);
        $this->assertStringContainsString(CallQueuedHandler::class, $payload['job']);

        $this->assertNull($payload['maxTries']);
        $this->assertNull($payload['maxExceptions']);
        $this->assertFalse($payload['failOnTimeout']);
        $this->assertNull($payload['backoff']);
        $this->assertNull($payload['timeout']);
        $this->assertNull($payload['retryUntil']);

        $this->assertSame(TestTenantDomainJob::class, $payload['data']['commandName']);
        $this->assertStringStartsWith('O:', $payload['data']['command']);
        $this->assertStringContainsString(TestTenantDomainJob::class, $payload['data']['command']);

        $this->assertIsInt($payload['createdAt']);
        $this->assertArrayHasKey('tenantId', $payload['illuminate:log:context']['data']);

        $this->assertEquals($this->tenant->domain, $payload['tenantDomain']);
        $this->assertNull($payload['delay']);

        /*** Running Jobs */
        $this->artisan('queue:work', ['--once' => true])->assertExitCode(0);

        $this->assertSame(0, \DB::table('jobs')->count());

        $this->assertLogMessageContains("Tenant {$this->tenant->name}: TestTenantDomainJob started");
    }

    #[Test]
    public function it_does_not_add_tenant_domain_if_job_is_not_tenant_aware()
    {
        $this->fakeLogs();

        $this->tenant->makeCurrent();

        $this->assertEmpty($this->getAllLogMessages());

        dispatch(new NonTenantAwareJob());

        $tenantJob = \DB::table('jobs')->latest('id')->first();
        $payload = json_decode($tenantJob->payload, true);

        $this->assertArrayNotHasKey('tenantDomain', $payload);

        /*** Running Jobs */
        $this->artisan('queue:work', ['--once' => true])->assertExitCode(0);

        $this->assertEmpty($this->getAllLogMessages());
    }
}
