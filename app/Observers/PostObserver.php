<?php

namespace App\Observers;

use App\Models\Post;
use App\Services\ContentCache;

class PostObserver
{
    public function saved(Post $post): void
    {
        ContentCache::flushAll();
    }

    public function deleted(Post $post): void
    {
        ContentCache::flushAll();
    }

    public function restored(Post $post): void
    {
        ContentCache::flushAll();
    }
}
