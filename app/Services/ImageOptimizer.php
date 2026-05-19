<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

/**
 * Otimiza imagens enviadas pelo admin:
 *
 * - Reescala para no máximo MAX_WIDTH px (mantendo proporção).
 * - Gera variantes em três larguras (480, 800, 1200) em WebP e JPG.
 * - Salva tudo em storage/app/public/posts/{slug}/.
 *
 * Caso GD/Imagick não estejam instalados, faz fallback graceful:
 * armazena a imagem original e retorna metadados mínimos para que
 * o site continue funcionando (apenas sem variantes).
 */
class ImageOptimizer
{
    public const MAX_WIDTH = 1600;

    /** Larguras geradas (px). Devem estar em ordem crescente. */
    public const WIDTHS = [480, 800, 1200];

    public const JPEG_QUALITY = 80;

    public const WEBP_QUALITY = 78;

    private string $disk = 'public';

    /**
     * @return array{
     *     base: string,
     *     ext_default: string,
     *     widths: array<int>,
     *     formats: array<string>,
     *     width: int,
     *     height: int,
     *     ratio: float,
     *     fallback?: bool,
     * }
     */
    public function optimize(UploadedFile $file, string $slug): array
    {
        $folder = 'posts/'.$slug;

        Storage::disk($this->disk)->deleteDirectory($folder);

        if (! $this->hasImageDriver()) {
            return $this->fallbackStore($file, $slug, $folder);
        }

        try {
            $manager = $this->makeManager();
            $image = $manager->decodePath($file->getRealPath());

            // Corrige orientação EXIF (fotos de celular).
            $image = $image->orient();

            $originalWidth = (int) $image->width();
            $originalHeight = (int) $image->height();

            if ($originalWidth > self::MAX_WIDTH) {
                $image = $image->scaleDown(width: self::MAX_WIDTH);
            }

            $masterWidth = (int) $image->width();
            $masterHeight = (int) $image->height();

            $widthsToWrite = array_values(array_unique(array_filter(
                self::WIDTHS,
                fn ($w) => $w <= $masterWidth
            )));

            // Garante pelo menos a largura "master".
            if ($widthsToWrite === []) {
                $widthsToWrite = [$masterWidth];
            } elseif (! in_array($masterWidth, $widthsToWrite, true) && $masterWidth > end($widthsToWrite)) {
                $widthsToWrite[] = $masterWidth;
            }

            sort($widthsToWrite);

            foreach ($widthsToWrite as $w) {
                $variant = $w === $masterWidth
                    ? clone $image
                    : (clone $image)->scaleDown(width: $w);

                $jpg = $variant->encode(new JpegEncoder(quality: self::JPEG_QUALITY));
                $webp = $variant->encode(new WebpEncoder(quality: self::WEBP_QUALITY));

                Storage::disk($this->disk)->put(
                    $folder.'/'.$slug.'-'.$w.'.jpg',
                    (string) $jpg
                );
                Storage::disk($this->disk)->put(
                    $folder.'/'.$slug.'-'.$w.'.webp',
                    (string) $webp
                );
            }

            return [
                'base' => '/storage/'.$folder.'/'.$slug,
                'ext_default' => 'jpg',
                'widths' => $widthsToWrite,
                'formats' => ['webp', 'jpg'],
                'width' => $masterWidth,
                'height' => $masterHeight,
                'ratio' => $masterHeight > 0 ? round($masterWidth / $masterHeight, 4) : 1.0,
            ];
        } catch (\Throwable $e) {
            Log::warning('ImageOptimizer falhou, usando fallback: '.$e->getMessage());

            return $this->fallbackStore($file, $slug, $folder);
        }
    }

    /**
     * Mesmo formato de retorno de optimize(), mas trabalhando em cima de um
     * arquivo já existente em disco (usado pelo comando images:optimize).
     */
    public function optimizeFromPath(string $absolutePath, string $slug, string $folder = null): array
    {
        $folder = $folder ?? 'posts/'.$slug;

        Storage::disk($this->disk)->deleteDirectory($folder);

        if (! $this->hasImageDriver()) {
            $ext = pathinfo($absolutePath, PATHINFO_EXTENSION) ?: 'jpg';
            $path = $folder.'/'.$slug.'.'.$ext;
            Storage::disk($this->disk)->put($path, file_get_contents($absolutePath));

            return [
                'base' => '/storage/'.$folder.'/'.$slug,
                'ext_default' => $ext,
                'widths' => [],
                'formats' => [$ext],
                'width' => 0,
                'height' => 0,
                'ratio' => 1.0,
                'fallback' => true,
            ];
        }

        $manager = $this->makeManager();
        $image = $manager->decodePath($absolutePath)->orient();

        if ($image->width() > self::MAX_WIDTH) {
            $image = $image->scaleDown(width: self::MAX_WIDTH);
        }

        $masterWidth = (int) $image->width();
        $masterHeight = (int) $image->height();

        $widthsToWrite = array_values(array_unique(array_filter(
            self::WIDTHS,
            fn ($w) => $w <= $masterWidth
        )));

        if ($widthsToWrite === []) {
            $widthsToWrite = [$masterWidth];
        } elseif (! in_array($masterWidth, $widthsToWrite, true) && $masterWidth > end($widthsToWrite)) {
            $widthsToWrite[] = $masterWidth;
        }

        sort($widthsToWrite);

        foreach ($widthsToWrite as $w) {
            $variant = $w === $masterWidth
                ? clone $image
                : (clone $image)->scaleDown(width: $w);

            $jpg = $variant->encode(new JpegEncoder(quality: self::JPEG_QUALITY));
            $webp = $variant->encode(new WebpEncoder(quality: self::WEBP_QUALITY));

            Storage::disk($this->disk)->put(
                $folder.'/'.$slug.'-'.$w.'.jpg',
                (string) $jpg
            );
            Storage::disk($this->disk)->put(
                $folder.'/'.$slug.'-'.$w.'.webp',
                (string) $webp
            );
        }

        return [
            'base' => '/storage/'.$folder.'/'.$slug,
            'ext_default' => 'jpg',
            'widths' => $widthsToWrite,
            'formats' => ['webp', 'jpg'],
            'width' => $masterWidth,
            'height' => $masterHeight,
            'ratio' => $masterHeight > 0 ? round($masterWidth / $masterHeight, 4) : 1.0,
        ];
    }

    /**
     * Recompacta uma imagem estática que vive em public/images (hero, about etc).
     * Gera o mesmo arquivo de saída + variantes em WebP ao lado.
     *
     * @return array{webp: array<int,string>, jpg: array<int,string>, width:int, height:int}|null
     */
    public function optimizeStatic(string $absolutePath, array $widths = self::WIDTHS): ?array
    {
        if (! $this->hasImageDriver()) {
            return null;
        }

        $manager = $this->makeManager();
        $image = $manager->decodePath($absolutePath)->orient();

        if ($image->width() > self::MAX_WIDTH) {
            $image = $image->scaleDown(width: self::MAX_WIDTH);
        }

        $masterWidth = (int) $image->width();
        $masterHeight = (int) $image->height();
        $info = pathinfo($absolutePath);
        $dir = $info['dirname'];
        $name = $info['filename'];
        $ext = strtolower($info['extension'] ?? 'jpg');

        $widthsToWrite = array_values(array_unique(array_filter(
            $widths,
            fn ($w) => $w <= $masterWidth
        )));

        if ($widthsToWrite === [] || ! in_array($masterWidth, $widthsToWrite, true)) {
            $widthsToWrite[] = $masterWidth;
        }

        sort($widthsToWrite);

        $jpgPaths = [];
        $webpPaths = [];

        foreach ($widthsToWrite as $w) {
            $variant = $w === $masterWidth
                ? clone $image
                : (clone $image)->scaleDown(width: $w);

            $jpgFile = $dir.'/'.$name.'-'.$w.'.'.$ext;
            $webpFile = $dir.'/'.$name.'-'.$w.'.webp';

            file_put_contents($jpgFile, (string) $variant->encode(new JpegEncoder(quality: self::JPEG_QUALITY)));
            file_put_contents($webpFile, (string) $variant->encode(new WebpEncoder(quality: self::WEBP_QUALITY)));

            $jpgPaths[$w] = $jpgFile;
            $webpPaths[$w] = $webpFile;
        }

        // Sobrescreve o arquivo original com a versão "master" recompactada.
        file_put_contents(
            $absolutePath,
            (string) $image->encode(new JpegEncoder(quality: self::JPEG_QUALITY))
        );

        return [
            'webp' => $webpPaths,
            'jpg' => $jpgPaths,
            'width' => $masterWidth,
            'height' => $masterHeight,
        ];
    }

    public function hasImageDriver(): bool
    {
        return extension_loaded('gd') || extension_loaded('imagick');
    }

    private function makeManager(): ImageManager
    {
        if (extension_loaded('imagick')) {
            return new ImageManager(new ImagickDriver());
        }

        return new ImageManager(new GdDriver());
    }

    /**
     * Quando não há GD/Imagick, salva o arquivo original sem processar.
     * Mantém a aplicação funcionando, apenas sem responsive images.
     *
     * @return array{base:string, ext_default:string, widths:array<int>, formats:array<string>, width:int, height:int, ratio:float, fallback:bool}
     */
    private function fallbackStore(UploadedFile $file, string $slug, string $folder): array
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $name = $slug.'.'.$ext;

        $file->storeAs($folder, $name, $this->disk);

        $size = @getimagesize($file->getRealPath()) ?: [0, 0];

        return [
            'base' => '/storage/'.$folder.'/'.$slug,
            'ext_default' => $ext,
            'widths' => [],
            'formats' => [$ext],
            'width' => (int) ($size[0] ?? 0),
            'height' => (int) ($size[1] ?? 0),
            'ratio' => ($size[1] ?? 0) > 0 ? round($size[0] / $size[1], 4) : 1.0,
            'fallback' => true,
        ];
    }
}
