<?php

namespace App\Observers;

use App\Models\EditalAttachment;
use App\Services\ContentCache;

class EditalAttachmentObserver
{
    public function saved(EditalAttachment $attachment): void
    {
        ContentCache::flushAll();
    }

    public function deleted(EditalAttachment $attachment): void
    {
        ContentCache::flushAll();
    }
}
