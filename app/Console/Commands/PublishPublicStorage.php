<?php

namespace App\Console\Commands;

use App\Services\PublicStoragePublisher;
use Illuminate\Console\Command;

class PublishPublicStorage extends Command
{
    protected $signature = 'storage:publish';

    protected $description = 'Espelha storage/app/public em public/storage (mesmo comportamento do deploy)';

    public function handle(): int
    {
        PublicStoragePublisher::publish();
        $this->info('public/storage/ atualizado a partir de storage/app/public/.');

        return self::SUCCESS;
    }
}
