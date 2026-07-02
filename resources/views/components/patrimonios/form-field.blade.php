@props([
    'label',
    'name' => null,
    'hint' => null,
    'required' => false,
    'span' => null,
])

<div {{ $attributes->class([
    'form-field',
    'md:col-span-2' => $span === 'full',
]) }}>
    @if ($label)
        <label @if ($name) for="{{ $name }}" @endif class="form-label">
            {{ $label }}@if ($required)<span class="text-oassab-orange"> *</span>@endif
        </label>
    @endif

    {{ $slot }}

    @if ($hint)
        <p class="form-hint">{{ $hint }}</p>
    @endif
</div>
