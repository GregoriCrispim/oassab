@props([
    'title',
    'icon' => 'heart',
])

@php
    $icons = [
        'heart' => '<path d="M12 21s-7.5-4.7-9.5-9.5C1.1 7.6 4.4 4 8.2 4c2 0 3.6 1.1 4.3 2.7C13.2 5.1 14.8 4 16.8 4c3.8 0 7.1 3.6 5.7 7.5C19.5 16.3 12 21 12 21z"/>',
        'hand' => '<path d="M7 11V6a2 2 0 1 1 4 0v5M11 11V4a2 2 0 1 1 4 0v7M15 11V6a2 2 0 1 1 4 0v8a8 8 0 0 1-8 8h-1c-3 0-5-1-7-4l-2-3a2 2 0 0 1 2.7-2.8L7 13V8a2 2 0 1 1 4 0"/>',
        'box' => '<path d="M3 7l9-4 9 4M3 7v10l9 4 9-4V7M3 7l9 4 9-4M12 11v10"/>',
        'users' => '<circle cx="9" cy="8" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M3 19c.5-3 3.4-5 6-5s5.5 2 6 5M14 19c.4-2 2.4-3.5 4-3.5s2.6 1 3 2.5"/>',
        'apple' => '<path d="M14 4c-1 0-2 .5-2.5 1.5C11 4.5 10 4 9 4c-3 0-5 2.5-5 6 0 4 3 9 6 9 1.2 0 1.8-.7 3-.7s1.8.7 3 .7c3 0 6-5 6-9 0-3.5-2-6-5-6-1 0-2 .5-2.5 1.5"/><path d="M13 3c0-1 .8-2 2-2"/>',
        'briefcase' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2M3 13h18"/>',
        'building' => '<rect x="4" y="3" width="16" height="18" rx="1"/><path d="M9 8h.01M9 12h.01M9 16h.01M14 8h.01M14 12h.01M14 16h.01"/>',
        'document' => '<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><path d="M14 3v6h6M9 14h6M9 18h6"/>',
        'star' => '<path d="M12 2l3.1 6.3L22 9.3l-5 4.9 1.2 6.8L12 17.8 5.8 21l1.2-6.8-5-4.9 6.9-1z"/>',
    ];
    $svg = $icons[$icon] ?? $icons['heart'];
@endphp

<article class="group relative h-full rounded-2xl border border-oassab-border bg-white p-7 shadow-sm transition hover:-translate-y-1 hover:border-oassab-orange/40 hover:shadow-xl">
    <div class="mb-5 flex h-14 w-14 items-center justify-center rounded-xl bg-oassab-blue/5 text-oassab-blue transition group-hover:bg-oassab-orange group-hover:text-white">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" class="h-7 w-7" aria-hidden="true">
            {!! $svg !!}
        </svg>
    </div>
    <h3 class="mb-3 font-heading text-lg font-semibold uppercase tracking-wide text-oassab-blue">
        {{ $title }}
    </h3>
    <div class="text-sm leading-relaxed text-oassab-gray">
        {{ $slot }}
    </div>
</article>
