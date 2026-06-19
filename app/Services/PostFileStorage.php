<?php

namespace App\Services;

use App\Models\Post;
use App\Support\MimeHelper;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PostFileStorage
{
    public function __construct(
        private readonly GoogleDriveService $googleDrive,
    ) {}

    public function usesGoogleDrive(): bool
    {
        return $this->googleDrive->isConfigured();
    }

    public function storeAttachmentFromPath(
        string $path,
        Post $post,
        string $storedName,
        ?string $mimeType = null
    ): array {
        $mimeType ??= MimeHelper::fromFilename($storedName);

        if ($this->usesGoogleDrive()) {
            return [
                'attachment_drive_file_id' => $this->googleDrive->upload(
                    $path,
                    'posts/'.$post->slug.'/'.$storedName,
                    $mimeType
                ),
                'attachment_file_path' => null,
            ];
        }

        $relativePath = 'posts/'.$post->slug.'/anexos/'.$storedName;
        $destination = storage_path('app/public/'.$relativePath);
        File::ensureDirectoryExists(dirname($destination));
        File::copy($path, $destination);
        $this->publishPublicFile($relativePath);

        return [
            'attachment_drive_file_id' => null,
            'attachment_file_path' => '/storage/'.$relativePath,
        ];
    }

    public function storeAttachment(UploadedFile $file, Post $post): array
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: 'pdf');
        $storedName = Str::uuid()->toString().'.'.$extension;

        $stored = $this->storeAttachmentFromPath(
            $file->getRealPath(),
            $post,
            $storedName,
            $file->getMimeType() ?: MimeHelper::fromFilename($storedName)
        );

        $stored['attachment_original_filename'] = $file->getClientOriginalName();

        return $stored;
    }

    public function deleteAttachment(Post $post): void
    {
        if ($post->attachment_drive_file_id) {
            $this->googleDrive->delete($post->attachment_drive_file_id);

            return;
        }

        if (! $post->attachment_file_path || ! Str::startsWith($post->attachment_file_path, '/storage/')) {
            return;
        }

        $relative = Str::after($post->attachment_file_path, '/storage/');
        File::delete(storage_path('app/public/'.$relative));
        File::delete(public_path('storage/'.$relative));
    }

    public function storeCoverImageFromPath(Post $post, string $sourcePath, string $extension = 'png'): string
    {
        $relativePath = 'posts/'.$post->slug.'/'.$post->slug.'.'.$extension;
        $destination = storage_path('app/public/'.$relativePath);
        File::ensureDirectoryExists(dirname($destination));
        File::copy($sourcePath, $destination);
        $this->publishPublicFile($relativePath);

        return '/storage/'.$relativePath;
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
