<?php

namespace App\Console\Commands;

use App\Models\Edital;
use App\Services\ContentCache;
use App\Services\EditalFileStorage;
use Database\Seeders\StaticContentSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SyncStaticContent extends Command
{
    protected $signature = 'static-content:sync';

    protected $description = 'Sincroniza editais/posts estáticos no banco, publica storage e limpa cache';

    /** @var list<string> */
    private array $removedEditalSlugs = [
        '4-edital-interno-oassab-n-1-2026',
    ];

    public function handle(EditalFileStorage $files): int
    {
        $this->info('Sincronizando conteúdo estático no banco...');
        $this->call('db:seed', ['--class' => StaticContentSeeder::class, '--force' => true]);

        $this->removeObsoleteEditais($files);

        $this->info('Publicando storage/app/public → public/storage...');
        $this->publishStorage();

        ContentCache::flushAll();
        $this->info('Cache de conteúdo limpo.');

        $this->newLine();
        $this->info('Concluído. Confira /editais e /projetos no site.');

        return self::SUCCESS;
    }

    private function removeObsoleteEditais(EditalFileStorage $files): void
    {
        foreach ($this->removedEditalSlugs as $slug) {
            $edital = Edital::with('attachments')->where('slug', $slug)->first();

            if (! $edital) {
                continue;
            }

            if ($edital->hasMainFile()) {
                $files->deleteMain($edital);
            }

            foreach ($edital->attachments as $attachment) {
                $files->deleteAttachment($attachment);
            }

            if (! $files->usesGoogleDrive()) {
                $files->purgeAllLocal($edital);
            }

            $edital->delete();
            $this->line("  removido edital obsoleto: {$slug}");
        }
    }

    private function publishStorage(): void
    {
        $source = storage_path('app/public');
        $dest = public_path('storage');

        File::ensureDirectoryExists($dest);

        if (! File::isDirectory($source)) {
            return;
        }

        File::copyDirectory($source, $dest);
    }
}
