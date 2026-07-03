@extends('patrimonios.layouts.app')

@section('title', 'Patrimônios')
@section('subtitle', 'Gestão de bens patrimoniais')

@section('content')
    @php
        $hasActiveFilters = request()->filled('q')
            || request()->filled('categoria_id')
            || request()->filled('ativo');
    @endphp

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
        <x-patrimonios.list-filters
            id="patrimonios-filter-form"
            :action="route('patrimonios.patrimonios.index')"
            live
            :clear-url="route('patrimonios.patrimonios.index')"
            :has-active-filters="$hasActiveFilters"
            class="w-full sm:w-auto"
        >
            <x-patrimonios.list-filter-search
                id="patrimonio-busca"
                label="Busca"
                placeholder="Nome, código..."
                :value="request('q')"
            />

            <x-patrimonios.list-filter-select name="categoria_id" label="Categoria">
                <option value="">Todas</option>
                @foreach ($categorias as $cat)
                    <option value="{{ $cat->id }}" @selected(request('categoria_id') == $cat->id)>{{ $cat->nome }}</option>
                @endforeach
            </x-patrimonios.list-filter-select>

            <x-patrimonios.list-filter-select name="ativo" label="Status">
                <option value="">Todos</option>
                <option value="1" @selected(request('ativo') === '1')>Ativos</option>
                <option value="0" @selected(request('ativo') === '0')>Inativos</option>
            </x-patrimonios.list-filter-select>
        </x-patrimonios.list-filters>

        <div class="flex w-full flex-wrap gap-2 sm:w-auto sm:justify-end">
            <a href="{{ route('patrimonios.relatorios.patrimonios.csv') }}" class="rounded-lg border border-oassab-border px-4 py-2 text-sm font-semibold text-oassab-blue">CSV</a>
            <a href="{{ route('patrimonios.relatorios.patrimonios.pdf') }}" class="rounded-lg border border-oassab-border px-4 py-2 text-sm font-semibold text-oassab-blue">PDF</a>
            @can('create', App\Models\Patrimonio::class)
                <x-patrimonios.form-modal-trigger :url="route('patrimonios.patrimonios.create')" title="Novo Patrimônio">
                    Novo Patrimônio
                </x-patrimonios.form-modal-trigger>
            @endcan
        </div>
    </div>

    <div id="patrimonios-list" class="transition-opacity duration-150">
        @include('patrimonios.patrimonios._table')
    </div>
@endsection
