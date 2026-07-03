@extends('patrimonios.layouts.app')

@section('title', 'Logs de Auditoria')

@section('content')
    @php
        $hasActiveFilters = request()->filled('acao');
    @endphp

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
        <x-patrimonios.list-filters
            :action="route('patrimonios.logs.index')"
            :clear-url="route('patrimonios.logs.index')"
            :has-active-filters="$hasActiveFilters"
            class="w-full sm:w-auto"
        >
            <x-patrimonios.list-filter-select name="acao" label="Ação">
                <option value="">Todas as ações</option>
                @foreach (['INSERT', 'UPDATE', 'DELETE', 'LOGIN', 'LOGOUT'] as $a)
                    <option value="{{ $a }}" @selected(request('acao') === $a)>{{ $a }}</option>
                @endforeach
            </x-patrimonios.list-filter-select>
        </x-patrimonios.list-filters>

        @if (auth()->user()->isPatrimonioAdmin())
            <form method="POST" action="{{ route('patrimonios.logs.clear') }}" onsubmit="return confirm('Limpar todos os logs?')" class="w-full sm:w-auto">
                @csrf @method('DELETE')
                <button type="submit" class="w-full rounded-lg border border-red-300 px-4 py-2 text-sm font-semibold text-red-600 sm:w-auto">Limpar Logs</button>
            </form>
        @endif
    </div>

    <x-patrimonios.responsive-table>
        <table>
            <thead class="bg-oassab-cream">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-oassab-blue">Data</th>
                    <th class="px-4 py-3 text-left font-semibold text-oassab-blue">Usuário</th>
                    <th class="px-4 py-3 text-left font-semibold text-oassab-blue">Ação</th>
                    <th class="px-4 py-3 text-left font-semibold text-oassab-blue">Descrição</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-oassab-border">
                @forelse ($logs as $log)
                    <tr>
                        <td data-label="Data" class="px-4 py-3 whitespace-nowrap">{{ $log->created_at?->format('d/m/Y H:i') }}</td>
                        <td data-label="Usuário" class="px-4 py-3">{{ $log->user?->name ?? '—' }}</td>
                        <td data-label="Ação" class="px-4 py-3"><span class="rounded bg-oassab-cream px-2 py-0.5 font-mono text-xs">{{ $log->acao }}</span></td>
                        <td data-label="Descrição" class="px-4 py-3 text-oassab-gray">{{ $log->descricao }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" data-label="" class="patrimonio-table__empty px-4 py-8 text-center text-oassab-gray">Nenhum log encontrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-patrimonios.responsive-table>
    <x-pagination :paginator="$logs" />
@endsection
