@props([
    'submit' => 'Salvar',
    'submitIcon' => 'check-lg',
])

<div {{ $attributes->merge(['class' => 'form-actions']) }}>
    <button type="submit" class="btn-orange-lg">
        @if ($submitIcon)
            <i class="bi bi-{{ $submitIcon }}"></i>
        @endif
        {{ $submit }}
    </button>
    <button type="button" class="btn-ghost" data-form-modal-close>
        Cancelar
    </button>
</div>
