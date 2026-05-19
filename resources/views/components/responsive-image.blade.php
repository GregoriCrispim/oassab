@props([
    'set' => null,
    'src' => null,
    'alt' => '',
    'sizes' => '(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw',
    'class' => '',
    'priority' => false,
    'fallback' => '/images/posts/placeholder.jpg',
])

@php
    $resolved = is_array($set) ? $set : null;

    $finalSrc = $resolved['src'] ?? $src ?? $fallback;
    $webp = $resolved['webp_srcset'] ?? null;
    $jpg = $resolved['jpg_srcset'] ?? null;
    $w = $resolved['width'] ?? null;
    $h = $resolved['height'] ?? null;

    $loading = $priority ? 'eager' : 'lazy';
    $fetchAttr = $priority ? 'high' : 'auto';
@endphp

@if ($webp || $jpg)
    <picture>
        @if ($webp)
            <source type="image/webp" srcset="{{ $webp }}" sizes="{{ $sizes }}">
        @endif
        @if ($jpg)
            <source type="image/jpeg" srcset="{{ $jpg }}" sizes="{{ $sizes }}">
        @endif
        <img src="{{ $finalSrc }}"
             alt="{{ $alt }}"
             @if ($w) width="{{ $w }}" @endif
             @if ($h) height="{{ $h }}" @endif
             loading="{{ $loading }}"
             decoding="async"
             fetchpriority="{{ $fetchAttr }}"
             onerror="this.onerror=null;this.src='{{ $fallback }}';"
             class="{{ $class }}">
    </picture>
@else
    <img src="{{ $finalSrc }}"
         alt="{{ $alt }}"
         @if ($w) width="{{ $w }}" @endif
         @if ($h) height="{{ $h }}" @endif
         loading="{{ $loading }}"
         decoding="async"
         fetchpriority="{{ $fetchAttr }}"
         onerror="this.onerror=null;this.src='{{ $fallback }}';"
         class="{{ $class }}">
@endif
