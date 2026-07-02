@props([
    'title',
    'icon' => null,
    'subtitle' => null,
])

<section {{ $attributes->merge(['class' => 'form-section']) }}>
    <header class="form-section__header">
        @if ($icon)
            <div class="form-section__icon" aria-hidden="true">
                <i class="bi bi-{{ $icon }}"></i>
            </div>
        @endif
        <div class="min-w-0 flex-1">
            <h3 class="form-section__title">{{ $title }}</h3>
            @if ($subtitle)
                <p class="form-section__subtitle">{{ $subtitle }}</p>
            @endif
        </div>
        @isset($actions)
            <div class="shrink-0">{{ $actions }}</div>
        @endisset
    </header>

    {{ $slot }}
</section>
