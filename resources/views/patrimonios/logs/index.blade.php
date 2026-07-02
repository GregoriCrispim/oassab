@extends('patrimonios.layouts.app')

@section('title', 'Logs de Auditoria')

@section('content')
    @php
        $hasActiveFilters = request()->filled('acao');
    @endphp

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <x-patrimonios.list-filters
            :action="route('patrimonios.logs.index')"
            :clear-url="route('patrimonios.logs.index')"
            :has-active-filters="$hasActiveFilters"
        >
            <x-patrimonios.list-filter-select name="acao" label="Ação">
                <option value="">Todas as ações</option>
                @foreach (['INSERT', 'UPDATE', 'DELETE', 'LOGIN', 'LOGOUT'] as $a)
                    <option value="{{ $a }}" @selected(request('acao') === $a)>{{ $a }}</option>
                @endforeach
            </x-patrimonios.list-filter-select>
        </x-patrimonios.list-filters>

        @if (auth()->user()->isPatrimonioAdmin())
            <form method="POST" action="{{ route('patrimonios.logs.clear') }}" onsubmit="return confirm('Limpar todos os logs?')">
                @csrf @method('DELETE')
                <button type="submit" class="rounded-lg border border-red-300 px-4 py-2 text-sm font-semibold text-red-600">Limpar Logs</button>
            </form>
        @endif
    </div>

    <div class="overflow-hidden rounded-xl border border-oassab-border bg-white shadow-sm">
        <table class="min-w-full divide-y divide-oassab-border text-sm">
            <thead class="bg-oassab-cream">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-oassab-blue">Data</th>
                    <th class="px-4 py-3 text-left font-semibold text-oassab-blue">Usuário</th>
                    <th class="px-4 py-3 text-left font-semibold text-oassab-blue">Ação</th>
                    <th class="px-4 py-3 text-left font-semibold text-oassab-blue">Descrição</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-oassab-border">
                @foreach ($logs as $log)
                    <tr>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $log->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">{{ $log->user?->name ?? '—' }}</td>
                        <td class="px-4 py-3"><span class="rounded bg-oassab-cream px-2 py-0.5 font-mono text-xs">{{ $log->acao }}</span></td>
                        <td class="px-4 py-3 text-oassab-gray">{{ $log->descricao }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <x-pagination :paginator="$logs" />
@endsection
