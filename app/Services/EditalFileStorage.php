<?php

namespace App\Services;

use App\Models\Edital;
use App\Models\EditalAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EditalFileStorage
{
    public function __construct(
        private readonly GoogleDriveService $googleDrive,
    ) {}

    public function usesGoogleDrive(): bool
    {
        return $this->googleDrive->isConfigured();
    }

    public function storeMainPdf(UploadedFile $file, Edital $edital): void
    {
        if ($this->usesGoogleDrive()) {
            $edital->drive_file_id = $this->googleDrive->upload(
                $file->getRealPath(),
                $edital->slug.'.pdf',
                'application/pdf'
            );
            $edital->file_path = null;
            $edital->original_filename = $file->getClientOriginalName();

            return;
        }

        $relativePath = Storage::disk('public')->putFileAs(
            'editais/'.$edital->slug,
            $file,
            $edital->slug.'.pdf'
        );

        if (! $relativePath) {
            throw new \RuntimeException('Não foi possível salvar o PDF do edital.');
        }

        $edital->drive_file_id = null;
        $edital->file_path = '/storage/'.$relativePath;
        $edital->original_filename = $file->getClientOriginalName();
    }

    /**
     * @return array{drive_file_id: ?string, file_path: string}
     */
    public function storeAttachmentPdf(UploadedFile $file, Edital $edital, string $storedName): array
    {
        if ($this->usesGoogleDrive()) {
            return [
                'drive_file_id' => $this->googleDrive->upload(
                    $file->getRealPath(),
                    $edital->slug.'/'.$storedName,
                    'application/pdf'
                ),
                'file_path' => '',
            ];
        }

        $relativePath = Storage::disk('public')->putFileAs(
            'editais/'.$edital->slug.'/anexos',
            $file,
            $storedName
        );

        if (! $relativePath) {
            throw new \RuntimeException('Não foi possível salvar o anexo.');
        }

        return [
            'drive_file_id' => null,
            'file_path' => '/storage/'.$relativePath,
        ];
    }

    public function deleteMain(Edital $edital): void
    {
        if ($edital->drive_file_id) {
            $this->googleDrive->delete($edital->drive_file_id);

            return;
        }

        if (! $edital->file_path || ! Str::startsWith($edital->file_path, '/storage/')) {
            return;
        }

        $relative = Str::after($edital->file_path, '/storage/');
        Storage::disk('public')->delete($relative);
    }

    public function deleteAttachment(EditalAttachment $attachment): void
    {
        if ($attachment->drive_file_id) {
            $this->googleDrive->delete($attachment->drive_file_id);

            return;
        }

        if (! $attachment->file_path || ! Str::startsWith($attachment->file_path, '/storage/')) {
            return;
        }

        $relative = Str::after($attachment->file_path, '/storage/');
        Storage::disk('public')->delete($relative);
    }

    public function purgeAllLocal(Edital $edital): void
    {
        Storage::disk('public')->deleteDirectory('editais/'.$edital->slug);
    }

    public function renameLocalFolder(Edital $edital, string $oldSlug): void
    {
        $oldFolder = 'editais/'.$oldSlug;
        $newFolder = 'editais/'.$edital->slug;

        if (! Storage::disk('public')->exists($oldFolder)) {
            return;
        }

        Storage::disk('public')->move($oldFolder, $newFolder);

        if ($edital->file_path) {
            $edital->file_path = '/storage/'.$newFolder.'/'.$edital->slug.'.pdf';
        }

        foreach ($edital->attachments as $attachment) {
            if (Str::startsWith($attachment->file_path, '/storage/')) {
                $relative = Str::after($attachment->file_path, '/storage/');
                $newRelative = preg_replace(
                    '#^editais/'.preg_quote($oldSlug, '#').'#',
                    'editais/'.$edital->slug,
                    $relative
                );
                $attachment->update(['file_path' => '/storage/'.$newRelative]);
            }
        }
    }
}
