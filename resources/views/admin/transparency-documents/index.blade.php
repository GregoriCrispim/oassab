@extends('admin.layouts.admin')

@section('title', 'Portal Transparência')
@section('subtitle', 'Documentos PDF — termos de fomento e parcerias')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <form method="GET" action="{{ route('admin.transparency-documents.index') }}" class="flex flex-wrap items-end gap-3">
            <label class="block">
                <span class="text-xs font-semibold uppercase tracking-wider text-oassab-blue">Buscar</span>
                <input type="search" name="search" value="{{ $currentSearch }}" placeholder="Nome, slug ou processo"
                       class="mt-1 rounded-lg border border-oassab-border bg-white px-3 py-2 text-sm text-oassab-blue focus:border-oassab-orange focus:outline-none">
            </label>
            <button type="submit" class="btn-ghost">Filtrar</button>
            @if ($currentSearch)
                <a href="{{ route('admin.transparency-documents.index') }}" class="text-xs font-semibold uppercase tracking-wider text-oassab-gray hover:text-oassab-orange">Limpar</a>
            @endif
        </form>

        <a href="{{ route('admin.transparency-documents.create') }}" class="btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
            Novo documento
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-oassab-border bg-white shadow-sm">
        <table class="min-w-full divide-y divide-oassab-border text-sm">
            <thead class="bg-oassab-cream text-xs uppercase tracking-wider text-oassab-gray">
                <tr>
                    <th class="px-4 py-3 text-left">Nome</th>
                    <th class="px-4 py-3 text-left">Processo</th>
                    <th class="px-4 py-3 text-left">Ano</th>
                    <th class="px-4 py-3 text-left">Ordem</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-oassab-border bg-white">
                @forelse ($documents as $doc)
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-medium text-oassab-blue">{{ $doc->title }}</p>
                            @if ($doc->valor_global)
                                <p class="text-xs text-oassab-gray">{{ $doc->valor_global }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-oassab-gray">{{ $doc->processo ?: '—' }}</td>
                        <td class="px-4 py-3 text-oassab-gray">{{ $doc->year ?: '—' }}</td>
                        <td class="px-4 py-3 text-oassab-gray">{{ $doc->sort_order }}</td>
                        <td class="px-4 py-3">
                            @if ($doc->is_published)
                                <span class="rounded-full bg-green-100 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wider text-green-800">Publicado</span>
                            @else
                                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wider text-amber-800">Rascunho</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-3">
                                @if ($doc->file_path)
                                    <a href="{{ $doc->file_path }}" target="_blank" rel="noopener"
                                       class="text-xs font-semibold uppercase tracking-wider text-oassab-gray hover:text-oassab-orange">PDF</a>
                                @endif
                                <a href="{{ route('admin.transparency-documents.edit', $doc) }}"
                                   class="text-xs font-semibold uppercase tracking-wider text-oassab-orange hover:underline">Editar</a>
                                <form method="POST" action="{{ route('admin.transparency-documents.destroy', $doc) }}"
                                      onsubmit="return confirm('Excluir este documento? O arquivo PDF também será removido.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold uppercase tracking-wider text-red-600 hover:underline">Excluir</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-oassab-gray">Nenhum documento cadastrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($documents->hasPages())
        <div class="mt-6">
            {{ $documents->links() }}
        </div>
    @endif
@endsection
