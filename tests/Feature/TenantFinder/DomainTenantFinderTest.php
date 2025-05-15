<?php

namespace Placetopay\Cerberus\Tests\Feature\TenantFinder;

use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Placetopay\Cerberus\Models\Tenant;
use Placetopay\Cerberus\TenantFinder\DomainTenantFinder;
use Placetopay\Cerberus\Tests\TestCase;

class DomainTenantFinderTest extends TestCase
{
    private DomainTenantFinder $tenantFinder;

    public function setUp(): void
    {
        parent::setUp();

        $this->tenantFinder = new DomainTenantFinder();
    }

    #[Test]
    public function it_can_find_a_tenant_for_the_current_domain()
    {
        $tenant = factory(Tenant::class)->create([
            'app' => config('multitenancy.identifier'),
            'domain' => 'my-domain.com',
        ]);

        $request = Request::create('https://my-domain.com');

        $this->assertEquals($tenant->id, $this->tenantFinder->findForRequest($request)->id);
    }

    #[Test]
    public function it_will_return_null_if_there_are_no_tenants()
    {
        $request = Request::create('https://my-domain.com');

        $this->assertNull($this->tenantFinder->findForRequest($request));
    }

    #[Test]
    public function it_will_return_null_if_no_tenant_can_be_found_for_the_current_domain()
    {
        factory(Tenant::class)->create([
            'app' => config('multitenancy.identifier'),
            'domain' => 'my-domain.com',
        ]);

        $request = Request::create('https://another-domain.com');

        $this->assertNull($this->tenantFinder->findForRequest($request));
    }

    #[Test]
    #[DataProvider('tenantWithPathProvider')]
    public function it_can_find_a_tenant_for_domain_with_path(
        string $tenantDomain,
        string $requestUrl,
        array $requestOptions
    ): void {
        $registeredTenant = factory(Tenant::class)->create([
            'app' => config('multitenancy.identifier'),
            'domain' => $tenantDomain,
        ]);

        $request = Request::create($requestUrl, 'GET', [], [], [], $requestOptions);

        $foundTenant = $this->tenantFinder->findForRequest($request);

        $this->assertInstanceOf(Tenant::class, $foundTenant);
        $this->assertEquals($registeredTenant->id, $foundTenant->id);
    }

    public static function tenantWithPathProvider(): array
    {
        return [
            'Registered tenant with path' => [
                'my-domain.com/my-path',
                'http://my-domain.com/my-path/login',
                [
                    'SCRIPT_FILENAME' => 'my-path',
                    'SCRIPT_NAME' => 'my-path',
                ],
            ],
            'Registered tenant without path and resolve request with Laravel front controller' => [
                'my-domain.com',
                'http://my-domain.com/index.php',
                [
                    'SCRIPT_FILENAME' => 'index.php',
                    'SCRIPT_NAME' => 'index',
                ],
            ],
        ];
    }
}
