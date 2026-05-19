<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\ImageOptimizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Reprocessa todas as imagens já existentes:
 *
 * 1. Imagens estáticas em public/images/ (hero, about, services, cta) — recompacta
 *    e gera variantes WebP + JPG em larguras múltiplas ao lado do arquivo original.
 *
 * 2. Imagens de posts vindas de uploads (estavam em /storage/posts/...). Para cada
 *    post com $post->image apontando para /storage/, lê o arquivo e regenera o
 *    conjunto de variantes via ImageOptimizer::optimizeFromPath(), atualizando
 *    image_meta no banco.
 *
 * Uso:
 *   php artisan images:optimize             (processa tudo)
 *   php artisan images:optimize --static    (só estáticas)
 *   php artisan images:optimize --posts     (só posts)
 */
class OptimizeImages extends Command
{
    protected $signature = 'images:optimize
        {--static : Processar apenas as imagens estáticas (public/images/*.jpg)}
        {--posts  : Processar apenas as imagens de posts}';

    protected $description = 'Recomprime imagens existentes e gera variantes responsivas (WebP + JPG)';

    public function handle(ImageOptimizer $optimizer): int
    {
        if (! $optimizer->hasImageDriver()) {
            $this->error('Extensão GD ou Imagick não está instalada. Instale php-gd para gerar variantes responsivas.');

            return self::FAILURE;
        }

        $onlyStatic = (bool) $this->option('static');
        $onlyPosts = (bool) $this->option('posts');
        $doStatic = ! $onlyPosts;
        $doPosts = ! $onlyStatic;

        if ($doStatic) {
            $this->processStatic($optimizer);
        }

        if ($doPosts) {
            $this->processPosts($optimizer);
        }

        // Garante que o cache de páginas e o de existência de variantes
        // (static-picture) sejam invalidados — assim a próxima visita
        // gera HTML usando as novas variantes recém-criadas.
        Cache::flush();
        $this->info('Cache invalidado.');

        $this->info('Pronto.');

        return self::SUCCESS;
    }

    private function processStatic(ImageOptimizer $optimizer): void
    {
        $this->info('=== Imagens estáticas (public/images) ===');

        $candidates = [
            public_path('images/hero-bg.jpg') => [800, 1200, 1600],
            public_path('images/about-bg.jpg') => [480, 800, 1200],
            public_path('images/services-bg.jpg') => [800, 1200, 1600],
            public_path('images/cta-bg.jpg') => [800, 1200],
        ];

        foreach ($candidates as $path => $widths) {
            if (! is_file($path)) {
                $this->warn("  - pulando (não existe): {$path}");
                continue;
            }

            $this->line("  - otimizando: {$path}");

            try {
                $result = $optimizer->optimizeStatic($path, $widths);

                if ($result === null) {
                    $this->warn('    (sem driver de imagem disponível)');
                    continue;
                }

                $this->line(sprintf(
                    '    ok — master %dx%d, %d variantes WebP + %d variantes JPG',
                    $result['width'],
                    $result['height'],
                    count($result['webp']),
                    count($result['jpg'])
                ));
            } catch (\Throwable $e) {
                $this->error('    falhou: '.$e->getMessage());
            }
        }
    }

    private function processPosts(ImageOptimizer $optimizer): void
    {
        $this->info('=== Imagens de posts ===');

        $posts = Post::query()->whereNotNull('image')->get();

        if ($posts->isEmpty()) {
            $this->line('  (nenhum post com imagem)');

            return;
        }

        foreach ($posts as $post) {
            $imagePath = $post->image;

            if (! $imagePath) {
                continue;
            }

            // Pode ser /storage/... (uploadado) ou /images/posts/... (legado).
            $abs = $this->resolveAbsolutePath($imagePath);

            if (! $abs || ! is_file($abs)) {
                $this->warn("  - [{$post->slug}] arquivo não encontrado: {$imagePath}");
                continue;
            }

            $this->line("  - otimizando post: {$post->slug}");

            try {
                $meta = $optimizer->optimizeFromPath($abs, $post->slug);

                if (! empty($meta['widths'])) {
                    $defaultW = $meta['widths'][(int) floor(count($meta['widths']) / 2)] ?? end($meta['widths']);
                    $url = $meta['base'].'-'.$defaultW.'.'.$meta['ext_default'];
                } else {
                    $url = $meta['base'].'.'.$meta['ext_default'];
                }

                // Save sem disparar observer (já estamos em processo controlado).
                Post::withoutEvents(function () use ($post, $url, $meta) {
                    $post->image = $url;
                    $post->image_meta = $meta;
                    $post->save();
                });

                $this->line(sprintf(
                    '    ok — master %dx%d, %d variantes',
                    $meta['width'],
                    $meta['height'],
                    count($meta['widths'])
                ));
            } catch (\Throwable $e) {
                $this->error('    falhou: '.$e->getMessage());
            }
        }
    }

    private function resolveAbsolutePath(string $url): ?string
    {
        if (Str::startsWith($url, '/storage/')) {
            $relative = Str::after($url, '/storage/');

            return Storage::disk('public')->path($relative);
        }

        if (Str::startsWith($url, '/')) {
            return public_path(ltrim($url, '/'));
        }

        return null;
    }
}
