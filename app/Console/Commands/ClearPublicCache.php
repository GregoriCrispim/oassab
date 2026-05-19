<?php

namespace App\Console\Commands;

use App\Services\ContentCache;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ClearPublicCache extends Command
{
    protected $signature = 'site:clear-cache';

    protected $description = 'Limpa cache de páginas HTML e conteúdo público (use após deploy)';

    public function handle(): int
    {
        ContentCache::flushAll();
        Artisan::call('view:clear');
        Artisan::call('cache:clear');

        $this->info('Cache público limpo (HTML, views e conteúdo).');

        return self::SUCCESS;
    }
}
