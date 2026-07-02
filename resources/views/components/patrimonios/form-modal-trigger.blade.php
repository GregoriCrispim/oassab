@props([
    'url',
    'title' => '',
    'variant' => 'button',
])

@if ($variant === 'link')
    <button
        type="button"
        {{ $attributes->merge(['class' => 'js-open-form-modal text-oassab-orange hover:underline']) }}
        data-url="{{ $url }}"
        @if ($title) data-title="{{ $title }}" @endif
    >
        {{ $slot }}
    </button>
@elseif ($variant === 'icon')
    <button
        type="button"
        title="{{ $title }}"
        aria-label="{{ $title }}"
        {{ $attributes->merge(['class' => 'js-open-form-modal btn-icon']) }}
        data-url="{{ $url }}"
        data-title="{{ $title }}"
    >
        {{ $slot }}
    </button>
@else
    <button
        type="button"
        {{ $attributes->merge(['class' => 'js-open-form-modal btn-orange']) }}
        data-url="{{ $url }}"
        @if ($title) data-title="{{ $title }}" @endif
    >
        {{ $slot }}
    </button>
@endif
