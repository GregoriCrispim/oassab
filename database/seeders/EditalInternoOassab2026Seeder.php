<?php

namespace Database\Seeders;

use App\Models\Edital;
use App\Models\EditalAttachment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class EditalInternoOassab2026Seeder extends Seeder
{
    public function run(): void
    {
        $dataFile = base_path('app/Data/edital-interno-oassab-2026.php');

        if (! file_exists($dataFile)) {
            $this->command?->warn("[EditalInternoOassab2026Seeder] Arquivo {$dataFile} não encontrado. Pulei.");

            return;
        }

        $data = require $dataFile;
        $assetsDir = database_path('seeders/assets/'.$data['slug']);

        if (! is_dir($assetsDir)) {
            $this->command?->warn("[EditalInternoOassab2026Seeder] Pasta de assets {$assetsDir} não encontrada. Pulei.");

            return;
        }

        $storageRoot = storage_path('app/public/editais/'.$data['slug']);
        $anexosDir = $storageRoot.'/anexos';

        File::ensureDirectoryExists($anexosDir);

        $mainSource = $assetsDir.'/'.$data['main_file']['source'];
        $mainDest = $storageRoot.'/'.$data['main_file']['stored'];

        if (! file_exists($mainSource)) {
            $this->command?->warn("[EditalInternoOassab2026Seeder] PDF principal não encontrado: {$mainSource}");

            return;
        }

        File::copy($mainSource, $mainDest);
        $this->publishPublicFile('editais/'.$data['slug'].'/'.$data['main_file']['stored']);

        $edital = Edital::updateOrCreate(
            ['slug' => $data['slug']],
            [
                'title' => $data['title'],
                'excerpt' => $data['excerpt'],
                'body' => $data['body'],
                'date' => $data['date'],
                'sort_order' => $data['sort_order'],
                'is_published' => $data['is_published'],
                'drive_file_id' => null,
                'file_path' => '/storage/editais/'.$data['slug'].'/'.$data['main_file']['stored'],
                'original_filename' => $data['main_file']['original_filename'],
            ],
        );

        foreach ($data['attachments'] as $attachmentData) {
            $source = $assetsDir.'/'.$attachmentData['source'];
            $dest = $anexosDir.'/'.$attachmentData['stored'];

            if (! file_exists($source)) {
                $this->command?->warn("[EditalInternoOassab2026Seeder] Anexo não encontrado: {$source}");
                continue;
            }

            File::copy($source, $dest);
            $relative = 'editais/'.$data['slug'].'/anexos/'.$attachmentData['stored'];
            $this->publishPublicFile($relative);

            EditalAttachment::updateOrCreate(
                [
                    'edital_id' => $edital->id,
                    'title' => $attachmentData['title'],
                ],
                [
                    'drive_file_id' => null,
                    'file_path' => '/storage/'.$relative,
                    'original_filename' => $attachmentData['original_filename'],
                    'sort_order' => $attachmentData['sort_order'],
                ],
            );
        }

        $this->command?->info('[EditalInternoOassab2026Seeder] Edital "'.$data['title'].'" publicado em /editais/'.$data['slug']);
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
