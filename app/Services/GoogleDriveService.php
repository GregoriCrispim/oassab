<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use RuntimeException;

class GoogleDriveService
{
    private ?Drive $drive = null;

    public function isConfigured(): bool
    {
        $path = config('services.google_drive.credentials_path');

        return filled($path) && is_readable($path);
    }

    public function upload(string $localPath, string $filename, string $mimeType): string
    {
        $metadata = new DriveFile([
            'name' => $filename,
        ]);

        $folderId = config('services.google_drive.folder_id');
        if (filled($folderId)) {
            $metadata->setParents([$folderId]);
        }

        $file = $this->drive()->files->create($metadata, [
            'data' => file_get_contents($localPath),
            'mimeType' => $mimeType,
            'uploadType' => 'multipart',
            'fields' => 'id',
        ]);

        $id = $file->getId();
        if (! $id) {
            throw new RuntimeException('O Google Drive não retornou o ID do arquivo enviado.');
        }

        return $id;
    }

    /**
     * @return resource
     */
    public function readStream(string $fileId)
    {
        $response = $this->drive()->files->get($fileId, ['alt' => 'media']);

        $body = $response->getBody();
        if ($body === null) {
            throw new RuntimeException('Não foi possível ler o arquivo no Google Drive.');
        }

        $stream = $body->detach();
        if (! is_resource($stream)) {
            throw new RuntimeException('Stream inválido ao baixar do Google Drive.');
        }

        return $stream;
    }

    public function delete(string $fileId): void
    {
        $this->drive()->files->delete($fileId);
    }

    private function drive(): Drive
    {
        if ($this->drive !== null) {
            return $this->drive;
        }

        if (! $this->isConfigured()) {
            throw new RuntimeException('Google Drive não está configurado (GOOGLE_DRIVE_CREDENTIALS_PATH).');
        }

        $client = new GoogleClient;
        $client->setApplicationName((string) config('app.name'));
        $client->setScopes([Drive::DRIVE]);
        $client->setAuthConfig(config('services.google_drive.credentials_path'));

        $this->drive = new Drive($client);

        return $this->drive;
    }
}
