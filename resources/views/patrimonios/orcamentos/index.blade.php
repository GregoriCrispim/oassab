@extends('patrimonios.layouts.app')

@section('title', 'Orçamentos')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('patrimonios.relatorios.orcamentos.csv') }}" class="rounded-lg border border-oassab-border px-4 py-2 text-sm font-semibold text-oassab-blue">CSV</a>
            <a href="{{ route('patrimonios.relatorios.orcamentos.pdf') }}" class="rounded-lg border border-oassab-border px-4 py-2 text-sm font-semibold text-oassab-blue">PDF</a>
        </div>
        @can('create', App\Models\Orcamento::class)
            <x-patrimonios.form-modal-trigger :url="route('patrimonios.orcamentos.create')" title="Novo Orçamento" class="w-full sm:w-auto">
                Novo Orçamento
            </x-patrimonios.form-modal-trigger>
        @endcan
    </div>

    <x-patrimonios.responsive-table>
        <table>
            <thead class="bg-oassab-cream">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-oassab-blue">Item</th>
                    <th class="px-4 py-3 text-left font-semibold text-oassab-blue">Prioridade</th>
                    <th class="px-4 py-3 text-left font-semibold text-oassab-blue">Status</th>
                    <th class="px-4 py-3 text-left font-semibold text-oassab-blue">Propostas</th>
                    <th class="px-4 py-3 text-right font-semibold text-oassab-blue">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-oassab-border">
                @forelse ($orcamentos as $orc)
                    <tr>
                        <td data-label="Item" class="px-4 py-3 font-medium">{{ $orc->nome_item }}</td>
                        <td data-label="Prioridade" class="px-4 py-3 capitalize">{{ $orc->prioridade }}</td>
                        <td data-label="Status" class="px-4 py-3 capitalize">{{ str_replace('_', ' ', $orc->status) }}</td>
                        <td data-label="Propostas" class="px-4 py-3">{{ $orc->propostas->count() }}</td>
                        <td data-label="Ações" class="patrimonio-table__actions px-4 py-3 text-right">
                            @can('update', $orc)
                                <x-patrimonios.form-modal-trigger :url="route('patrimonios.orcamentos.edit', $orc)" title="Gerenciar Orçamento" variant="link">
                                    Gerenciar
                                </x-patrimonios.form-modal-trigger>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" data-label="" class="patrimonio-table__empty px-4 py-8 text-center text-oassab-gray">Nenhum orçamento encontrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-patrimonios.responsive-table>
    <x-pagination :paginator="$orcamentos" />
@endsection
