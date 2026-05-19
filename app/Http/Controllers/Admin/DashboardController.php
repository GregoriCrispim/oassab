<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalPosts = Post::count();
        $publishedPosts = Post::published()->count();
        $draftPosts = $totalPosts - $publishedPosts;

        $byCategory = Category::withCount('posts')->orderBy('name')->get();

        $recentPosts = Post::with('categories')
            ->orderedRecent()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalPosts',
            'publishedPosts',
            'draftPosts',
            'byCategory',
            'recentPosts',
        ));
    }
}
