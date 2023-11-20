<?php

namespace Placetopay\Cerberus\Tests\Feature\Commands;

use Illuminate\Support\Facades\Cache;
use Placetopay\Cerberus\Tests\TestCase;
use Symfony\Component\Console\Command\Command;

class TestClearCacheCommand extends TestCase
{
    /**
     * @test
     */
    public function it_can_not_clear_cache_with_unsupported_driver(): void
    {
        config()->set('cache.default', 'database');
        $this->artisan('cerberus:cache-clear')
            ->assertExitCode(Command::FAILURE)
            ->expectsOutput('Cache driver not supported');
    }

    /**
     * @test
     */
    public function it_can_clear_cache_with_redis(): void
    {
        config()->set('cache.default', 'redis');
        Cache::put('some_cache_key', 'some_cache_value');

        $this->get('co.domain.com');
        $this->artisan('cerberus:cache-clear')
            ->assertExitCode(Command::SUCCESS)
            ->expectsOutput('1 records deleted');
    }

    /**
     * @test
     */
    public function it_can_clear_cache_with_redis_and_prefix_option(): void
    {
        config()->set('cache.default', 'redis');
        Cache::put('myPrefix:some_cache_key', 'some_cache_value');
        Cache::put('some_cache_key', 'some_cache_value');

        $this->get('co.domain.com');
        $this->artisan('cerberus:cache-clear --prefix=myPrefix')
            ->assertExitCode(Command::SUCCESS)
            ->expectsOutput('1 records deleted');
    }
}
