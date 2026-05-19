<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\PostImageStorage;
use App\Services\PublicStoragePublisher;
use Illuminate\Console\Command;

/**
 * Corrige posts com imagens fora de posts/{slug}/ e republica public/storage.
 */
class NormalizePostStorage extends Command
{
    protected $signature = 'posts:normalize-storage {--slug= : Apenas um post}';

    protected $description = 'Move capas legadas para posts/{slug}/ e atualiza referências no banco';

    public function handle(): int
    {
        $query = Post::query();

        if ($slug = $this->option('slug')) {
            $query->where('slug', $slug);
        }

        $relocated = 0;
        $updated = 0;

        $query->each(function (Post $post) use (&$relocated, &$updated) {
            if (PostImageStorage::relocateLegacyFiles($post->slug)) {
                $relocated++;
                $this->line("  movido → posts/{$post->slug}/");
            }

            $before = $post->image;
            PostImageStorage::applyRecord($post);

            if ($post->image !== $before || $post->isDirty()) {
                $post->save();
                $updated++;
                $this->line('  BD: '.$post->slug.' → '.($post->image_filename ?? 'sem imagem'));
            }
        });

        PublicStoragePublisher::publish();

        $this->info("Concluído: {$relocated} pasta(s) reorganizada(s), {$updated} post(s) atualizado(s).");

        return self::SUCCESS;
    }
}
