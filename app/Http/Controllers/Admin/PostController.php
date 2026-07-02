<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostRequest;
use App\Models\Category;
use App\Models\Edital;
use App\Models\Post;
use App\Services\ImageOptimizer;
use App\Services\PostImageStorage;
use App\Support\PaginationPerPage;
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

        $posts = $query->paginate(PaginationPerPage::resolve($request, 10))->withQueryString();
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
            'editais' => Edital::query()->ordered()->get(['id', 'title', 'slug']),
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

        if (! UploadedFileHelper::valid($request, 'image')) {
            $this->reconcileAndPersistImage($post);
        }

        return redirect()
            ->route('admin.posts.index')
            ->with('status', 'Post criado com sucesso.');
    }

    public function edit(Post $post): View
    {
        if ($post->image || $post->image_meta || PostImageStorage::metaFromDisk($post->slug)) {
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
            'editais' => Edital::query()->ordered()->get(['id', 'title', 'slug']),
        ]);
    }

    public function update(StorePostRequest $request, Post $post): RedirectResponse
    {
        $oldSlug = $post->slug;
        $post->fill($request->payload());

        if ($oldSlug !== $post->slug && ($post->image || $post->image_meta)) {
            PostImageStorage::renameFolder($oldSlug, $post->slug);
            [$post->image, $post->image_meta] = $this->reconcileStoredImage($post);
        }

        if ($request->boolean('remove_image') && ($post->image || $post->image_meta)) {
            $this->purgeImageAssets($post);
            $post->image = null;
            $post->image_meta = null;
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

        if (! UploadedFileHelper::valid($request, 'image') && ! $request->boolean('remove_image')) {
            $this->reconcileAndPersistImage($post);
        }

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

    private function applyImageUpload(StorePostRequest $request, Post $post): void
    {
        if (! UploadedFileHelper::valid($request, 'image')) {
            return;
        }

        $this->purgeImageAssets($post);
        [$post->image, $post->image_meta] = $this->processImage($request, $post);
    }

    /**
     * @return array{0: string, 1: array<string, mixed>}  [imageUrl, imageMeta]
     */
    private function processImage(StorePostRequest $request, Post $post): array
    {
        $file = $request->file('image');
        $slug = $post->slug ?: Str::slug($post->title);

        $meta = $this->optimizer->optimize($file, $slug);

        return PostImageStorage::normalizeAfterUpload($slug, $meta);
    }

    /**
     * @return array{0: ?string, 1: ?array<string, mixed>}
     */
    private function reconcileStoredImage(Post $post): array
    {
        $url = PostImageStorage::resolveDisplayUrl($post->slug, $post->image, $post->image_meta);

        if (! $url) {
            return [null, null];
        }

        $meta = PostImageStorage::metaFromDisk($post->slug)
            ?? (is_array($post->image_meta) ? $post->image_meta : []);

        return [$url, $meta];
    }

    private function reconcileAndPersistImage(Post $post): void
    {
        [$url, $meta] = $this->reconcileStoredImage($post);

        if (! $url) {
            $post->image = null;
            $post->image_meta = null;
        } else {
            $post->image = $url;
            $post->image_meta = $meta;
        }

        $post->save();
    }

    private function purgeImageAssets(Post $post): void
    {
        $base = $post->image_meta['base'] ?? null;

        if ($base && Str::startsWith($base, '/storage/')) {
            $relativeFolder = dirname(Str::after($base, '/storage/'));
            Storage::disk('public')->deleteDirectory($relativeFolder);

            return;
        }

        if ($post->image && Str::startsWith($post->image, '/storage/')) {
            $relative = Str::after($post->image, '/storage/');
            Storage::disk('public')->delete($relative);
        }
    }
}
