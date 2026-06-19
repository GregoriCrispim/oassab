<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Edital;
use App\Models\EditalAttachment;
use App\Models\Post;
use App\Services\EditalFileStorage;
use App\Services\GoogleDriveService;
use App\Services\PostFileStorage;
use App\Support\MimeHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class PublishResultadoEdital1Command extends Command
{
    protected $signature = 'oassab:publicar-resultado-edital-1
                            {--force : Recria a notícia e o anexo mesmo se já existirem}';

    protected $description = 'Publica a notícia do resultado do 1º Edital Interno OASSAB 2026';

    public function handle(
        PostFileStorage $postFiles,
        EditalFileStorage $editalFiles,
        GoogleDriveService $drive
    ): int {
        if (! $drive->isConfigured()) {
            $this->warn('Google Drive não configurado — arquivos serão salvos localmente.');
        }

        $dataFile = base_path('app/Data/resultado-1-edital-interno-oassab-2026.php');
        if (! file_exists($dataFile)) {
            $this->error("Arquivo de dados não encontrado: {$dataFile}");

            return self::FAILURE;
        }

        $data = require $dataFile;
        $this->removeEditalAttachment($data['cleanup'], $editalFiles);
        $this->removeOldPost($data['cleanup']['old_post_slug']);

        $postData = $data['post'];
        $imageSource = base_path($postData['image_source']);
        $attachmentSource = base_path($postData['attachment_source']);

        if (! is_file($imageSource)) {
            $this->error("Imagem da notícia não encontrada: {$imageSource}");

            return self::FAILURE;
        }

        if (! is_file($attachmentSource)) {
            $this->error("Documento de resultado não encontrado: {$attachmentSource}");

            return self::FAILURE;
        }

        $categoryId = Category::query()->where('slug', $postData['category'])->value('id');
        if (! $categoryId) {
            $this->error('Categoria não encontrada: '.$postData['category']);

            return self::FAILURE;
        }

        $post = Post::query()->where('slug', $postData['slug'])->first();
        if ($post && ! $this->option('force')) {
            $this->line('Notícia já existe: '.$postData['slug']);

            return self::SUCCESS;
        }

        if ($post) {
            $postFiles->deleteAttachment($post);
            $post->delete();
        }

        $post = Post::create([
            'slug' => $postData['slug'],
            'title' => $postData['title'],
            'excerpt' => $postData['excerpt'],
            'body' => $postData['body'],
            'date' => $postData['date'],
            'edital_id' => null,
            'is_published' => true,
        ]);

        $post->categories()->sync([$categoryId]);

        $post->update([
            'image' => $postFiles->storeCoverImageFromPath($post, $imageSource, 'png'),
        ]);

        $extension = strtolower(pathinfo($postData['attachment_original_filename'], PATHINFO_EXTENSION) ?: 'docx');
        $storedName = Str::uuid()->toString().'.'.$extension;
        $stored = $postFiles->storeAttachmentFromPath(
            $attachmentSource,
            $post,
            $storedName,
            MimeHelper::fromFilename($postData['attachment_original_filename'])
        );

        $post->update([
            'attachment_file_path' => $stored['attachment_file_path'],
            'attachment_drive_file_id' => $stored['attachment_drive_file_id'],
            'attachment_original_filename' => $postData['attachment_original_filename'],
        ]);

        $this->info('Notícia publicada: /posts/'.$post->slug);
        $this->line(url('/posts/'.$post->slug));

        return self::SUCCESS;
    }

    /**
     * @param  array{edital_slug: string, edital_attachment_title: string}  $cleanup
     */
    private function removeEditalAttachment(array $cleanup, EditalFileStorage $editalFiles): void
    {
        $edital = Edital::query()->where('slug', $cleanup['edital_slug'])->first();
        if (! $edital) {
            return;
        }

        $attachment = $edital->attachments()
            ->where('title', $cleanup['edital_attachment_title'])
            ->first();

        if (! $attachment) {
            return;
        }

        $editalFiles->deleteAttachment($attachment);
        $attachment->delete();
        $this->line('Anexo removido do edital: '.$cleanup['edital_attachment_title']);
    }

    private function removeOldPost(string $oldSlug): void
    {
        $old = Post::query()->where('slug', $oldSlug)->first();
        if (! $old) {
            return;
        }

        app(PostFileStorage::class)->deleteAttachment($old);
        $old->delete();
        $this->line('Notícia antiga removida: '.$oldSlug);
    }
}
