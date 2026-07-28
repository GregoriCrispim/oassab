<?php

namespace Database\Seeders;

use App\Models\TransparencyDocument;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class TransparencyDocumentsSeeder extends Seeder
{
    public function run(): void
    {
        $dataFile = base_path('app/Data/transparency-documents.php');

        if (! file_exists($dataFile)) {
            $this->command?->warn("[TransparencyDocumentsSeeder] Arquivo {$dataFile} não encontrado. Pulei.");

            return;
        }

        $documents = require $dataFile;
        $assetsDir = database_path('seeders/assets/transparency-documents');

        if (! is_dir($assetsDir)) {
            $this->command?->warn("[TransparencyDocumentsSeeder] Pasta de assets {$assetsDir} não encontrada. Pulei.");

            return;
        }

        $created = 0;

        foreach ($documents as $data) {
            $source = $assetsDir.'/'.$data['file'];

            if (! file_exists($source)) {
                $this->command?->warn("[TransparencyDocumentsSeeder] PDF não encontrado: {$source}");
                continue;
            }

            $storageFolder = 'transparency/'.$data['slug'];
            $storedName = $data['slug'].'.pdf';
            $storageRoot = storage_path('app/public/'.$storageFolder);

            File::ensureDirectoryExists($storageRoot);
            File::copy($source, $storageRoot.'/'.$storedName);
            $this->publishPublicFile($storageFolder.'/'.$storedName);

            TransparencyDocument::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'processo' => $data['processo'],
                    'valor_global' => $data['valor_global'],
                    'year' => $data['year'],
                    'file_path' => '/storage/'.$storageFolder.'/'.$storedName,
                    'original_filename' => $data['original_filename'],
                    'sort_order' => $data['sort_order'],
                    'is_published' => $data['is_published'],
                ],
            );

            $created++;
        }

        $this->command?->info("[TransparencyDocumentsSeeder] {$created} documento(s) de transparência publicados.");
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
