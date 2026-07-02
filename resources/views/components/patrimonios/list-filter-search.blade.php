@props([
    'name' => 'q',
    'id' => null,
    'label' => 'Busca',
    'placeholder' => 'Buscar...',
    'value' => '',
])

@php
    $inputId = $id ?? $name;
    $clearId = $inputId . '-clear';
@endphp

<div class="list-filter-field list-filter-field--search">
    <label for="{{ $inputId }}" class="form-label">{{ $label }}</label>
    <div class="list-filter-search">
        <i class="bi bi-search list-filter-search__icon" aria-hidden="true"></i>
        <input
            type="text"
            name="{{ $name }}"
            id="{{ $inputId }}"
            value="{{ $value }}"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            {{ $attributes->class(['list-filter-search__input', 'form-input']) }}
        >
        <button
            type="button"
            id="{{ $clearId }}"
            class="list-filter-search__clear hidden"
            aria-label="Limpar busca"
            title="Limpar busca"
        >
            <i class="bi bi-x-lg text-sm"></i>
        </button>
    </div>
</div>
