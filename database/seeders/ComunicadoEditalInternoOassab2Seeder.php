<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ComunicadoEditalInternoOassab2Seeder extends Seeder
{
    public function run(): void
    {
        $dataFile = base_path('app/Data/comunicado-edital-interno-oassab-2-2026.php');

        if (! file_exists($dataFile)) {
            $this->command?->warn("[ComunicadoEditalInternoOassab2Seeder] Arquivo {$dataFile} não encontrado. Pulei.");

            return;
        }

        $data = require $dataFile;
        $assetsDir = database_path('seeders/assets/'.$data['slug']);

        if (! is_dir($assetsDir)) {
            $this->command?->warn("[ComunicadoEditalInternoOassab2Seeder] Pasta de assets {$assetsDir} não encontrada. Pulei.");

            return;
        }

        $storageRoot = storage_path('app/public/posts/'.$data['slug']);
        File::ensureDirectoryExists($storageRoot);

        $coverSource = $assetsDir.'/'.$data['cover']['source'];
        $coverDest = $storageRoot.'/'.$data['cover']['stored'];

        if (! file_exists($coverSource)) {
            $this->command?->warn("[ComunicadoEditalInternoOassab2Seeder] Capa não encontrada: {$coverSource}");

            return;
        }

        File::copy($coverSource, $coverDest);
        $this->publishPublicFile('posts/'.$data['slug'].'/'.$data['cover']['stored']);

        $pdfSource = $assetsDir.'/'.$data['pdf']['source'];
        $pdfDest = $storageRoot.'/'.$data['pdf']['stored'];

        if (! file_exists($pdfSource)) {
            $this->command?->warn("[ComunicadoEditalInternoOassab2Seeder] PDF não encontrado: {$pdfSource}");

            return;
        }

        File::copy($pdfSource, $pdfDest);
        $this->publishPublicFile('posts/'.$data['slug'].'/'.$data['pdf']['stored']);

        $imageUrl = '/storage/posts/'.$data['slug'].'/'.$data['cover']['stored'];
        $imageMeta = [
            'base' => '/storage/posts/'.$data['slug'].'/'.pathinfo($data['cover']['stored'], PATHINFO_FILENAME),
            'widths' => [],
            'formats' => ['png'],
            'fallback' => true,
            'ext_default' => 'png',
        ];

        $post = Post::updateOrCreate(
            ['slug' => $data['slug']],
            [
                'title' => $data['title'],
                'excerpt' => $data['excerpt'],
                'body' => $data['body'],
                'date' => $data['date'],
                'image' => $imageUrl,
                'image_meta' => $imageMeta,
                'is_published' => true,
            ],
        );

        $categorySlugs = $data['categories'] ?? [$data['category'] ?? Category::NOTICIAS];
        $categoryIds = Category::whereIn('slug', $categorySlugs)->pluck('id');

        if ($categoryIds->isNotEmpty()) {
            $post->categories()->sync($categoryIds);
        }

        $this->command?->info('[ComunicadoEditalInternoOassab2Seeder] Comunicado publicado em /posts/'.$data['slug']);
    }

    private function publishPublicFile(string $relativePath): void
    {
        $source = storage_path('app/public/'.$relativePath);
        $dest = public_path('storage/'.$relativePath);

        if (! file_exists($source)) {
            return;
        }

        File::ensureDirectoryExists(dirname($dest));
        File::copy($source, $dest);
    }
}
