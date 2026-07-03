@extends('patrimonios.layouts.app')

@section('title', 'Manutenções')

@section('content')
    @php
        $hasActiveFilters = request()->filled('status');
    @endphp

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
        <x-patrimonios.list-filters
            :action="route('patrimonios.manutencoes.index')"
            :clear-url="route('patrimonios.manutencoes.index')"
            :has-active-filters="$hasActiveFilters"
            class="w-full sm:w-auto"
        >
            <x-patrimonios.list-filter-select name="status" label="Status">
                <option value="">Todos os status</option>
                @foreach (['agendada', 'em_andamento', 'concluida', 'cancelada'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                @endforeach
            </x-patrimonios.list-filter-select>
        </x-patrimonios.list-filters>

        @can('create', App\Models\Manutencao::class)
            <x-patrimonios.form-modal-trigger :url="route('patrimonios.manutencoes.create')" title="Nova Manutenção" class="w-full sm:w-auto">
                Nova Manutenção
            </x-patrimonios.form-modal-trigger>
        @endcan
    </div>

    <x-patrimonios.responsive-table>
        <table>
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
                @forelse ($manutencoes as $man)
                    <tr>
                        <td data-label="Patrimônio" class="px-4 py-3">{{ $man->patrimonio?->nome ?? '—' }}</td>
                        <td data-label="Tipo" class="px-4 py-3 capitalize">{{ $man->tipo }}</td>
                        <td data-label="Data" class="px-4 py-3">{{ $man->data_manutencao->format('d/m/Y') }}</td>
                        <td data-label="Status" class="px-4 py-3 capitalize">{{ str_replace('_', ' ', $man->status) }}</td>
                        <td data-label="Custo" class="px-4 py-3">R$ {{ number_format($man->custo ?? 0, 2, ',', '.') }}</td>
                        <td data-label="Ações" class="patrimonio-table__actions px-4 py-3 text-right">
                            @can('update', $man)
                                <x-patrimonios.form-modal-trigger :url="route('patrimonios.manutencoes.edit', $man)" title="Editar Manutenção" variant="link">
                                    Editar
                                </x-patrimonios.form-modal-trigger>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" data-label="" class="patrimonio-table__empty px-4 py-8 text-center text-oassab-gray">Nenhuma manutenção encontrada.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-patrimonios.responsive-table>
    <x-pagination :paginator="$manutencoes" />
@endsection
