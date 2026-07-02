@props([
    'icon',
    'title',
    'href' => null,
    'modalUrl' => null,
    'type' => 'button',
    'variant' => 'blue',
])

@php
    $variantClass = match ($variant) {
        'orange' => 'btn-icon--orange',
        'red' => 'btn-icon--red',
        default => 'btn-icon--blue',
    };
@endphp

@if ($modalUrl)
    <button
        type="button"
        title="{{ $title }}"
        aria-label="{{ $title }}"
        {{ $attributes->merge(['class' => "js-open-form-modal btn-icon {$variantClass}"]) }}
        data-url="{{ $modalUrl }}"
        data-title="{{ $title }}"
    >
        <i class="bi bi-{{ $icon }}"></i>
    </button>
@elseif ($href)
    <a
        href="{{ $href }}"
        title="{{ $title }}"
        aria-label="{{ $title }}"
        {{ $attributes->merge(['class' => "btn-icon {$variantClass}"]) }}
    >
        <i class="bi bi-{{ $icon }}"></i>
    </a>
@else
    <button
        type="{{ $type }}"
        title="{{ $title }}"
        aria-label="{{ $title }}"
        {{ $attributes->merge(['class' => "btn-icon {$variantClass}"]) }}
    >
        <i class="bi bi-{{ $icon }}"></i>
    </button>
@endif
