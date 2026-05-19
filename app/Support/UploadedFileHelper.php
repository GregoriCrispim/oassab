<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class UploadedFileHelper
{
    public static function valid(Request $request, string $key): bool
    {
        $file = $request->file($key);

        return $file instanceof UploadedFile && $file->isValid();
    }
}
