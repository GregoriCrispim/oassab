@extends('patrimonios.layouts.app')

@section('title', $patrimonio->nome)

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
        <div class="min-w-0">
            <p class="font-mono text-sm text-oassab-gray">{{ $patrimonio->codigoResumo() }}</p>
            <h2 class="font-heading text-xl font-bold text-oassab-blue sm:text-2xl">{{ $patrimonio->nome }}</h2>
            @if ($patrimonio->unidades() > 1)
                <p class="mt-1 text-sm text-oassab-gray">{{ $patrimonio->unidades() }} unidades — código inicial <span class="font-mono">{{ $patrimonio->codigo }}</span></p>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <x-patrimonios.qrcode-trigger :patrimonio="$patrimonio" />
            @can('update', $patrimonio)
                <x-patrimonios.icon-button
                    icon="pencil"
                    title="Editar"
                    :modal-url="route('patrimonios.patrimonios.edit', $patrimonio)"
                    variant="orange"
                />
            @endcan
            @can('delete', $patrimonio)
                <form method="POST" action="{{ route('patrimonios.patrimonios.destroy', $patrimonio) }}" class="inline">
                    @csrf
                    @method('DELETE')
                    <x-patrimonios.icon-button
                        icon="trash"
                        title="Excluir"
                        type="button"
                        variant="red"
                        class="js-open-delete-modal"
                        data-name="{{ $patrimonio->nome }}"
                        data-unidades="{{ $patrimonio->unidades() }}"
                    />
                </form>
            @endcan
        </div>
    </div>

    @if ($dadosUnidadeAtiva && $patrimonio->unidades() > 1)
        <div id="unidade-ativa" class="mb-6 overflow-hidden rounded-xl border-2 border-oassab-orange bg-white shadow-sm">
            <div class="border-b border-oassab-border bg-oassab-orange/10 px-4 py-3 sm:px-6">
                <p class="text-xs font-semibold uppercase tracking-wide text-oassab-orange">Unidade escaneada</p>
                <p class="font-mono text-lg font-bold text-oassab-blue">{{ $dadosUnidadeAtiva['codigo'] }}</p>
            </div>
            <div class="grid gap-4 p-4 sm:grid-cols-[auto,1fr] sm:items-start sm:p-6">
                @if ($dadosUnidadeAtiva['imagem'])
                    <img
                        src="{{ $dadosUnidadeAtiva['imagem'] }}"
                        alt="Unidade {{ $dadosUnidadeAtiva['codigo'] }}"
                        class="mx-auto h-40 w-40 rounded-xl border border-oassab-border bg-white object-contain p-2 sm:mx-0"
                    >
                @endif
                <div class="min-w-0">
                    @if ($dadosUnidadeAtiva['descricao'])
                        <p class="text-sm text-oassab-gray">{{ $dadosUnidadeAtiva['descricao'] }}</p>
                    @else
                        <p class="text-sm italic text-oassab-gray">Sem descrição específica para esta unidade.</p>
                    @endif
                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="flex flex-col gap-1 sm:flex-row sm:justify-between sm:gap-4">
                            <dt class="text-oassab-gray">Conjunto</dt>
                            <dd class="font-medium sm:text-right">{{ $patrimonio->nome }} ({{ $patrimonio->unidades() }} un.)</dd>
                        </div>
                        <div class="flex flex-col gap-1 sm:flex-row sm:justify-between sm:gap-4">
                            <dt class="text-oassab-gray">Código do grupo</dt>
                            <dd class="font-mono sm:text-right">{{ $patrimonio->codigo }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    @endif

    @php
        $imagemUrl = $patrimonio->imagemUrl();
    @endphp

    <div @class([
        'mb-6 grid gap-6',
        'lg:grid-cols-3 lg:items-start' => $imagemUrl,
    ])>
        <div @class([
            'rounded-xl border border-oassab-border bg-white p-4 shadow-sm sm:p-6',
            'lg:col-span-2' => $imagemUrl,
        ])>
            <h3 class="mb-4 font-heading font-bold text-oassab-blue">Informações</h3>

            <dl class="space-y-3 text-sm">
                <div class="flex flex-col gap-1 sm:flex-row sm:justify-between sm:gap-4"><dt class="text-oassab-gray">Quantidade</dt><dd class="font-medium sm:text-right">{{ $patrimonio->unidades() }}</dd></div>
                <div class="flex flex-col gap-1 sm:flex-row sm:justify-between sm:gap-4"><dt class="text-oassab-gray">Categoria</dt><dd class="font-medium sm:text-right">{{ $patrimonio->categoria?->nome ?? '—' }}</dd></div>
                <div class="flex flex-col gap-1 sm:flex-row sm:justify-between sm:gap-4"><dt class="text-oassab-gray">Valor Unit. Aquisição</dt><dd class="sm:text-right">R$ {{ number_format($patrimonio->valor_aquisicao, 2, ',', '.') }}</dd></div>
                <div class="flex flex-col gap-1 sm:flex-row sm:justify-between sm:gap-4"><dt class="text-oassab-gray">Valor Unit. Atual</dt><dd class="font-semibold text-green-600 sm:text-right">R$ {{ number_format($patrimonio->valor_atual, 2, ',', '.') }}</dd></div>
                @if ($patrimonio->unidades() > 1)
                    <div class="flex flex-col gap-1 sm:flex-row sm:justify-between sm:gap-4"><dt class="text-oassab-gray">Valor Total Atual</dt><dd class="font-semibold text-green-600 sm:text-right">R$ {{ number_format($patrimonio->valorAtualTotal(), 2, ',', '.') }}</dd></div>
                @endif
                <div class="flex flex-col gap-1 sm:flex-row sm:justify-between sm:gap-4"><dt class="text-oassab-gray">Depreciação</dt><dd class="sm:text-right">{{ $patrimonio->indice_depreciacao }}%/ano</dd></div>
                <div class="flex flex-col gap-1 sm:flex-row sm:justify-between sm:gap-4"><dt class="text-oassab-gray">Data Aquisição</dt><dd class="sm:text-right">{{ $patrimonio->data_aquisicao->format('d/m/Y') }}</dd></div>
                <div class="flex flex-col gap-1 sm:flex-row sm:justify-between sm:gap-4"><dt class="text-oassab-gray">Localização</dt><dd class="sm:text-right">{{ $patrimonio->localizacao ?? '—' }}</dd></div>
                <div class="flex flex-col gap-1 sm:flex-row sm:justify-between sm:gap-4"><dt class="text-oassab-gray">Responsável</dt><dd class="sm:text-right">{{ $patrimonio->responsavel ?? '—' }}</dd></div>
                <div class="flex flex-col gap-1 sm:flex-row sm:justify-between sm:gap-4"><dt class="text-oassab-gray">Status</dt><dd class="sm:text-right">{{ $patrimonio->ativo ? 'Ativo' : 'Inativo' }}</dd></div>
            </dl>

            @if ($patrimonio->descricao)
                <p class="mt-4 text-sm text-oassab-gray">{{ $patrimonio->descricao }}</p>
            @endif

            @if ($patrimonio->unidades() > 1)
                <div class="mt-6 border-t border-oassab-border pt-4">
                    <p class="mb-3 text-xs font-semibold uppercase text-oassab-gray">Unidades de inventário</p>
                    <div class="space-y-3">
                        @foreach ($patrimonio->itensInventario as $unidade)
                            <div
                                id="unidade-{{ $unidade->codigo }}"
                                @class([
                                    'flex flex-col gap-3 rounded-xl border bg-oassab-cream/20 p-4 sm:flex-row sm:items-start',
                                    'border-oassab-orange ring-2 ring-oassab-orange/30' => ($codigoUnidade ?? '') === $unidade->codigo,
                                    'border-oassab-border' => ($codigoUnidade ?? '') !== $unidade->codigo,
                                ])
                            >
                                @if ($unidade->imagemEfetivaUrl())
                                    <img
                                        src="{{ $unidade->imagemEfetivaUrl() }}"
                                        alt="Unidade {{ $unidade->codigo }}"
                                        class="mx-auto h-24 w-24 shrink-0 rounded-lg border border-oassab-border bg-white object-contain p-1 sm:mx-0"
                                        loading="lazy"
                                    >
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="font-mono text-sm font-semibold text-oassab-blue">{{ $unidade->codigo }}</p>
                                    @if ($unidade->descricao)
                                        <p class="mt-1 text-sm text-oassab-gray">{{ $unidade->descricao }}</p>
                                    @elseif ($patrimonio->descricao)
                                        <p class="mt-1 text-sm text-oassab-gray italic">{{ $patrimonio->descricao }}</p>
                                    @endif
                                    @if ($unidade->imagem)
                                        <p class="mt-1 text-xs text-oassab-gray">Foto personalizada</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        @if ($imagemUrl)
            <figure class="overflow-hidden rounded-xl border border-oassab-border bg-white p-4 shadow-sm lg:col-span-1">
                <img
                    src="{{ $imagemUrl }}"
                    alt="Imagem de {{ $patrimonio->nome }}"
                    class="mx-auto max-h-96 w-full object-contain"
                    loading="lazy"
                >
            </figure>
        @endif
    </div>

    @if ($patrimonio->campoValores->isNotEmpty())
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-xl border border-oassab-border bg-white p-6 shadow-sm">
                <h3 class="mb-4 font-heading font-bold text-oassab-blue">Campos Customizados</h3>
                <dl class="space-y-3 text-sm">
                    @foreach ($patrimonio->campoValores as $cv)
                        <div class="flex justify-between">
                            <dt class="text-oassab-gray">{{ $cv->campo?->label }}</dt>
                            <dd>{{ $cv->valor }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </div>
    @endif

    @php
        $documentos = $patrimonio->arquivos->where('categoria_arquivo', '!=', 'imagem');
    @endphp

    @if ($documentos->isNotEmpty())
        <div class="mt-6 rounded-xl border border-oassab-border bg-white p-6 shadow-sm">
            <h3 class="mb-4 font-heading font-bold text-oassab-blue">Documentos Anexos</h3>
            <ul class="divide-y divide-oassab-border">
                @foreach ($documentos as $arquivo)
                    <li class="flex items-center justify-between gap-4 py-3 first:pt-0 last:pb-0">
                        <div class="min-w-0">
                            <p class="truncate font-medium text-oassab-blue">{{ $arquivo->nome_original }}</p>
                            <p class="text-xs text-oassab-gray">
                                {{ ucfirst(str_replace('_', ' ', $arquivo->categoria_arquivo)) }}
                                @if ($arquivo->tamanho)
                                    · {{ number_format($arquivo->tamanho / 1024, 1, ',', '.') }} KB
                                @endif
                            </p>
                        </div>
                        <a href="{{ $arquivo->fileUrl() }}" target="_blank" rel="noopener"
                           class="btn-ghost shrink-0 py-2 text-xs">
                            <i class="bi bi-download"></i>
                            Baixar
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($patrimonio->manutencoes->isNotEmpty())
        <div class="mt-6 rounded-xl border border-oassab-border bg-white p-6 shadow-sm">
            <h3 class="mb-4 font-heading font-bold text-oassab-blue">Manutenções Recentes</h3>
            @foreach ($patrimonio->manutencoes as $man)
                <div class="border-b border-oassab-border py-3 last:border-0">
                    <p class="font-medium">{{ $man->descricao }}</p>
                    <p class="text-xs text-oassab-gray">{{ $man->data_manutencao->format('d/m/Y') }} — {{ $man->tipo }} — {{ $man->status }}</p>
                </div>
            @endforeach
        </div>
    @endif
@endsection
