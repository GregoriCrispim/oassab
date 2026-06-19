<?php

namespace App\Http\Controllers;

use App\Models\Edital;
use App\Models\EditalAttachment;
use App\Services\GoogleDriveService;
use App\Support\MimeHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EditalFileController extends Controller
{
    public function main(Edital $edital, GoogleDriveService $googleDrive): Response|RedirectResponse|StreamedResponse
    {
        if (! $edital->is_published && ! auth()->check()) {
            abort(404);
        }

        if ($edital->drive_file_id) {
            $filename = $edital->original_filename ?: $edital->slug.'.pdf';

            return $this->streamFromDrive(
                $googleDrive,
                $edital->drive_file_id,
                $filename,
                MimeHelper::fromFilename($filename)
            );
        }

        if ($edital->file_path) {
            return redirect($edital->file_path);
        }

        abort(404);
    }

    public function attachment(
        Edital $edital,
        EditalAttachment $attachment,
        GoogleDriveService $googleDrive
    ): Response|RedirectResponse|StreamedResponse {
        if ($attachment->edital_id !== $edital->id) {
            abort(404);
        }

        if (! $edital->is_published && ! auth()->check()) {
            abort(404);
        }

        if ($attachment->drive_file_id) {
            $filename = $attachment->original_filename ?: $attachment->title;

            return $this->streamFromDrive(
                $googleDrive,
                $attachment->drive_file_id,
                $filename,
                MimeHelper::fromFilename($filename)
            );
        }

        if ($attachment->file_path) {
            return redirect($attachment->file_path);
        }

        abort(404);
    }

    private function streamFromDrive(
        GoogleDriveService $googleDrive,
        string $fileId,
        string $downloadName,
        string $contentType = 'application/octet-stream'
    ): StreamedResponse {
        return response()->streamDownload(function () use ($googleDrive, $fileId) {
            $stream = $googleDrive->readStream($fileId);
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, $downloadName, [
            'Content-Type' => $contentType,
        ]);
    }
}
