@props([
    'src',                // ex: /images/hero-bg.jpg
    'widths' => [],       // ex: [800, 1200, 1600]
    'alt' => '',
    'sizes' => '100vw',
    'class' => '',
    'priority' => false,
    'width' => null,
    'height' => null,
    'imgAttrs' => '',
])

@php
    $cacheKey = 'static-picture:'.$src.':'.implode(',', $widths);

    /**
     * Descobre quais variantes (jpg/webp em cada largura) existem fisicamente
     * em public/. Usa cache para não fazer file_exists() em toda request.
     * Cache é invalidado por PostObserver e pelo comando images:optimize.
     */
    $variants = \Illuminate\Support\Facades\Cache::remember($cacheKey, 86400, function () use ($src, $widths) {
        $info = pathinfo($src);
        $dir = $info['dirname'] === '.' ? '' : $info['dirname'];
        $name = $info['filename'];
        $ext = strtolower($info['extension'] ?? 'jpg');

        $jpg = [];
        $webp = [];

        foreach ($widths as $w) {
            $jpgRel = $dir.'/'.$name.'-'.$w.'.'.$ext;
            $webpRel = $dir.'/'.$name.'-'.$w.'.webp';

            if (is_file(public_path(ltrim($jpgRel, '/')))) {
                $jpg[$w] = $jpgRel;
            }
            if (is_file(public_path(ltrim($webpRel, '/')))) {
                $webp[$w] = $webpRel;
            }
        }

        return ['jpg' => $jpg, 'webp' => $webp];
    });

    $jpgSrcset = collect($variants['jpg'] ?? [])
        ->map(fn ($p, $w) => $p.' '.$w.'w')
        ->values()
        ->implode(', ');

    $webpSrcset = collect($variants['webp'] ?? [])
        ->map(fn ($p, $w) => $p.' '.$w.'w')
        ->values()
        ->implode(', ');

    $loading = $priority ? 'eager' : 'lazy';
    $fetchAttr = $priority ? 'high' : 'auto';
@endphp

@if ($webpSrcset !== '' || $jpgSrcset !== '')
    <picture>
        @if ($webpSrcset !== '')
            <source type="image/webp" srcset="{{ $webpSrcset }}" sizes="{{ $sizes }}">
        @endif
        @if ($jpgSrcset !== '')
            <source type="image/jpeg" srcset="{{ $jpgSrcset }}" sizes="{{ $sizes }}">
        @endif
        <img src="{{ $src }}"
             alt="{{ $alt }}"
             @if ($width) width="{{ $width }}" @endif
             @if ($height) height="{{ $height }}" @endif
             loading="{{ $loading }}"
             decoding="async"
             fetchpriority="{{ $fetchAttr }}"
             class="{{ $class }}"
             {!! $imgAttrs !!}>
    </picture>
@else
    <img src="{{ $src }}"
         alt="{{ $alt }}"
         @if ($width) width="{{ $width }}" @endif
         @if ($height) height="{{ $height }}" @endif
         loading="{{ $loading }}"
         decoding="async"
         fetchpriority="{{ $fetchAttr }}"
         class="{{ $class }}"
         {!! $imgAttrs !!}>
@endif
