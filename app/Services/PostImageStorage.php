<?php

namespace App\Services;

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

    public static function existsPublicUrl(?string $url): bool
    {
        if (! $url || ! Str::startsWith($url, '/storage/')) {
            return false;
        }

        return self::disk()->exists(Str::after($url, '/storage/'));
    }

    /**
     * Após o upload, garante que image + meta apontam apenas para arquivos que existem no disco.
     *
     * @param  array<string, mixed>  $meta
     * @return array{0: string, 1: array<string, mixed>}
     */
    public static function normalizeAfterUpload(string $slug, array $meta): array
    {
        $disk = self::disk();
        $folder = self::folder($slug);
        $defaultExt = $meta['ext_default'] ?? 'jpg';

        $widths = self::scanVariantWidths($folder, $slug);

        if ($widths !== []) {
            sort($widths);
            $meta['base'] = '/storage/'.$folder.'/'.$slug;
            $meta['widths'] = $widths;
            $meta['ext_default'] = $defaultExt;
            $meta['formats'] = ['webp', 'jpg'];
            unset($meta['fallback']);

            $defaultW = $widths[(int) floor(count($widths) / 2)] ?? end($widths);
            $url = self::resolveVariantUrl($folder, $slug, $defaultW, $defaultExt);

            if ($url) {
                return [$url, $meta];
            }
        }

        foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
            $relative = $folder.'/'.$slug.'.'.$ext;
            if ($disk->exists($relative)) {
                $meta['base'] = '/storage/'.$folder.'/'.$slug;
                $meta['widths'] = [];
                $meta['ext_default'] = $ext;
                $meta['formats'] = [$ext];
                $meta['fallback'] = true;

                return [self::publicUrl($relative), $meta];
            }
        }

        throw new \RuntimeException('A imagem não foi gravada em storage. Verifique permissões em storage/app/public.');
    }

    /**
     * URL pública da capa, mesmo que o caminho salvo no banco esteja desatualizado.
     */
    public static function resolveDisplayUrl(string $slug, ?string $storedImage, ?array $meta): ?string
    {
        if ($storedImage && self::existsPublicUrl($storedImage)) {
            return $storedImage;
        }

        $folder = self::folder($slug);
        $meta = is_array($meta) ? $meta : [];
        $defaultExt = $meta['ext_default'] ?? 'jpg';
        $widths = ! empty($meta['widths']) ? $meta['widths'] : self::scanVariantWidths($folder, $slug);

        if ($widths !== []) {
            $defaultW = $widths[(int) floor(count($widths) / 2)] ?? end($widths);
            $url = self::resolveVariantUrl($folder, $slug, $defaultW, $defaultExt);

            if ($url) {
                return $url;
            }

            foreach ($widths as $w) {
                foreach (['jpg', 'jpeg', 'webp', 'png'] as $ext) {
                    $url = self::resolveVariantUrl($folder, $slug, $w, $ext);
                    if ($url) {
                        return $url;
                    }
                }
            }
        }

        foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
            $relative = $folder.'/'.$slug.'.'.$ext;
            if (self::disk()->exists($relative)) {
                return self::publicUrl($relative);
            }
        }

        if (self::disk()->exists($folder)) {
            foreach (self::disk()->files($folder) as $file) {
                if (preg_match('/\.(jpe?g|png|webp)$/i', $file)) {
                    return self::publicUrl($file);
                }
            }
        }

        return null;
    }

    /**
     * @return array{src:string, webp_srcset:?string, jpg_srcset:?string, width:?int, height:?int, base:?string}
     */
    public static function imageSet(string $slug, ?string $storedImage, ?array $meta): array
    {
        $src = self::resolveDisplayUrl($slug, $storedImage, $meta);

        if (! $src) {
            return [
                'src' => '/images/posts/placeholder.jpg',
                'webp_srcset' => null,
                'jpg_srcset' => null,
                'width' => null,
                'height' => null,
                'base' => null,
            ];
        }

        $meta = is_array($meta) ? $meta : [];
        $folder = self::folder($slug);
        $defaultExt = $meta['ext_default'] ?? 'jpg';
        $widths = ! empty($meta['widths']) ? $meta['widths'] : self::scanVariantWidths($folder, $slug);

        if ($widths === []) {
            return [
                'src' => $src,
                'webp_srcset' => null,
                'jpg_srcset' => null,
                'width' => isset($meta['width']) ? (int) $meta['width'] : null,
                'height' => isset($meta['height']) ? (int) $meta['height'] : null,
                'base' => $meta['base'] ?? null,
            ];
        }

        $base = $meta['base'] ?? '/storage/'.$folder.'/'.$slug;
        $existingWidths = collect($widths)->filter(function ($w) use ($folder, $slug, $defaultExt) {
            return self::disk()->exists($folder.'/'.$slug.'-'.$w.'.'.$defaultExt)
                || self::disk()->exists($folder.'/'.$slug.'-'.$w.'.jpg');
        })->values()->all();

        if ($existingWidths === []) {
            return [
                'src' => $src,
                'webp_srcset' => null,
                'jpg_srcset' => null,
                'width' => isset($meta['width']) ? (int) $meta['width'] : null,
                'height' => isset($meta['height']) ? (int) $meta['height'] : null,
                'base' => $base,
            ];
        }

        $jpg = collect($existingWidths)
            ->map(function ($w) use ($base, $slug, $folder, $defaultExt) {
                $ext = self::disk()->exists(self::folder($slug).'/'.$slug.'-'.$w.'.'.$defaultExt)
                    ? $defaultExt
                    : 'jpg';

                return "{$base}-{$w}.{$ext} {$w}w";
            })
            ->implode(', ');

        $webp = collect($existingWidths)
            ->filter(fn ($w) => self::disk()->exists($folder.'/'.$slug.'-'.$w.'.webp'))
            ->map(fn ($w) => "{$base}-{$w}.webp {$w}w")
            ->implode(', ');

        return [
            'src' => $src,
            'webp_srcset' => $webp !== '' ? $webp : null,
            'jpg_srcset' => $jpg !== '' ? $jpg : null,
            'width' => isset($meta['width']) ? (int) $meta['width'] : null,
            'height' => isset($meta['height']) ? (int) $meta['height'] : null,
            'base' => $base,
        ];
    }

    /**
     * Reconstrói image_meta a partir dos arquivos existentes no disco.
     *
     * @return array<string, mixed>|null
     */
    public static function metaFromDisk(string $slug): ?array
    {
        $folder = self::folder($slug);
        $widths = self::scanVariantWidths($folder, $slug);

        if ($widths !== []) {
            sort($widths);

            return [
                'base' => '/storage/'.$folder.'/'.$slug,
                'ext_default' => 'jpg',
                'widths' => $widths,
                'formats' => ['webp', 'jpg'],
            ];
        }

        foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
            if (self::disk()->exists($folder.'/'.$slug.'.'.$ext)) {
                return [
                    'base' => '/storage/'.$folder.'/'.$slug,
                    'ext_default' => $ext,
                    'widths' => [],
                    'formats' => [$ext],
                    'fallback' => true,
                ];
            }
        }

        return null;
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
        if (! self::disk()->exists($folder)) {
            return [];
        }

        $widths = [];
        foreach (self::disk()->files($folder) as $file) {
            if (preg_match('/^'.preg_quote($slug, '/').'-(\d+)\.(jpe?g|webp|png)$/i', basename($file), $m)) {
                $widths[] = (int) $m[1];
            }
        }

        return array_values(array_unique($widths));
    }

    private static function resolveVariantUrl(string $folder, string $slug, int $width, string $ext): ?string
    {
        foreach ([$ext, 'jpg', 'jpeg', 'webp', 'png'] as $tryExt) {
            $relative = $folder.'/'.$slug.'-'.$width.'.'.$tryExt;
            if (self::disk()->exists($relative)) {
                return self::publicUrl($relative);
            }
        }

        return null;
    }
}
