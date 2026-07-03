<form method="POST"
      action="{{ $patrimonio->exists ? route('patrimonios.patrimonios.update', $patrimonio) : route('patrimonios.patrimonios.store') }}"
      enctype="multipart/form-data"
      data-form-modal
      data-patrimonio-form
      data-categorias-campos='@json($categorias->mapWithKeys(fn ($c) => [$c->id => $c->camposAtivos]))'
      data-campo-valores='@json($campoValores ?? [])'
      data-unidades-inventario='@json($unidadesInventario ?? [])'
      data-modo-imagem="{{ old('modo_imagem', $modoImagem ?? 'unica') }}"
      data-is-edit="{{ $patrimonio->exists ? '1' : '0' }}"
      class="space-y-6">
    @csrf
    @if ($patrimonio->exists)
        @method('POST')
    @endif

    <x-patrimonios.form-section
        title="Identificação"
        icon="box-seam"
        subtitle="Nome, descrição e quantidade de unidades do bem patrimonial."
    >
        <div class="form-grid">
            @if ($patrimonio->exists)
                <x-patrimonios.form-field label="Código principal">
                    <input type="text" value="{{ $patrimonio->codigo }}" disabled class="form-input font-mono">
                </x-patrimonios.form-field>
                <x-patrimonios.form-field label="Quantidade" name="quantidade" hint="Número de unidades vinculadas a este registro. Use os botões abaixo para excluir unidades específicas.">
                    <input type="number" name="quantidade" id="quantidade" value="{{ old('quantidade', $patrimonio->unidades()) }}" min="{{ max(1, $patrimonio->unidades()) }}" max="999" class="form-input">
                </x-patrimonios.form-field>
            @else
                <x-patrimonios.form-field
                    label="Quantidade"
                    name="quantidade"
                    hint="Um único registro com várias unidades iguais (1–999)."
                >
                    <input type="number" name="quantidade" id="quantidade" value="{{ old('quantidade', 1) }}" min="1" max="999" class="form-input">
                </x-patrimonios.form-field>
            @endif

            <x-patrimonios.form-field label="Nome" name="nome" required span="full">
                <input type="text" name="nome" id="nome" value="{{ old('nome', $patrimonio->nome) }}" required class="form-input" placeholder="Ex.: Notebook Dell Latitude 5420">
            </x-patrimonios.form-field>

            <x-patrimonios.form-field label="Descrição" name="descricao" span="full">
                <textarea name="descricao" id="descricao" rows="2" class="form-input" placeholder="Detalhes adicionais sobre o item">{{ old('descricao', $patrimonio->descricao) }}</textarea>
            </x-patrimonios.form-field>
        </div>
    </x-patrimonios.form-section>

    <x-patrimonios.form-section
        title="Classificação e Valores"
        icon="tags"
        subtitle="Categoria, aquisição e depreciação anual."
    >
        <div class="form-grid">
            <x-patrimonios.form-field label="Categoria" name="categoriaSelect">
                <select name="patrimonio_categoria_id" id="categoriaSelect" class="form-input">
                    <option value="">Selecione...</option>
                    @foreach ($categorias as $cat)
                        <option value="{{ $cat->id }}" data-depreciacao="{{ $cat->indice_depreciacao_padrao }}"
                            @selected(old('patrimonio_categoria_id', $patrimonio->patrimonio_categoria_id) == $cat->id)>
                            {{ $cat->nome }}
                        </option>
                    @endforeach
                </select>
            </x-patrimonios.form-field>

            <x-patrimonios.form-field label="Data de Aquisição" name="data_aquisicao" required>
                <input type="date" name="data_aquisicao" id="data_aquisicao" value="{{ old('data_aquisicao', $patrimonio->data_aquisicao?->format('Y-m-d')) }}" required class="form-input">
            </x-patrimonios.form-field>

            <x-patrimonios.form-field label="Valor de Aquisição (R$)" name="valor_aquisicao" required>
                <input type="number" step="0.01" name="valor_aquisicao" id="valor_aquisicao" value="{{ old('valor_aquisicao', $patrimonio->valor_aquisicao) }}" required class="form-input" placeholder="0,00">
            </x-patrimonios.form-field>

            <x-patrimonios.form-field label="Depreciação (%/ano)" name="indiceDepreciacao" required hint="Preenchido automaticamente ao selecionar a categoria.">
                <input type="number" step="0.01" name="indice_depreciacao" id="indiceDepreciacao" value="{{ old('indice_depreciacao', $patrimonio->indice_depreciacao ?? 10) }}" required class="form-input">
            </x-patrimonios.form-field>
        </div>
    </x-patrimonios.form-section>

    <x-patrimonios.form-section
        title="Localização e Documentação"
        icon="geo-alt"
        subtitle="Onde o bem está, quem responde e dados fiscais."
    >
        <div class="form-grid">
            <x-patrimonios.form-field label="Localização" name="localizacao">
                <input type="text" name="localizacao" id="localizacao" value="{{ old('localizacao', $patrimonio->localizacao) }}" class="form-input" placeholder="Ex.: Sala 204 — Secretaria">
            </x-patrimonios.form-field>

            <x-patrimonios.form-field label="Responsável" name="responsavel">
                <input type="text" name="responsavel" id="responsavel" value="{{ old('responsavel', $patrimonio->responsavel) }}" class="form-input" placeholder="Nome do responsável">
            </x-patrimonios.form-field>

            <x-patrimonios.form-field label="Nota Fiscal" name="nota_fiscal">
                <input type="text" name="nota_fiscal" id="nota_fiscal" value="{{ old('nota_fiscal', $patrimonio->nota_fiscal) }}" class="form-input" placeholder="Número da NF">
            </x-patrimonios.form-field>

            <x-patrimonios.form-field label="Observações" name="observacoes" span="full">
                <textarea name="observacoes" id="observacoes" rows="2" class="form-input" placeholder="Anotações gerais">{{ old('observacoes', $patrimonio->observacoes) }}</textarea>
            </x-patrimonios.form-field>

            <div class="md:col-span-2">
                <label class="form-toggle">
                    <input type="hidden" name="ativo" value="0">
                    <input type="checkbox" name="ativo" value="1" id="ativo" @checked(old('ativo', $patrimonio->ativo ?? true)) class="form-checkbox">
                    <span>
                        <span class="block text-sm font-semibold text-oassab-blue">Patrimônio ativo</span>
                        <span class="block text-xs text-oassab-gray">Itens inativos permanecem no histórico, mas não entram nos relatórios principais.</span>
                    </span>
                </label>
            </div>
        </div>
    </x-patrimonios.form-section>

    <x-patrimonios.form-section
        id="camposCustomizados"
        title="Campos Customizados"
        icon="ui-radios"
        subtitle="Preenchidos conforme a categoria selecionada."
    >
        <div id="camposContainer" class="form-grid"></div>
        <p id="camposEmptyHint" class="hidden rounded-xl border border-dashed border-oassab-border bg-oassab-cream/30 px-4 py-6 text-center text-sm text-oassab-gray">
            <i class="bi bi-info-circle mr-1"></i>
            Selecione uma categoria para exibir os campos adicionais.
        </p>
    </x-patrimonios.form-section>

    <x-patrimonios.form-section
        id="secaoUnidades"
        title="Unidades de Inventário"
        icon="boxes"
        subtitle="Gerencie descrição, foto e exclusão de cada unidade quando a quantidade for maior que 1."
        class="hidden"
    >
        <div class="mb-4 flex flex-wrap gap-4">
            <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-oassab-border px-4 py-2 text-sm transition has-[:checked]:border-oassab-orange has-[:checked]:bg-orange-50">
                <input type="radio" name="modo_imagem" value="unica" class="form-radio" @checked(old('modo_imagem', $modoImagem ?? 'unica') === 'unica')>
                <span><i class="bi bi-image mr-1"></i> Uma foto para todos</span>
            </label>
            <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-oassab-border px-4 py-2 text-sm transition has-[:checked]:border-oassab-orange has-[:checked]:bg-orange-50">
                <input type="radio" name="modo_imagem" value="individual" class="form-radio" @checked(old('modo_imagem', $modoImagem ?? 'unica') === 'individual')>
                <span><i class="bi bi-images mr-1"></i> Foto por unidade</span>
            </label>
        </div>

        <p class="mb-4 text-xs text-oassab-gray">
            Com &quot;Uma foto para todos&quot;, a imagem principal vale para todas as unidades. Você ainda pode personalizar foto ou descrição de unidades específicas abaixo.
        </p>

        <div id="unidadesContainer" class="space-y-4"></div>
    </x-patrimonios.form-section>

    <x-patrimonios.form-section
        title="Arquivos"
        icon="paperclip"
        subtitle="Imagem de capa e documentos anexos."
    >
        <div class="form-grid">
            <x-patrimonios.form-field id="campoImagemGrupo" label="Imagem" name="imagem" :hint="$patrimonio->imagemUrl() ? 'Imagem atual cadastrada. Envie um novo arquivo para substituir.' : 'Formatos de imagem (JPG, PNG, WebP).'">
                @if ($patrimonio->imagemUrl())
                    <div id="previewImagemGrupo" class="mb-3 overflow-hidden rounded-xl border border-oassab-border bg-oassab-cream/40 p-2">
                        <img src="{{ $patrimonio->imagemUrl() }}" alt="Imagem atual" class="mx-auto max-h-40 object-contain">
                    </div>
                @else
                    <div id="previewImagemGrupo" class="hidden mb-3 overflow-hidden rounded-xl border border-oassab-border bg-oassab-cream/40 p-2"></div>
                @endif
                <input type="file" name="imagem" id="imagem" accept="image/*" class="form-file">
            </x-patrimonios.form-field>

            <x-patrimonios.form-field label="Documentos" name="arquivos" hint="Você pode selecionar vários arquivos de uma vez.">
                <input type="file" name="arquivos[]" id="arquivos" multiple class="form-file">
            </x-patrimonios.form-field>
        </div>
    </x-patrimonios.form-section>

    <x-patrimonios.form-actions />
</form>
