<?php

namespace Placetopay\Cerberus\Commands;

use Illuminate\Console\Command;
use Placetopay\Cerberus\Cache\CacheHandler;
use Placetopay\Cerberus\Cache\RedisHandler;

class CacheClearCommand extends Command
{
    protected $signature = 'cerberus:cache-clear {--prefix=* : Prefix to match the keys to clear}';

    protected $description = 'Delete all the cache entries by the given prefix';

    public function handle(): void
    {
        $prefix = $this->option('prefix');
        if (!$prefix) {
            $this->error('You must provide a prefix');
            return;
        }

        $cacheHandler = $this->getCacheHandler();
        if (!$cacheHandler) {
            $this->error('Cache driver not supported');
            return;
        }
        $cacheHandler->clear($prefix);
        $this->info('Cache cleared');
    }

    private function getCacheHandler(): CacheHandler|null
    {
        return match (config('cache.default')) {
            'redis' => app(RedisHandler::class),
            default => null
        };
    }
}
