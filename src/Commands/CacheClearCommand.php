<?php

namespace Placetopay\Cerberus\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Placetopay\Cerberus\Cache\CacheHandlerFactory;

class CacheClearCommand extends Command
{
    protected $signature = 'cerberus:cache-clear {--prefix= : Prefix to match the keys to clear}';

    protected $description = 'Delete all the cache entries by prefix';

    public function handle(): void
    {
        $prefix = $this->option('prefix');
        if (!$prefix) {
            $prefix = rtrim(Cache::getStore()->getPrefix(), ':');
        }

        $cacheHandler = CacheHandlerFactory::getHandler(config('cache.default'));
        if (!$cacheHandler) {
            $this->error('Cache driver not supported');
            return;
        }

        $records = $cacheHandler->clear($prefix);
        $this->info("$records records deleted");
    }

}
