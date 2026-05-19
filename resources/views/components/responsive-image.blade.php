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
    use App\Services\PostImageStorage;

    $resolved = is_array($set) ? $set : null;

    $finalSrc = $resolved['src'] ?? $src ?? $fallback;
    $webp = $resolved['webp_srcset'] ?? null;
    $jpg = $resolved['jpg_srcset'] ?? null;
    $w = $resolved['width'] ?? null;
    $h = $resolved['height'] ?? null;

    if (str_starts_with($finalSrc, '/storage/') && ! PostImageStorage::existsPublicUrl($finalSrc)) {
        $slug = null;
        if (preg_match('#/storage/posts/([^/]+)/#', $finalSrc, $m)) {
            $slug = $m[1];
        }
        $finalSrc = $slug
            ? (PostImageStorage::resolveDisplayUrl($slug, null, null) ?? $fallback)
            : $fallback;
        $webp = null;
        $jpg = null;
    }

    $loading = $priority ? 'eager' : 'lazy';
    $fetchAttr = $priority ? 'high' : 'auto';
@endphp

<img src="{{ $finalSrc }}"
     alt="{{ $alt }}"
     @if ($w) width="{{ $w }}" @endif
     @if ($h) height="{{ $h }}" @endif
     loading="{{ $loading }}"
     decoding="async"
     fetchpriority="{{ $fetchAttr }}"
     onerror="this.onerror=null;this.src='{{ $fallback }}';"
     class="{{ $class }}">
