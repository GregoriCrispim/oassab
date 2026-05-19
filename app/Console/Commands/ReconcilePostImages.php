<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\ContentCache;
use App\Services\PostImageStorage;
use App\Services\PublicStoragePublisher;
use Illuminate\Console\Command;

/**
 * Alinha image, image_filename e image_meta com os arquivos reais no disco.
 */
class ReconcilePostImages extends Command
{
    protected $signature = 'posts:reconcile-images';

    protected $description = 'Sincroniza capas dos posts com os arquivos em storage (corrige URLs 404 no banco)';

    public function handle(): int
    {
        $fixed = 0;
        $cleared = 0;

        PublicStoragePublisher::publish();

        Post::query()
            ->orderBy('id')
            ->each(function (Post $post) use (&$fixed, &$cleared) {
                PostImageStorage::relocateLegacyFiles($post->slug);

                $beforeImage = $post->image;
                PostImageStorage::applyRecord($post);

                if (! $post->image) {
                    if ($beforeImage || $post->image_meta || $post->image_filename) {
                        $post->save();
                        $cleared++;
                        $this->line("  limpo: {$post->slug}");
                    }

                    return;
                }

                if ($post->isDirty()) {
                    $post->save();
                    $fixed++;
                    $this->line("  ok: {$post->slug} → {$post->image_filename}");
                }
            });

        ContentCache::flushAll();

        $this->info("Concluído: {$fixed} atualizado(s), {$cleared} referência(s) removida(s). Cache HTML limpo.");

        return self::SUCCESS;
    }
}
