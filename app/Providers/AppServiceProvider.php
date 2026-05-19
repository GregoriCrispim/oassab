<?php

namespace App\Providers;

use App\Models\Edital;
use App\Models\EditalAttachment;
use App\Models\Post;
use App\Models\TransparencyDocument;
use App\Observers\EditalAttachmentObserver;
use App\Observers\EditalObserver;
use App\Observers\PostObserver;
use App\Observers\TransparencyDocumentObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Post::observe(PostObserver::class);
        TransparencyDocument::observe(TransparencyDocumentObserver::class);
        Edital::observe(EditalObserver::class);
        EditalAttachment::observe(EditalAttachmentObserver::class);
    }
}
