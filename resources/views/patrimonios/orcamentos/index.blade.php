@extends('patrimonios.layouts.app')

@section('title', 'Orçamentos')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div class="flex gap-2">
            <a href="{{ route('patrimonios.relatorios.orcamentos.csv') }}" class="rounded-lg border border-oassab-border px-4 py-2 text-sm font-semibold text-oassab-blue">CSV</a>
            <a href="{{ route('patrimonios.relatorios.orcamentos.pdf') }}" class="rounded-lg border border-oassab-border px-4 py-2 text-sm font-semibold text-oassab-blue">PDF</a>
        </div>
        @can('create', App\Models\Orcamento::class)
            <x-patrimonios.form-modal-trigger :url="route('patrimonios.orcamentos.create')" title="Novo Orçamento">
                Novo Orçamento
            </x-patrimonios.form-modal-trigger>
        @endcan
    </div>

    <div class="overflow-hidden rounded-xl border border-oassab-border bg-white shadow-sm">
        <table class="min-w-full divide-y divide-oassab-border text-sm">
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
                @foreach ($orcamentos as $orc)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $orc->nome_item }}</td>
                        <td class="px-4 py-3 capitalize">{{ $orc->prioridade }}</td>
                        <td class="px-4 py-3 capitalize">{{ str_replace('_', ' ', $orc->status) }}</td>
                        <td class="px-4 py-3">{{ $orc->propostas->count() }}</td>
                        <td class="px-4 py-3 text-right">
                            <x-patrimonios.form-modal-trigger :url="route('patrimonios.orcamentos.edit', $orc)" title="Gerenciar Orçamento" variant="link">
                                Gerenciar
                            </x-patrimonios.form-modal-trigger>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <x-pagination :paginator="$orcamentos" />
@endsection
