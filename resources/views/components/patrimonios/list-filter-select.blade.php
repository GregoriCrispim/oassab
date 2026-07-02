@props([
    'name',
    'label',
])

<div class="list-filter-field">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    <div class="list-filter-select">
        <select
            name="{{ $name }}"
            id="{{ $name }}"
            {{ $attributes->class(['list-filter-select__input', 'form-input']) }}
            data-auto-submit
        >
            {{ $slot }}
        </select>
        <i class="bi bi-chevron-down list-filter-select__icon" aria-hidden="true"></i>
    </div>
</div>
