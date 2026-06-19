<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Edital;
use App\Models\EditalAttachment;
use App\Models\Post;
use App\Services\EditalFileStorage;
use App\Services\GoogleDriveService;
use App\Support\MimeHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class PublishResultadoEdital4Command extends Command
{
    protected $signature = 'oassab:publicar-resultado-edital-4
                            {--force : Recria anexo e notícia mesmo se já existirem}';

    protected $description = 'Publica a notícia do resultado do 4º edital e anexa o documento no edital (Google Drive)';

    public function handle(EditalFileStorage $files, GoogleDriveService $drive): int
    {
        if (! $drive->isConfigured()) {
            $this->warn('Google Drive não configurado — o anexo será salvo localmente. Configure GOOGLE_DRIVE_* no .env e execute com --force para enviar ao Drive.');
        }

        $dataFile = base_path('app/Data/resultado-4-edital-interno-oassab-2026.php');
        if (! file_exists($dataFile)) {
            $this->error("Arquivo de dados não encontrado: {$dataFile}");

            return self::FAILURE;
        }

        $data = require $dataFile;
        $sourcePath = base_path($data['attachment']['source']);

        if (! is_file($sourcePath)) {
            $this->error("Documento de resultado não encontrado: {$sourcePath}");

            return self::FAILURE;
        }

        $edital = Edital::query()->where('slug', $data['edital_slug'])->first();
        if (! $edital) {
            $this->error('Edital não encontrado: '.$data['edital_slug']);

            return self::FAILURE;
        }

        $attachmentTitle = $data['attachment']['title'];
        $existingAttachment = $edital->attachments()
            ->where('title', $attachmentTitle)
            ->first();

        if ($existingAttachment && ! $this->option('force')) {
            $this->line("Anexo já existe: {$attachmentTitle}");
        } else {
            if ($existingAttachment) {
                $files->deleteAttachment($existingAttachment);
                $existingAttachment->delete();
            }

            $originalFilename = $data['attachment']['original_filename'];
            $extension = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION) ?: 'docx');
            $storedName = Str::uuid()->toString().'.'.$extension;

            $stored = $files->storeAttachmentFromPath(
                $sourcePath,
                $edital,
                $storedName,
                MimeHelper::fromFilename($originalFilename)
            );

            EditalAttachment::create([
                'edital_id' => $edital->id,
                'title' => $attachmentTitle,
                'file_path' => $stored['file_path'],
                'drive_file_id' => $stored['drive_file_id'],
                'original_filename' => $data['attachment']['original_filename'],
                'sort_order' => $data['attachment']['sort_order'],
            ]);

            $this->info($drive->isConfigured()
                ? 'Anexo enviado ao Google Drive e vinculado ao edital.'
                : 'Anexo salvo localmente e vinculado ao edital.');
        }

        $postData = $data['post'];
        $categoryId = Category::query()->where('slug', $postData['category'])->value('id');

        if (! $categoryId) {
            $this->error('Categoria não encontrada: '.$postData['category']);

            return self::FAILURE;
        }

        $post = Post::query()->where('slug', $postData['slug'])->first();

        if ($post && ! $this->option('force')) {
            if (! $post->edital_id) {
                $post->update(['edital_id' => $edital->id]);
            }
            $this->line('Notícia já existe: '.$postData['slug']);
        } else {
            if ($post) {
                $post->delete();
            }

            $post = Post::create([
                'slug' => $postData['slug'],
                'title' => $postData['title'],
                'excerpt' => $postData['excerpt'],
                'body' => $postData['body'],
                'date' => $postData['date'],
                'image' => $postData['image'],
                'edital_id' => $edital->id,
                'is_published' => true,
            ]);

            $post->categories()->sync([$categoryId]);
            $this->info('Notícia publicada: /posts/'.$post->slug);
        }

        $this->newLine();
        $this->line('Edital: '.url('/editais/'.$edital->slug));
        $this->line('Notícia: '.url('/posts/'.$postData['slug']));

        return self::SUCCESS;
    }
}
