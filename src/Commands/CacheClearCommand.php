<?php

namespace Placetopay\Cerberus\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Placetopay\Cerberus\Cache\CacheHandlerFactory;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class CacheClearCommand extends Command
{
    protected $signature = 'cerberus:cache-clear {--prefix= : Prefix to match the keys to clear}';

    protected $description = 'Delete all the cache entries by prefix';

    public function handle(): int
    {
        $prefix = $this->option('prefix');
        if (!$prefix) {
            $prefix = rtrim(Cache::getStore()->getPrefix(), ':');
        }

        $cacheHandler = CacheHandlerFactory::getHandler(config('cache.default'));

        if (!$cacheHandler) {
            $this->error('Cache driver not supported');
            return SymfonyCommand::FAILURE;
        }

        $records = $cacheHandler->clear($prefix);
        $this->info("$records records deleted");

        return SymfonyCommand::SUCCESS;
    }
}
