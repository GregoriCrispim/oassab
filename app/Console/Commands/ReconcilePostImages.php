<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\PostImageStorage;
use Illuminate\Console\Command;

/**
 * Alinha image e image_meta de cada post com os arquivos reais em storage/app/public.
 * Remove referências quebradas (404) quando o arquivo não existe no disco.
 *
 * Uso: php artisan posts:reconcile-images
 */
class ReconcilePostImages extends Command
{
    protected $signature = 'posts:reconcile-images';

    protected $description = 'Sincroniza capas dos posts com os arquivos em storage (corrige URLs 404 no banco)';

    public function handle(): int
    {
        $fixed = 0;
        $cleared = 0;

        Post::query()
            ->where(fn ($q) => $q->whereNotNull('image')->orWhereNotNull('image_meta'))
            ->orderBy('id')
            ->each(function (Post $post) use (&$fixed, &$cleared) {
                $beforeImage = $post->image;
                $url = PostImageStorage::resolveDisplayUrl($post->slug, $post->image, $post->image_meta);

                if (! $url) {
                    if ($beforeImage || $post->image_meta) {
                        $post->image = null;
                        $post->image_meta = null;
                        $post->save();
                        $cleared++;
                        $this->line("  limpo: {$post->slug} (arquivo ausente)");
                    }

                    return;
                }

                $meta = PostImageStorage::metaFromDisk($post->slug)
                    ?? (is_array($post->image_meta) ? $post->image_meta : []);

                if ($post->image !== $url || $post->image_meta !== $meta) {
                    $post->image = $url;
                    $post->image_meta = $meta;
                    $post->save();
                    $fixed++;
                    $this->line("  ok: {$post->slug} → {$url}");
                }
            });

        $this->info("Concluído: {$fixed} atualizado(s), {$cleared} referência(s) removida(s).");

        return self::SUCCESS;
    }
}
