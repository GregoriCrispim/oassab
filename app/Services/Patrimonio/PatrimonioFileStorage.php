<?php

namespace App\Services\Patrimonio;

use App\Models\Patrimonio;
use App\Models\PatrimonioArquivo;
use App\Support\MimeHelper;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PatrimonioFileStorage
{
    /**
     * @return array{nome_original: string, nome_arquivo: string, tipo: string|null, tamanho: int, categoria_arquivo: string}
     */
    public function storeUnidadeImagem(UploadedFile $file, Patrimonio $patrimonio, string $codigoUnidade): string
    {
        $ext = $file->getClientOriginalExtension() ?: 'bin';
        $storedName = Str::uuid().'.'.$ext;
        $relativePath = 'patrimonios/'.$patrimonio->codigo.'/unidades/'.$codigoUnidade.'/imagem/'.$storedName;

        $destination = storage_path('app/public/'.$relativePath);
        File::ensureDirectoryExists(dirname($destination));
        File::copy($file->getRealPath(), $destination);

        $this->publishPublicFile($relativePath);

        return '/storage/'.$relativePath;
    }

    public function store(UploadedFile $file, Patrimonio $patrimonio, string $categoria = 'outro'): array
    {
        $subdir = match ($categoria) {
            'imagem' => 'imagem',
            'nota_fiscal' => 'notas-fiscais',
            'documento' => 'documentos',
            default => 'outros',
        };

        $ext = $file->getClientOriginalExtension() ?: 'bin';
        $storedName = Str::uuid().'.'.$ext;
        $relativePath = 'patrimonios/'.$patrimonio->codigo.'/'.$subdir.'/'.$storedName;

        $destination = storage_path('app/public/'.$relativePath);
        File::ensureDirectoryExists(dirname($destination));
        File::copy($file->getRealPath(), $destination);

        $this->publishPublicFile($relativePath);

        return [
            'nome_original' => $file->getClientOriginalName(),
            'nome_arquivo' => '/storage/'.$relativePath,
            'tipo' => $file->getMimeType() ?: MimeHelper::fromFilename($storedName),
            'tamanho' => $file->getSize(),
            'categoria_arquivo' => $categoria,
        ];
    }

    public function deletePath(?string $filePath): void
    {
        $relative = $this->relativePath($filePath);

        if (! $relative) {
            return;
        }

        Storage::disk('public')->delete($relative);

        $publicPath = public_path('storage/'.$relative);

        if (file_exists($publicPath)) {
            File::delete($publicPath);
        }
    }

    public function deleteAttachment(PatrimonioArquivo $arquivo): void
    {
        $this->deletePath($arquivo->nome_arquivo);
    }

    public function purgeAll(Patrimonio $patrimonio): void
    {
        $folder = 'patrimonios/'.$patrimonio->codigo;

        Storage::disk('public')->deleteDirectory($folder);

        $publicFolder = public_path('storage/'.$folder);

        if (is_dir($publicFolder)) {
            File::deleteDirectory($publicFolder);
        }
    }

    public function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, '/storage/')) {
            return $path;
        }

        return '/storage/'.ltrim($path, '/');
    }

    private function relativePath(?string $filePath): ?string
    {
        if (! $filePath) {
            return null;
        }

        if (str_starts_with($filePath, '/storage/')) {
            return Str::after($filePath, '/storage/');
        }

        if (! str_contains($filePath, '..')) {
            return ltrim($filePath, '/');
        }

        return null;
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
