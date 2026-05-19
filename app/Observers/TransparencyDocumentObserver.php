<?php

namespace App\Observers;

use App\Models\TransparencyDocument;
use App\Services\ContentCache;

class TransparencyDocumentObserver
{
    public function saved(TransparencyDocument $document): void
    {
        ContentCache::flushAll();
    }

    public function deleted(TransparencyDocument $document): void
    {
        ContentCache::flushAll();
    }
}
