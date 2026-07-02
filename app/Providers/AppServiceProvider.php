<?php

namespace App\Providers;

use App\Models\Edital;
use App\Models\EditalAttachment;
use App\Models\Manutencao;
use App\Models\Orcamento;
use App\Models\Patrimonio;
use App\Models\PatrimonioCategoria;
use App\Models\Post;
use App\Models\TransparencyDocument;
use App\Models\User;
use App\Observers\EditalAttachmentObserver;
use App\Observers\EditalObserver;
use App\Observers\PostObserver;
use App\Observers\TransparencyDocumentObserver;
use App\Policies\Patrimonio\ManutencaoPolicy;
use App\Policies\Patrimonio\OrcamentoPolicy;
use App\Policies\Patrimonio\PatrimonioCategoriaPolicy;
use App\Policies\Patrimonio\PatrimonioPolicy;
use App\Policies\Patrimonio\PatrimonioUserPolicy;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        AbstractPaginator::defaultView('vendor.pagination.bootstrap-oassab');
        AbstractPaginator::defaultSimpleView('vendor.pagination.bootstrap-oassab');

        Post::observe(PostObserver::class);
        TransparencyDocument::observe(TransparencyDocumentObserver::class);
        Edital::observe(EditalObserver::class);
        EditalAttachment::observe(EditalAttachmentObserver::class);

        Gate::policy(Patrimonio::class, PatrimonioPolicy::class);
        Gate::policy(PatrimonioCategoria::class, PatrimonioCategoriaPolicy::class);
        Gate::policy(Manutencao::class, ManutencaoPolicy::class);
        Gate::policy(Orcamento::class, OrcamentoPolicy::class);
        Gate::policy(User::class, PatrimonioUserPolicy::class);
    }
}
