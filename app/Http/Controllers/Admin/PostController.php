<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Services\ImageOptimizer;
use App\Services\PostImageStorage;
use App\Services\PublicStoragePublisher;
use App\Support\UploadedFileHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

        try {
            $this->applyImageUpload($request, $post);
        } catch (\Throwable $e) {
            Log::error('Falha ao processar imagem do post: '.$e->getMessage(), ['exception' => $e]);

            return back()
                ->withInput()
                ->withErrors(['image' => 'Não foi possível salvar a imagem. Verifique o formato (JPG, PNG ou WEBP) e o tamanho (máx. 4 MB).']);
        }

        $post->save();
        $post->categories()->sync($request->input('categories', []));
        $this->finalizePostImage($post, UploadedFileHelper::valid($request, 'image'));

        return redirect()
            ->route('admin.posts.index')
            ->with('status', 'Post criado com sucesso.');
    }

    public function edit(Post $post): View
    {
        if (PostImageStorage::pickPrimaryRelativePath($post->slug)) {
            $this->reconcileAndPersistImage($post);
            $post->refresh();
        }

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
        $oldSlug = $post->slug;
        $post->fill($request->payload());

        if ($oldSlug !== $post->slug && ($post->image || $post->image_meta)) {
            PostImageStorage::renameFolder($oldSlug, $post->slug);
        }

        if ($request->boolean('remove_image') && ($post->image || $post->image_meta || $post->image_filename)) {
            $this->purgeImageAssets($post);
            $post->image = null;
            $post->image_meta = null;
            $post->image_filename = null;
        }

        try {
            $this->applyImageUpload($request, $post);
        } catch (\Throwable $e) {
            Log::error('Falha ao processar imagem do post: '.$e->getMessage(), ['exception' => $e]);

            return back()
                ->withInput()
                ->withErrors(['image' => 'Não foi possível salvar a imagem. Verifique o formato (JPG, PNG ou WEBP) e o tamanho (máx. 4 MB).']);
        }

        $post->save();
        $post->categories()->sync($request->input('categories', []));
        $this->finalizePostImage(
            $post,
            UploadedFileHelper::valid($request, 'image') || (! $request->boolean('remove_image') && ($post->image || PostImageStorage::pickPrimaryRelativePath($post->slug)))
        );

        return redirect()
            ->route('admin.posts.index')
            ->with('status', 'Post atualizado com sucesso.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->purgeImageAssets($post);
        $post->delete();
        PublicStoragePublisher::publish();

        return redirect()
            ->route('admin.posts.index')
            ->with('status', 'Post excluído.');
    }

    private function applyImageUpload(StorePostRequest $request, Post $post): void
    {
        if (! UploadedFileHelper::valid($request, 'image')) {
            return;
        }

        $this->purgeImageAssets($post);
        $slug = $post->slug ?: Str::slug($post->title);
        $this->optimizer->optimize($request->file('image'), $slug);
    }

    /**
     * @return array{0: ?string, 1: ?array<string, mixed>}
     */
    private function finalizePostImage(Post $post, bool $syncFromDisk): void
    {
        PublicStoragePublisher::publish();

        if (! $syncFromDisk) {
            return;
        }

        PostImageStorage::applyRecord($post);
        $post->save();
    }

    private function reconcileAndPersistImage(Post $post): void
    {
        PublicStoragePublisher::publish();
        PostImageStorage::applyRecord($post);
        $post->save();
    }

    private function purgeImageAssets(Post $post): void
    {
        $relative = PostImageStorage::relativeFromUrl($post->image)
            ?? (isset($post->image_meta['path']) ? $post->image_meta['path'] : null);

        if ($relative) {
            Storage::disk('public')->deleteDirectory(dirname($relative));

            return;
        }

        Storage::disk('public')->deleteDirectory(PostImageStorage::folder($post->slug));
    }
}
