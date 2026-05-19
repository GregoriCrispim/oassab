<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
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
        $posts = ContentCache::remember('list:transparencia', fn () => Post::with('categories')
            ->published()
            ->forCategory(Category::TRANSPARENCIA)
            ->orderedRecent()
            ->get());

        return view('pages.transparencia', compact('posts'));
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

    public function post(string $slug): View
    {
        $post = ContentCache::remember(
            ContentCache::postKey($slug),
            fn () => Post::with('categories')->where('slug', $slug)->firstOrFail()
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
