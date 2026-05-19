<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Services\ImageOptimizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PostController extends Controller
{
    public function __construct(private readonly ImageOptimizer $optimizer)
    {
    }

    public function index(Request $request): View
    {
        $query = Post::with('categories')->orderedRecent();

        if ($category = $request->string('category')->toString()) {
            $query->forCategory($category);
        }

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $posts = $query->paginate(15)->withQueryString();
        $categories = Category::assignableToPosts()->orderBy('name')->get();

        return view('admin.posts.index', [
            'posts' => $posts,
            'categories' => $categories,
            'currentCategory' => $request->string('category')->toString(),
            'currentSearch' => $request->string('search')->toString(),
        ]);
    }

    public function create(): View
    {
        $post = new Post([
            'date' => now()->toDateString(),
            'is_published' => true,
        ]);

        return view('admin.posts.form', [
            'post' => $post,
            'categories' => Category::assignableToPosts()->orderBy('name')->get(),
            'selectedCategoryIds' => [],
        ]);
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $post = new Post($request->payload());

        if ($request->hasFile('image')) {
            [$post->image, $post->image_meta] = $this->processImage($request, $post);
        }

        $post->save();
        $post->categories()->sync($request->input('categories', []));

        return redirect()
            ->route('admin.posts.index')
            ->with('status', 'Post criado com sucesso.');
    }

    public function edit(Post $post): View
    {
        return view('admin.posts.form', [
            'post' => $post,
            'categories' => Category::assignableToPosts()->orderBy('name')->get(),
            'selectedCategoryIds' => $post->categories
                ->where('slug', '!=', Category::TRANSPARENCIA)
                ->pluck('id')
                ->all(),
        ]);
    }

    public function update(StorePostRequest $request, Post $post): RedirectResponse
    {
        $post->fill($request->payload());

        if ($request->boolean('remove_image') && ($post->image || $post->image_meta)) {
            $this->purgeImageAssets($post);
            $post->image = null;
            $post->image_meta = null;
        }

        if ($request->hasFile('image')) {
            $this->purgeImageAssets($post);
            [$post->image, $post->image_meta] = $this->processImage($request, $post);
        }

        $post->save();
        $post->categories()->sync($request->input('categories', []));

        return redirect()
            ->route('admin.posts.index')
            ->with('status', 'Post atualizado com sucesso.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->purgeImageAssets($post);
        $post->delete();

        return redirect()
            ->route('admin.posts.index')
            ->with('status', 'Post excluído.');
    }

    /**
     * @return array{0: string, 1: array<string, mixed>}  [imageUrl, imageMeta]
     */
    private function processImage(StorePostRequest $request, Post $post): array
    {
        $file = $request->file('image');
        $slug = $post->slug ?: Str::slug($post->title);

        $meta = $this->optimizer->optimize($file, $slug);

        $defaultExt = $meta['ext_default'] ?? 'jpg';
        $widths = $meta['widths'] ?? [];

        if (! empty($widths)) {
            $defaultW = $widths[(int) floor(count($widths) / 2)] ?? end($widths);
            $url = $meta['base'].'-'.$defaultW.'.'.$defaultExt;
        } else {
            // fallback (sem GD/Imagick): arquivo único.
            $url = $meta['base'].'.'.$defaultExt;
        }

        return [$url, $meta];
    }

    private function purgeImageAssets(Post $post): void
    {
        $base = $post->image_meta['base'] ?? null;

        if ($base && Str::startsWith($base, '/storage/')) {
            $relativeFolder = dirname(Str::after($base, '/storage/'));
            Storage::disk('public')->deleteDirectory($relativeFolder);

            return;
        }

        // Legado: imagem única gravada em storage/app/public/posts/...
        if ($post->image && Str::startsWith($post->image, '/storage/')) {
            $relative = Str::after($post->image, '/storage/');
            Storage::disk('public')->delete($relative);
        }
    }
}
