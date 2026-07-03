<form method="POST"
      action="{{ $categoria->exists ? route('patrimonios.categorias.update', $categoria) : route('patrimonios.categorias.store') }}"
      data-form-modal
      data-categoria-form
      data-campos-iniciais='@json($campos)'
      class="space-y-6">
    @csrf
    @if ($categoria->exists) @method('PUT') @endif

    <x-patrimonios.form-section
        title="Dados da Categoria"
        icon="tag"
        subtitle="Nome, aparência e depreciação padrão para novos patrimônios."
    >
        <div class="space-y-5">
            <x-patrimonios.form-field label="Nome" name="nome" required>
                <input type="text" name="nome" id="nome" value="{{ old('nome', $categoria->nome) }}" required class="form-input" placeholder="Ex.: Informática">
            </x-patrimonios.form-field>

            <x-patrimonios.form-field label="Descrição" name="descricao">
                <textarea name="descricao" id="descricao" rows="2" class="form-input" placeholder="Breve descrição da categoria">{{ old('descricao', $categoria->descricao) }}</textarea>
            </x-patrimonios.form-field>

            <div class="form-grid">
                <x-patrimonios.form-field label="Depreciação (%/ano)" name="indice_depreciacao_padrao" hint="Aplicada automaticamente ao cadastrar patrimônios desta categoria.">
                    <input type="number" step="0.01" name="indice_depreciacao_padrao" id="indice_depreciacao_padrao" value="{{ old('indice_depreciacao_padrao', $categoria->indice_depreciacao_padrao ?? 10) }}" class="form-input">
                </x-patrimonios.form-field>

                <x-patrimonios.form-field label="Ícone" name="icone" hint="Classe Bootstrap Icons (ex.: bi-laptop).">
                    <div class="mt-2 flex items-center gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border border-oassab-border bg-oassab-cream text-lg text-oassab-blue" id="iconePreview">
                            <i class="bi {{ old('icone', $categoria->icone ?? 'bi-tag') }}"></i>
                        </span>
                        <input type="text" name="icone" id="icone" value="{{ old('icone', $categoria->icone ?? 'bi-tag') }}" class="form-input mt-0 flex-1" placeholder="bi-tag">
                    </div>
                </x-patrimonios.form-field>

                <x-patrimonios.form-field label="Cor" name="cor">
                    <input type="color" name="cor" id="cor" value="{{ old('cor', $categoria->cor ?? '#6366f1') }}" class="form-input form-input--color">
                </x-patrimonios.form-field>
            </div>

            <label class="form-toggle">
                <input type="hidden" name="ativo" value="0">
                <input type="checkbox" name="ativo" value="1" @checked(old('ativo', $categoria->ativo ?? true)) class="form-checkbox">
                <span>
                    <span class="block text-sm font-semibold text-oassab-blue">Categoria ativa</span>
                    <span class="block text-xs text-oassab-gray">Categorias inativas não aparecem na seleção de novos patrimônios.</span>
                </span>
            </label>
        </div>
    </x-patrimonios.form-section>

    <x-patrimonios.form-section title="Campos Customizados" icon="ui-radios" subtitle="Defina campos extras exibidos nos patrimônios desta categoria.">
        <x-slot:actions>
            <button type="button" id="addCampo" class="btn-blue text-xs uppercase tracking-wider">
                <i class="bi bi-plus-lg"></i>
                Campo
            </button>
        </x-slot:actions>

        <div id="camposList" class="space-y-4"></div>
        <p id="camposEmptyState" class="rounded-xl border border-dashed border-oassab-border bg-oassab-cream/30 px-4 py-8 text-center text-sm text-oassab-gray">
            <i class="bi bi-ui-checks mr-1"></i>
            Nenhum campo customizado. Clique em <strong>+ Campo</strong> para adicionar.
        </p>
    </x-patrimonios.form-section>

    <x-patrimonios.form-actions />
</form>
