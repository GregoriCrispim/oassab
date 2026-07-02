@props([
    'action',
    'id' => null,
    'method' => 'GET',
    'live' => false,
    'clearUrl' => null,
    'hasActiveFilters' => false,
])

<form
    @if ($id) id="{{ $id }}" @endif
    method="{{ $method }}"
    action="{{ $action }}"
    {{ $attributes->class(['list-filters', 'list-filters--live' => $live]) }}
    @if ($live) data-patrimonio-live-filters @endif
>
    <div class="list-filters__fields">
        {{ $slot }}
    </div>

    @if ($clearUrl && $hasActiveFilters)
        <a href="{{ $clearUrl }}" class="list-filters__clear">
            <i class="bi bi-x-lg text-[10px]"></i>
            Limpar
        </a>
    @endif
</form>
