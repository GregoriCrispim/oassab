<?php

namespace App\Support;

class MimeHelper
{
    public static function fromFilename(string $filename): string
    {
        return self::fromExtension(pathinfo($filename, PATHINFO_EXTENSION));
    }

    public static function fromExtension(string $extension): string
    {
        return match (strtolower($extension)) {
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            default => 'application/octet-stream',
        };
    }
}
