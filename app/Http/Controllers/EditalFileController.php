<?php

namespace App\Http\Controllers;

use App\Models\Edital;
use App\Models\EditalAttachment;
use App\Services\GoogleDriveService;
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
            return $this->streamFromDrive(
                $googleDrive,
                $edital->drive_file_id,
                $edital->original_filename ?: $edital->slug.'.pdf'
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
            return $this->streamFromDrive(
                $googleDrive,
                $attachment->drive_file_id,
                $attachment->original_filename ?: $attachment->title.'.pdf'
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
        string $downloadName
    ): StreamedResponse {
        return response()->streamDownload(function () use ($googleDrive, $fileId) {
            $stream = $googleDrive->readStream($fileId);
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, $downloadName, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
