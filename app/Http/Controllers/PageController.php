<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Edital;
use App\Models\Post;
use App\Models\TransparencyDocument;
use App\Services\ContentCache;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        $latestNews = ContentCache::remember('home:latest', fn () => Post::with('categories')
            ->published()
            ->forCategory(Category::NOTICIAS)
            ->orderedRecent()
            ->limit(3)
            ->get());

        return view('pages.home', compact('latestNews'));
    }

    public function quemSomos(): View
    {
        return view('pages.quem-somos');
    }

    public function projetos(): View
    {
        $posts = ContentCache::remember('list:projetos', fn () => Post::with('categories')
            ->published()
            ->forCategory(Category::PROJETOS)
            ->orderedRecent()
            ->get());

        return view('pages.projetos', compact('posts'));
    }

    public function transparencia(): View
    {
        $documents = ContentCache::remember('list:transparency-documents', fn () => TransparencyDocument::query()
            ->published()
            ->ordered()
            ->get());

        return view('pages.transparencia', compact('documents'));
    }

    public function contato(): View
    {
        return view('pages.contato');
    }

    public function noticias(): View
    {
        $posts = ContentCache::remember('list:noticias', fn () => Post::with('categories')
            ->published()
            ->forCategory(Category::NOTICIAS)
            ->orderedRecent()
            ->get());

        return view('pages.noticias', compact('posts'));
    }

    public function relatorios(): View
    {
        return view('pages.relatorios');
    }

    public function editais(): View
    {
        $editais = ContentCache::remember('list:editais', fn () => Edital::query()
            ->withCount('attachments')
            ->published()
            ->ordered()
            ->get());

        return view('pages.editais', compact('editais'));
    }

    public function edital(Edital $edital): View
    {
        abort_unless($edital->is_published, 404);

        $slug = $edital->slug;

        $edital = ContentCache::remember(
            ContentCache::editalKey($slug),
            fn () => Edital::with('attachments')->where('slug', $slug)->firstOrFail()
        );

        $neighbors = ContentCache::remember(
            ContentCache::editalNeighborsKey($edital->slug),
            fn () => [
                'previous' => $edital->previous(),
                'next' => $edital->next(),
            ]
        );

        return view('pages.edital', [
            'edital' => $edital,
            'previous' => $neighbors['previous'],
            'next' => $neighbors['next'],
        ]);
    }

    public function post(string $slug): View
    {
        $post = ContentCache::remember(
            ContentCache::postKey($slug),
            fn () => Post::with(['categories', 'edital'])->where('slug', $slug)->firstOrFail()
        );

        $primary = $post->primaryCategorySlug();

        $neighbors = ContentCache::remember(
            ContentCache::neighborsKey($slug),
            fn () => [
                'previous' => $post->previousFor($primary),
                'next' => $post->nextFor($primary),
            ]
        );

        return view('pages.post', [
            'post' => $post,
            'previous' => $neighbors['previous'],
            'next' => $neighbors['next'],
        ]);
    }
}
