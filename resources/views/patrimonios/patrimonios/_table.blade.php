<div class="overflow-hidden rounded-xl border border-oassab-border bg-white shadow-sm">
    <table class="min-w-full divide-y divide-oassab-border text-sm">
        <thead class="bg-oassab-cream">
            <tr>
                <th class="px-4 py-3 text-left font-semibold text-oassab-blue">Código</th>
                <th class="px-4 py-3 text-left font-semibold text-oassab-blue">Nome</th>
                <th class="px-4 py-3 text-center font-semibold text-oassab-blue">Qtd.</th>
                <th class="px-4 py-3 text-left font-semibold text-oassab-blue">Categoria</th>
                <th class="px-4 py-3 text-left font-semibold text-oassab-blue">Valor Unit.</th>
                <th class="px-4 py-3 text-left font-semibold text-oassab-blue">Localização</th>
                <th class="px-4 py-3 text-right font-semibold text-oassab-blue">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-oassab-border">
            @forelse ($patrimonios as $p)
                <tr class="hover:bg-oassab-cream/50">
                    <td class="px-4 py-3 font-mono text-xs">
                        {{ $p->codigoResumo() }}
                        @if ($p->unidades() > 1)
                            <span class="mt-1 block text-[11px] font-sans text-oassab-gray">Ref. {{ $p->codigo }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('patrimonios.patrimonios.show', $p) }}" class="font-medium text-oassab-blue hover:underline">{{ $p->nome }}</a>
                        @unless ($p->ativo)
                            <span class="ml-2 rounded bg-gray-200 px-2 py-0.5 text-xs">Inativo</span>
                        @endunless
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if ($p->unidades() > 1)
                            <span class="inline-flex min-w-8 items-center justify-center rounded-full bg-oassab-blue/10 px-2 py-0.5 text-xs font-semibold text-oassab-blue">{{ $p->unidades() }}</span>
                        @else
                            <span class="text-oassab-gray">1</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-oassab-gray">{{ $p->categoria?->nome ?? '—' }}</td>
                    <td class="px-4 py-3">
                        R$ {{ number_format($p->valor_atual, 2, ',', '.') }}
                        @if ($p->unidades() > 1)
                            <span class="block text-[11px] text-oassab-gray">Total: R$ {{ number_format($p->valorAtualTotal(), 2, ',', '.') }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-oassab-gray">{{ $p->localizacao ?? '—' }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="inline-flex items-center justify-end gap-1">
                            <x-patrimonios.qrcode-trigger :patrimonio="$p" />
                            @can('update', $p)
                                <x-patrimonios.icon-button
                                    icon="pencil"
                                    title="Editar"
                                    :modal-url="route('patrimonios.patrimonios.edit', $p)"
                                    variant="orange"
                                />
                            @endcan
                            @can('delete', $p)
                                <form method="POST" action="{{ route('patrimonios.patrimonios.destroy', $p) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <x-patrimonios.icon-button
                                        icon="trash"
                                        title="Excluir"
                                        type="button"
                                        variant="red"
                                        class="js-open-delete-modal"
                                        data-name="{{ $p->nome }}"
                                        data-unidades="{{ $p->unidades() }}"
                                    />
                                </form>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-oassab-gray">Nenhum patrimônio encontrado.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<x-pagination :paginator="$patrimonios" />
