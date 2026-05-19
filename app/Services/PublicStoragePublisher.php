<?php

namespace App\Services;

/**
 * Mantém public/storage/ idêntico a storage/app/public/ (local e produção).
 * Substitui o symlink do storage:link — mesma referência em todo ambiente.
 */
class PublicStoragePublisher
{
    public static function publish(): void
    {
        $source = storage_path('app/public');
        $target = public_path('storage');

        if (! is_dir($source)) {
            mkdir($source, 0755, true);
        }

        self::removeTarget($target);
        mkdir($target, 0755, true);
        self::copyDirectory($source, $target);

        ContentCache::flushAll();
    }

    private static function removeTarget(string $target): void
    {
        if (is_link($target)) {
            unlink($target);

            return;
        }

        if (! is_dir($target)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($target, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }

        rmdir($target);
    }

    private static function copyDirectory(string $source, string $target): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $dest = $target.DIRECTORY_SEPARATOR.$iterator->getSubPathName();

            if ($item->isDir()) {
                if (! is_dir($dest)) {
                    mkdir($dest, 0755, true);
                }
            } else {
                copy($item->getPathname(), $dest);
            }
        }
    }
}
