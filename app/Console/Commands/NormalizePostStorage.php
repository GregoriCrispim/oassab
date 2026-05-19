<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\PostImageStorage;
use App\Services\PublicStoragePublisher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Corrige posts com imagens fora de posts/{slug}/ e republica public/storage.
 */
class NormalizePostStorage extends Command
{
    protected $signature = 'posts:normalize-storage {--slug= : Apenas um post}';

    protected $description = 'Move capas legadas para posts/{slug}/ e atualiza referências no banco';

    public function handle(): int
    {
        $query = Post::query()->where(fn ($q) => $q->whereNotNull('image')->orWhereNotNull('image_meta'));

        if ($slug = $this->option('slug')) {
            $query->where('slug', $slug);
        }

        $relocated = 0;
        $updated = 0;

        $query->each(function (Post $post) use (&$relocated, &$updated) {
            if ($this->relocateLegacyFiles($post->slug)) {
                $relocated++;
                $this->line("  movido → posts/{$post->slug}/");
            }

            $url = PostImageStorage::resolveDisplayUrl($post->slug, $post->image, $post->image_meta);

            if (! $url) {
                return;
            }

            $meta = PostImageStorage::metaFromDisk($post->slug) ?? $post->image_meta;

            if ($post->image !== $url || $post->image_meta !== $meta) {
                $post->image = $url;
                $post->image_meta = $meta;
                $post->save();
                $updated++;
                $this->line("  BD: {$post->slug} → {$url}");
            }
        });

        PublicStoragePublisher::publish();

        $this->info("Concluído: {$relocated} pasta(s) reorganizada(s), {$updated} post(s) atualizado(s). public/storage republicado.");

        return self::SUCCESS;
    }

    private function relocateLegacyFiles(string $slug): bool
    {
        $disk = Storage::disk('public');
        $target = PostImageStorage::folder($slug);

        if ($disk->exists($target)) {
            return false;
        }

        $moved = false;

        foreach ($disk->files('') as $file) {
            $name = basename($file);
            if (! preg_match('/^'.preg_quote($slug, '/').'(-\d+)?\.(jpe?g|webp|png)$/i', $name)) {
                continue;
            }

            if (! $moved) {
                $disk->makeDirectory($target);
            }

            $disk->move($file, $target.'/'.$name);
            $moved = true;
        }

        return $moved;
    }
}
