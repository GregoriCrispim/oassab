@extends('admin.layouts.admin')

@section('title', 'Editais')
@section('subtitle', 'Programa Edital Aberto — publicações e PDFs')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <form method="GET" action="{{ route('admin.editais.index') }}" class="flex flex-wrap items-end gap-3">
            <label class="block">
                <span class="text-xs font-semibold uppercase tracking-wider text-oassab-blue">Buscar</span>
                <input type="search" name="search" value="{{ $currentSearch }}" placeholder="Título ou slug"
                       class="mt-1 rounded-lg border border-oassab-border bg-white px-3 py-2 text-sm text-oassab-blue focus:border-oassab-orange focus:outline-none">
            </label>
            <button type="submit" class="btn-ghost">Filtrar</button>
            @if ($currentSearch)
                <a href="{{ route('admin.editais.index') }}" class="text-xs font-semibold uppercase tracking-wider text-oassab-gray hover:text-oassab-orange">Limpar</a>
            @endif
        </form>

        <a href="{{ route('admin.editais.create') }}" class="btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
            Novo edital
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-oassab-border bg-white shadow-sm">
        <table class="min-w-full divide-y divide-oassab-border text-sm">
            <thead class="bg-oassab-cream text-xs uppercase tracking-wider text-oassab-gray">
                <tr>
                    <th class="px-4 py-3 text-left">Título</th>
                    <th class="px-4 py-3 text-left">Data</th>
                    <th class="px-4 py-3 text-left">PDFs</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-oassab-border bg-white">
                @forelse ($editais as $edital)
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-medium text-oassab-blue">{{ $edital->title }}</p>
                            <p class="text-xs text-oassab-gray">/editais/{{ $edital->slug }}</p>
                        </td>
                        <td class="px-4 py-3 text-oassab-gray">{{ $edital->date?->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-oassab-gray">
                            @if ($edital->hasMainFile())
                                <span class="mr-1 inline-block rounded-full bg-oassab-blue/5 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wider text-oassab-blue">Principal</span>
                            @endif
                            @if ($edital->attachments_count > 0)
                                <span class="inline-block rounded-full bg-oassab-orange/10 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wider text-oassab-orange">{{ $edital->attachments_count }} anexo(s)</span>
                            @endif
                            @if (! $edital->hasMainFile() && $edital->attachments_count === 0)
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($edital->is_published)
                                <span class="rounded-full bg-green-100 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wider text-green-800">Publicado</span>
                            @else
                                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wider text-amber-800">Rascunho</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('edital', $edital) }}" target="_blank" rel="noopener"
                                   class="text-xs font-semibold uppercase tracking-wider text-oassab-gray hover:text-oassab-orange">Ver</a>
                                <a href="{{ route('admin.editais.edit', $edital) }}"
                                   class="text-xs font-semibold uppercase tracking-wider text-oassab-orange hover:underline">Editar</a>
                                <form method="POST" action="{{ route('admin.editais.destroy', $edital) }}"
                                      onsubmit="return confirm('Excluir este edital? Todos os PDFs serão removidos.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold uppercase tracking-wider text-red-600 hover:underline">Excluir</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-oassab-gray">Nenhum edital cadastrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($editais->hasPages())
        <div class="mt-6">{{ $editais->links() }}</div>
    @endif
@endsection
