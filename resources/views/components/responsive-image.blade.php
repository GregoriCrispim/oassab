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
    $loading = $priority ? 'eager' : 'lazy';
    $fetchAttr = $priority ? 'high' : 'auto';
@endphp

<img src="{{ $finalSrc }}"
     alt="{{ $alt }}"
     loading="{{ $loading }}"
     decoding="async"
     fetchpriority="{{ $fetchAttr }}"
     class="{{ $class }}">
