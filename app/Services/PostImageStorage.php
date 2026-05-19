<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostImageStorage
{
    public static function disk(): \Illuminate\Contracts\Filesystem\Filesystem
    {
        return Storage::disk('public');
    }

    public static function folder(string $slug): string
    {
        return 'posts/'.$slug;
    }

    public static function publicUrl(string $relativePath): string
    {
        return '/storage/'.ltrim($relativePath, '/');
    }

    /**
     * Verifica se a URL é servível pelo browser (public/storage).
     */
    public static function existsPublicUrl(?string $url): bool
    {
        $relative = self::relativeFromUrl($url);

        return $relative ? self::existsWebPath($relative) : false;
    }

    public static function relativeFromUrl(?string $url): ?string
    {
        if (! $url || ! Str::startsWith($url, '/storage/')) {
            return null;
        }

        return Str::after($url, '/storage/');
    }

    public static function existsWebPath(string $relative): bool
    {
        $publicFile = public_path('storage/'.ltrim($relative, '/'));

        if (is_file($publicFile)) {
            return true;
        }

        return self::disk()->exists($relative);
    }

    /**
     * @return list<string> caminhos relativos (ex.: posts/slug/arquivo.jpg)
     */
    public static function listWebFiles(string $folder): array
    {
        $folder = trim($folder, '/');
        $publicDir = public_path('storage/'.$folder);

        if (is_dir($publicDir)) {
            $files = [];
            foreach (scandir($publicDir) ?: [] as $name) {
                if ($name === '.' || $name === '..') {
                    continue;
                }
                $path = $publicDir.DIRECTORY_SEPARATOR.$name;
                if (is_file($path)) {
                    $files[] = $folder.'/'.$name;
                }
            }

            if ($files !== []) {
                return $files;
            }
        }

        if (! self::disk()->exists($folder)) {
            return [];
        }

        return self::disk()->files($folder);
    }

    /**
     * Grava no BD o caminho real do arquivo principal (fonte da verdade).
     *
     * @return array{0: ?string, 1: ?array<string, mixed>} [imageUrl, imageMeta]
     */
    public static function recordFromDisk(string $slug): array
    {
        $primary = self::pickPrimaryRelativePath($slug);

        if (! $primary) {
            return [null, null];
        }

        $folder = self::folder($slug);
        $filename = basename($primary);
        $url = self::publicUrl($primary);
        $widths = self::scanVariantWidths($folder, $slug);

        $meta = [
            'filename' => $filename,
            'path' => $primary,
        ];

        if ($widths !== []) {
            sort($widths);
            $meta['base'] = '/storage/'.$folder.'/'.$slug;
            $meta['widths'] = $widths;
            $meta['ext_default'] = pathinfo($filename, PATHINFO_EXTENSION) ?: 'jpg';
            $meta['formats'] = ['webp', 'jpg'];
        } else {
            $meta['ext_default'] = pathinfo($filename, PATHINFO_EXTENSION) ?: 'jpg';
            $meta['widths'] = [];
            $meta['fallback'] = true;
        }

        return [$url, $meta];
    }

    /**
     * Escolhe o arquivo principal acessível via /storage/...
     */
    public static function pickPrimaryRelativePath(string $slug): ?string
    {
        $folder = self::folder($slug);

        foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
            $relative = $folder.'/'.$slug.'.'.$ext;
            if (self::existsWebPath($relative)) {
                return $relative;
            }
        }

        $widths = self::scanVariantWidths($folder, $slug);
        if ($widths !== []) {
            sort($widths);
            $preferred = $widths[(int) floor(count($widths) / 2)] ?? end($widths);

            foreach (['jpg', 'jpeg', 'webp', 'png'] as $ext) {
                $relative = $folder.'/'.$slug.'-'.$preferred.'.'.$ext;
                if (self::existsWebPath($relative)) {
                    return $relative;
                }
            }
        }

        foreach (self::listWebFiles($folder) as $file) {
            if (preg_match('/\.(jpe?g|png|webp)$/i', $file)) {
                return $file;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array{0: string, 1: array<string, mixed>}
     */
    public static function normalizeAfterUpload(string $slug, array $meta): array
    {
        $record = self::recordFromDisk($slug);

        if ($record[0] === null) {
            throw new \RuntimeException('A imagem não foi gravada em storage. Verifique permissões em storage/app/public.');
        }

        return $record;
    }

    public static function resolveDisplayUrl(string $slug, ?string $storedImage, ?array $meta): ?string
    {
        if ($storedImage && self::existsPublicUrl($storedImage)) {
            return $storedImage;
        }

        $record = self::recordFromDisk($slug);

        return $record[0];
    }

    /**
     * @return array{src:string, webp_srcset:?string, jpg_srcset:?string, width:?int, height:?int, base:?string}
     */
    public static function imageSetForPost(?string $storedImage, ?array $meta): array
    {
        $src = ($storedImage && self::existsPublicUrl($storedImage))
            ? $storedImage
            : '/images/posts/placeholder.jpg';

        return [
            'src' => $src,
            'webp_srcset' => null,
            'jpg_srcset' => null,
            'width' => isset($meta['width']) ? (int) $meta['width'] : null,
            'height' => isset($meta['height']) ? (int) $meta['height'] : null,
            'base' => $meta['base'] ?? null,
        ];
    }

    /**
     * Move arquivos legados da raiz de storage/app/public para posts/{slug}/.
     */
    public static function relocateLegacyFiles(string $slug): bool
    {
        $disk = self::disk();
        $target = self::folder($slug);

        if ($disk->exists($target) && $disk->files($target) !== []) {
            return false;
        }

        $moved = false;

        foreach ($disk->files('') as $file) {
            $name = basename($file);
            if (! preg_match('/^'.preg_quote($slug, '/').'(-\d+)?\.(jpe?g|webp|png)$/i', $name)) {
                continue;
            }

            if (! $moved && ! $disk->exists($target)) {
                $disk->makeDirectory($target);
            }

            $disk->move($file, $target.'/'.$name);
            $moved = true;
        }

        return $moved;
    }

    /**
     * @param  array{0: ?string, 1: ?array<string, mixed>}|null  $record
     */
    public static function applyRecord(Post $post, ?array $record = null): void
    {
        $record ??= self::recordFromDisk($post->slug);
        [$url, $meta] = $record;

        $post->image = $url;
        $post->image_meta = $meta;
        $post->image_filename = is_array($meta) ? ($meta['filename'] ?? null) : null;
    }

    public static function renameFolder(string $oldSlug, string $newSlug): void
    {
        $oldFolder = self::folder($oldSlug);
        $newFolder = self::folder($newSlug);

        if ($oldSlug === $newSlug || ! self::disk()->exists($oldFolder)) {
            return;
        }

        if (self::disk()->exists($newFolder)) {
            self::disk()->deleteDirectory($newFolder);
        }

        self::disk()->move($oldFolder, $newFolder);
    }

    /**
     * @return array<int, int>
     */
    private static function scanVariantWidths(string $folder, string $slug): array
    {
        $widths = [];

        foreach (self::listWebFiles($folder) as $file) {
            if (preg_match('/^'.preg_quote($slug, '/').'-(\d+)\.(jpe?g|webp|png)$/i', basename($file), $m)) {
                $widths[] = (int) $m[1];
            }
        }

        return array_values(array_unique($widths));
    }
}
