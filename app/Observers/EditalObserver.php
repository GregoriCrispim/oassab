<?php

namespace App\Observers;

use App\Models\Edital;
use App\Services\ContentCache;

class EditalObserver
{
    public function saved(Edital $edital): void
    {
        ContentCache::flushAll();
    }

    public function deleted(Edital $edital): void
    {
        ContentCache::flushAll();
    }
}
