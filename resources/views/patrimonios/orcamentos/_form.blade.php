<form method="POST"
      action="{{ $orcamento->exists ? route('patrimonios.orcamentos.update', $orcamento) : route('patrimonios.orcamentos.store') }}"
      data-form-modal
      class="space-y-6">
    @csrf
    @if ($orcamento->exists) @method('PUT') @endif

    <x-patrimonios.form-section title="Item Solicitado" icon="cart3" subtitle="O que precisa ser adquirido ou substituído.">
        <div class="form-grid">
            <x-patrimonios.form-field label="Nome do Item" name="nome_item" required span="full">
                <input type="text" name="nome_item" id="nome_item" value="{{ old('nome_item', $orcamento->nome_item) }}" required class="form-input" placeholder="Ex.: Impressora laser A4">
            </x-patrimonios.form-field>

            <x-patrimonios.form-field label="Descrição" name="descricao" span="full">
                <textarea name="descricao" id="descricao" rows="2" class="form-input" placeholder="Especificações ou detalhes do item">{{ old('descricao', $orcamento->descricao) }}</textarea>
            </x-patrimonios.form-field>

            <x-patrimonios.form-field label="Categoria" name="patrimonio_categoria_id">
                <select name="patrimonio_categoria_id" id="patrimonio_categoria_id" class="form-input">
                    <option value="">—</option>
                    @foreach ($categorias as $cat)
                        <option value="{{ $cat->id }}" @selected(old('patrimonio_categoria_id', $orcamento->patrimonio_categoria_id) == $cat->id)>{{ $cat->nome }}</option>
                    @endforeach
                </select>
            </x-patrimonios.form-field>

            <x-patrimonios.form-field label="Quantidade" name="quantidade">
                <input type="number" name="quantidade" id="quantidade" value="{{ old('quantidade', $orcamento->quantidade ?? 1) }}" min="1" class="form-input">
            </x-patrimonios.form-field>
        </div>
    </x-patrimonios.form-section>

    <x-patrimonios.form-section title="Prioridade e Prazo" icon="flag" subtitle="Urgência, status do processo e responsável.">
        <div class="form-grid">
            <x-patrimonios.form-field label="Prioridade" name="prioridade">
                <select name="prioridade" id="prioridade" class="form-input">
                    @foreach (['baixa','media','alta','urgente'] as $p)
                        <option value="{{ $p }}" @selected(old('prioridade', $orcamento->prioridade) === $p)>{{ ucfirst($p) }}</option>
                    @endforeach
                </select>
            </x-patrimonios.form-field>

            <x-patrimonios.form-field label="Status" name="status">
                <select name="status" id="status" class="form-input">
                    @foreach (['aberto','em_cotacao','aprovado','cancelado','finalizado'] as $s)
                        <option value="{{ $s }}" @selected(old('status', $orcamento->status) === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
                </select>
            </x-patrimonios.form-field>

            <x-patrimonios.form-field label="Data de Necessidade" name="data_necessidade">
                <input type="date" name="data_necessidade" id="data_necessidade" value="{{ old('data_necessidade', $orcamento->data_necessidade?->format('Y-m-d')) }}" class="form-input">
            </x-patrimonios.form-field>

            <x-patrimonios.form-field label="Solicitante" name="usuario_solicitante">
                <input type="text" name="usuario_solicitante" id="usuario_solicitante" value="{{ old('usuario_solicitante', $orcamento->usuario_solicitante) }}" class="form-input" placeholder="Quem solicitou">
            </x-patrimonios.form-field>

            <x-patrimonios.form-field label="Justificativa" name="justificativa" span="full">
                <textarea name="justificativa" id="justificativa" rows="2" class="form-input" placeholder="Motivo da solicitação">{{ old('justificativa', $orcamento->justificativa) }}</textarea>
            </x-patrimonios.form-field>
        </div>
    </x-patrimonios.form-section>

    <x-patrimonios.form-actions submit="Salvar Orçamento" />
</form>

@if ($orcamento->exists)
    <x-patrimonios.form-section
        title="Propostas de Fornecedores"
        icon="building"
        subtitle="Compare cotações e selecione a melhor opção."
        class="mt-6"
    >
        @if ($propostas->isNotEmpty())
            <div class="mb-6 space-y-3">
                @foreach ($propostas as $prop)
                    <div @class([
                        'form-proposta-card',
                        'form-proposta-card--selected' => $prop->selecionada,
                    ])>
                        <div class="min-w-0">
                            <p class="font-semibold text-oassab-blue">{{ $prop->fornecedor }}</p>
                            <p class="mt-1 text-sm text-oassab-gray">
                                <span class="font-medium text-oassab-blue">R$ {{ number_format($prop->valor_total, 2, ',', '.') }}</span>
                                @if ($prop->prazo_entrega)
                                    <span class="mx-1 text-oassab-border">·</span>
                                    {{ $prop->prazo_entrega }}
                                @endif
                            </p>
                        </div>
                        @if ($prop->selecionada)
                            <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">
                                <i class="bi bi-check-circle-fill"></i>
                                Selecionada
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <p class="mb-6 rounded-xl border border-dashed border-oassab-border bg-oassab-cream/30 px-4 py-6 text-center text-sm text-oassab-gray">
                Nenhuma proposta cadastrada ainda.
            </p>
        @endif

        @can('update', $orcamento)
            <form method="POST" action="{{ route('patrimonios.orcamentos.propostas.store', $orcamento) }}" data-form-modal class="border-t border-oassab-border pt-5">
                @csrf

                <p class="mb-4 flex items-center gap-2 text-sm font-semibold text-oassab-blue">
                    <i class="bi bi-plus-circle"></i>
                    Nova Proposta
                </p>

                <div class="form-grid">
                    <x-patrimonios.form-field label="Fornecedor" name="fornecedor" required>
                        <input type="text" name="fornecedor" id="fornecedor" required class="form-input" placeholder="Nome da empresa">
                    </x-patrimonios.form-field>

                    <x-patrimonios.form-field label="Contato" name="contato_fornecedor">
                        <input type="text" name="contato_fornecedor" id="contato_fornecedor" class="form-input" placeholder="Telefone ou e-mail">
                    </x-patrimonios.form-field>

                    <x-patrimonios.form-field label="Valor Unitário (R$)" name="valor_unitario" required>
                        <input type="number" step="0.01" name="valor_unitario" id="valor_unitario" required class="form-input" placeholder="0,00">
                    </x-patrimonios.form-field>

                    <x-patrimonios.form-field label="Quantidade" name="quantidade_proposta" required>
                        <input type="number" name="quantidade" id="quantidade_proposta" value="1" min="1" required class="form-input">
                    </x-patrimonios.form-field>

                    <x-patrimonios.form-field label="Frete (R$)" name="custo_frete">
                        <input type="number" step="0.01" name="custo_frete" id="custo_frete" value="0" class="form-input">
                    </x-patrimonios.form-field>

                    <x-patrimonios.form-field label="Instalação (R$)" name="custo_instalacao">
                        <input type="number" step="0.01" name="custo_instalacao" id="custo_instalacao" value="0" class="form-input">
                    </x-patrimonios.form-field>

                    <x-patrimonios.form-field label="Prazo de Entrega" name="prazo_entrega">
                        <input type="text" name="prazo_entrega" id="prazo_entrega" class="form-input" placeholder="Ex.: 15 dias úteis">
                    </x-patrimonios.form-field>

                    <x-patrimonios.form-field label="Forma de Pagamento" name="forma_pagamento">
                        <input type="text" name="forma_pagamento" id="forma_pagamento" class="form-input" placeholder="Ex.: 30/60 dias">
                    </x-patrimonios.form-field>

                    <div class="md:col-span-2">
                        <label class="form-toggle">
                            <input type="checkbox" name="selecionada" value="1" class="form-checkbox">
                            <span>
                                <span class="block text-sm font-semibold text-oassab-blue">Marcar como selecionada</span>
                                <span class="block text-xs text-oassab-gray">Substitui a proposta atualmente selecionada.</span>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="form-actions mt-5">
                    <button type="submit" class="btn-blue-lg">
                        <i class="bi bi-plus-lg"></i>
                        Adicionar Proposta
                    </button>
                </div>
            </form>
        @endcan
    </x-patrimonios.form-section>
@endif
