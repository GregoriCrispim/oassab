@extends('patrimonios.layouts.app')

@section('title', 'Manutenções')

@section('content')
    @php
        $hasActiveFilters = request()->filled('status');
    @endphp

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <x-patrimonios.list-filters
            :action="route('patrimonios.manutencoes.index')"
            :clear-url="route('patrimonios.manutencoes.index')"
            :has-active-filters="$hasActiveFilters"
        >
            <x-patrimonios.list-filter-select name="status" label="Status">
                <option value="">Todos os status</option>
                @foreach (['agendada', 'em_andamento', 'concluida', 'cancelada'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                @endforeach
            </x-patrimonios.list-filter-select>
        </x-patrimonios.list-filters>

        @can('create', App\Models\Manutencao::class)
            <x-patrimonios.form-modal-trigger :url="route('patrimonios.manutencoes.create')" title="Nova Manutenção">
                Nova Manutenção
            </x-patrimonios.form-modal-trigger>
        @endcan
    </div>

    <div class="overflow-hidden rounded-xl border border-oassab-border bg-white shadow-sm">
        <table class="min-w-full divide-y divide-oassab-border text-sm">
            <thead class="bg-oassab-cream">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-oassab-blue">Patrimônio</th>
                    <th class="px-4 py-3 text-left font-semibold text-oassab-blue">Tipo</th>
                    <th class="px-4 py-3 text-left font-semibold text-oassab-blue">Data</th>
                    <th class="px-4 py-3 text-left font-semibold text-oassab-blue">Status</th>
                    <th class="px-4 py-3 text-left font-semibold text-oassab-blue">Custo</th>
                    <th class="px-4 py-3 text-right font-semibold text-oassab-blue">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-oassab-border">
                @foreach ($manutencoes as $man)
                    <tr>
                        <td class="px-4 py-3">{{ $man->patrimonio?->nome ?? '—' }}</td>
                        <td class="px-4 py-3 capitalize">{{ $man->tipo }}</td>
                        <td class="px-4 py-3">{{ $man->data_manutencao->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 capitalize">{{ str_replace('_', ' ', $man->status) }}</td>
                        <td class="px-4 py-3">R$ {{ number_format($man->custo, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">
                            @can('update', $man)
                                <x-patrimonios.form-modal-trigger :url="route('patrimonios.manutencoes.edit', $man)" title="Editar Manutenção" variant="link">
                                    Editar
                                </x-patrimonios.form-modal-trigger>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <x-pagination :paginator="$manutencoes" />
@endsection
