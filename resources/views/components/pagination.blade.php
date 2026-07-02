@props(['paginator'])

@if ($paginator->total() > 0)
    <div {{ $attributes->merge(['class' => 'mt-6']) }}>
        {{ $paginator->links('vendor.pagination.bootstrap-oassab') }}
    </div>
@endif
