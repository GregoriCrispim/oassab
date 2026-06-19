<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\GoogleDriveService;
use App\Support\MimeHelper;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PostFileController extends Controller
{
    public function attachment(Post $post, GoogleDriveService $googleDrive): RedirectResponse|StreamedResponse
    {
        abort_unless($post->is_published || auth()->check(), 404);
        abort_unless($post->hasAttachment(), 404);

        if ($post->attachment_drive_file_id) {
            $filename = $post->attachment_original_filename ?: 'documento';

            return response()->streamDownload(function () use ($googleDrive, $post) {
                $stream = $googleDrive->readStream($post->attachment_drive_file_id);
                fpassthru($stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }, $filename, [
                'Content-Type' => MimeHelper::fromFilename($filename),
            ]);
        }

        return redirect($post->attachment_file_path);
    }
}
