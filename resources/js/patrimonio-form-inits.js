export function initPatrimonioForm(form) {
    const categoriaSelect = form.querySelector('#categoriaSelect');
    const indiceDepreciacao = form.querySelector('#indiceDepreciacao');
    const container = form.querySelector('#camposContainer');
    const emptyHint = form.querySelector('#camposEmptyHint');
    const quantidadeInput = form.querySelector('#quantidade');
    const secaoUnidades = form.querySelector('#secaoUnidades');
    const unidadesContainer = form.querySelector('#unidadesContainer');
    const campoImagemGrupo = form.querySelector('#campoImagemGrupo');
    const previewImagemGrupo = form.querySelector('#previewImagemGrupo');
    const modoImagemInputs = form.querySelectorAll('input[name="modo_imagem"]');

    if (! categoriaSelect || ! container) {
        return;
    }

    let categoriasCampos = {};
    let valoresAtuais = {};
    let unidadesInventario = [];
    let isEdit = false;

    try {
        categoriasCampos = JSON.parse(form.dataset.categoriasCampos || '{}');
        valoresAtuais = JSON.parse(form.dataset.campoValores || '{}');
        unidadesInventario = JSON.parse(form.dataset.unidadesInventario || '[]');
        isEdit = form.dataset.isEdit === '1';
    } catch {
        return;
    }

    const getModoImagem = () => form.querySelector('input[name="modo_imagem"]:checked')?.value || 'unica';

    const toggleImagemGrupo = () => {
        const modo = getModoImagem();
        const quantidade = parseInt(quantidadeInput?.value || '1', 10);

        if (campoImagemGrupo) {
            campoImagemGrupo.classList.toggle('hidden', modo === 'individual' && quantidade > 1);
        }
    };

    const buildUnidadeCard = (unidade, index) => {
        const card = document.createElement('div');
        card.className = 'rounded-xl border border-oassab-border bg-oassab-cream/20 p-4';
        card.dataset.unidadeCard = '';

        if (unidade.markedDelete) {
            card.classList.add('opacity-50');
        }

        const key = unidade.isNova
            ? `novas_${index}`
            : (isEdit && unidade.id ? unidade.id : index);
        const namePrefix = unidade.isNova ? `unidades_novas[${index - unidadesInventario.length}]` : `unidades[${key}]`;
        const codigoLabel = unidade.codigo || `Unidade ${index + 1}`;
        const modo = getModoImagem();
        const showUnitImage = modo === 'individual' || isEdit;

        card.innerHTML = `
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <p class="font-mono text-sm font-semibold text-oassab-blue">${codigoLabel}</p>
                    <p class="text-xs text-oassab-gray">Unidade ${index + 1}</p>
                </div>
                ${isEdit && unidade.id ? `
                    <button type="button" data-toggle-excluir class="inline-flex items-center gap-1 rounded-lg border px-3 py-1.5 text-xs font-semibold transition ${unidade.markedDelete ? 'border-green-300 bg-green-50 text-green-700' : 'border-red-200 bg-red-50 text-red-600 hover:bg-red-100'}" ${unidadesInventario.filter((u) => ! u.markedDelete).length <= 1 && ! unidade.markedDelete ? 'disabled title="Deve restar ao menos uma unidade"' : ''}>
                        <i class="bi ${unidade.markedDelete ? 'bi-arrow-counterclockwise' : 'bi-trash'}"></i>
                        ${unidade.markedDelete ? 'Desfazer exclusão' : 'Excluir unidade'}
                    </button>
                ` : ''}
            </div>
            ${isEdit && unidade.id ? `<input type="hidden" name="unidades[${key}][id]" value="${unidade.id}">` : ''}
            ${isEdit && unidade.id ? `<input type="hidden" name="unidades[${key}][excluir]" value="${unidade.markedDelete ? '1' : '0'}" data-excluir-input>` : ''}
            <div class="form-grid">
                <div class="form-field md:col-span-2">
                    <label class="form-label">Descrição específica</label>
                    <textarea name="${namePrefix}[descricao]" rows="2" class="form-input" placeholder="Opcional — sobrescreve a descrição geral desta unidade" ${unidade.markedDelete ? 'disabled' : ''}>${unidade.descricao || ''}</textarea>
                </div>
                <div class="form-field unit-image-field ${showUnitImage ? '' : 'hidden'}" data-unit-image-field>
                    <label class="form-label">Foto da unidade</label>
                    ${unidade.imagem ? `
                        <div class="mb-2 overflow-hidden rounded-lg border border-oassab-border bg-white p-2" data-unit-preview>
                            <img src="${unidade.imagem}" alt="Foto ${codigoLabel}" class="mx-auto max-h-28 object-contain">
                        </div>
                    ` : '<div class="mb-2 hidden overflow-hidden rounded-lg border border-oassab-border bg-white p-2" data-unit-preview></div>'}
                    <input type="file" name="${namePrefix}[imagem]" accept="image/*" class="form-file" ${unidade.markedDelete ? 'disabled' : ''}>
                    ${isEdit && unidade.id && unidade.imagem ? `
                        <label class="mt-2 inline-flex items-center gap-2 text-xs text-oassab-gray">
                            <input type="checkbox" name="unidades[${key}][remover_imagem]" value="1" class="form-checkbox" ${unidade.markedDelete ? 'disabled' : ''}>
                            Remover foto personalizada (usar imagem do grupo)
                        </label>
                    ` : ''}
                </div>
            </div>
        `;

        if (isEdit && unidade.id) {
            card.querySelector('[data-toggle-excluir]')?.addEventListener('click', () => {
                const ativas = unidadesInventario.filter((u) => ! u.markedDelete);

                if (! unidade.markedDelete && ativas.length <= 1) {
                    return;
                }

                unidade.markedDelete = ! unidade.markedDelete;

                if (quantidadeInput) {
                    const delta = unidade.markedDelete ? -1 : 1;
                    quantidadeInput.value = String(Math.max(1, parseInt(quantidadeInput.value || '1', 10) + delta));
                }

                renderUnidades();
            });
        }

        return card;
    };

    const renderUnidades = () => {
        if (! unidadesContainer || ! secaoUnidades || ! quantidadeInput) {
            return;
        }

        const quantidade = Math.min(999, Math.max(1, parseInt(quantidadeInput.value || '1', 10)));

        if (quantidade <= 1 && ! isEdit) {
            secaoUnidades.classList.add('hidden');
            unidadesContainer.innerHTML = '';

            return;
        }

        if (isEdit && unidadesInventario.length === 0 && quantidade <= 1) {
            secaoUnidades.classList.add('hidden');
            unidadesContainer.innerHTML = '';

            return;
        }

        secaoUnidades.classList.remove('hidden');
        unidadesContainer.innerHTML = '';

        if (isEdit) {
            const ativas = unidadesInventario.filter((u) => ! u.markedDelete);
            const novas = Math.max(0, quantidade - ativas.length);

            unidadesInventario.forEach((unidade, index) => {
                unidadesContainer.appendChild(buildUnidadeCard(unidade, index));
            });

            for (let i = 0; i < novas; i++) {
                unidadesContainer.appendChild(buildUnidadeCard({
                    descricao: '',
                    imagem: null,
                    codigo: `Nova unidade ${i + 1}`,
                    isNova: true,
                }, unidadesInventario.length + i));
            }

            toggleImagemGrupo();

            return;
        }

        while (unidadesInventario.length < quantidade) {
            unidadesInventario.push({ descricao: '', imagem: null });
        }

        if (unidadesInventario.length > quantidade) {
            unidadesInventario.length = quantidade;
        }

        unidadesInventario.forEach((unidade, index) => {
            unidadesContainer.appendChild(buildUnidadeCard(unidade, index));
        });

        toggleImagemGrupo();
    };

    const renderCampos = (categoriaId) => {
        container.innerHTML = '';
        const campos = categoriasCampos[categoriaId] || [];

        if (emptyHint) {
            emptyHint.classList.toggle('hidden', campos.length > 0);
        }

        campos.forEach((campo) => {
            const val = valoresAtuais[campo.id] || '';
            const name = `campos_customizados[${campo.id}]`;
            const wrapper = document.createElement('div');
            wrapper.className = 'form-field';

            const label = document.createElement('label');
            label.className = 'form-label';
            label.textContent = campo.label;
            if (campo.obrigatorio) {
                const required = document.createElement('span');
                required.className = 'text-oassab-orange';
                required.textContent = ' *';
                label.appendChild(required);
            }

            let input;

            if (campo.tipo_campo === 'textarea') {
                input = document.createElement('textarea');
                input.name = name;
                input.className = 'form-input';
                input.rows = 2;
                input.textContent = val;
            } else if (campo.tipo_campo === 'select') {
                input = document.createElement('select');
                input.name = name;
                input.className = 'form-input';
                const empty = document.createElement('option');
                empty.value = '';
                empty.textContent = 'Selecione...';
                input.appendChild(empty);
                (campo.opcoes_select || '').split(',').map((o) => o.trim()).filter(Boolean).forEach((option) => {
                    const opt = document.createElement('option');
                    opt.value = option;
                    opt.textContent = option;
                    opt.selected = val === option;
                    input.appendChild(opt);
                });
            } else {
                input = document.createElement('input');
                input.type = campo.tipo_campo === 'numero' ? 'number' : (campo.tipo_campo === 'data' ? 'date' : 'text');
                input.name = name;
                input.value = val;
                input.className = 'form-input';
            }

            if (campo.obrigatorio) {
                input.required = true;
            }

            wrapper.append(label, input);
            container.appendChild(wrapper);
        });
    };

    categoriaSelect.addEventListener('change', () => {
        const dep = categoriaSelect.selectedOptions[0]?.dataset.depreciacao;
        if (dep && indiceDepreciacao) {
            indiceDepreciacao.value = dep;
        }
        renderCampos(categoriaSelect.value);
    });

    quantidadeInput?.addEventListener('input', renderUnidades);
    quantidadeInput?.addEventListener('change', renderUnidades);

    modoImagemInputs.forEach((input) => {
        input.addEventListener('change', () => {
            toggleImagemGrupo();
            renderUnidades();
        });
    });

    renderCampos(categoriaSelect.value);
    renderUnidades();
}

export function initCategoriaForm(form) {
    const camposList = form.querySelector('#camposList');
    const addCampoBtn = form.querySelector('#addCampo');
    const emptyState = form.querySelector('#camposEmptyState');
    const iconeInput = form.querySelector('#icone');
    const iconePreview = form.querySelector('#iconePreview');

    if (! camposList || ! addCampoBtn) {
        return;
    }

    let camposIniciais = [];
    let campoIndex = 0;

    try {
        camposIniciais = JSON.parse(form.dataset.camposIniciais || '[]');
    } catch {
        camposIniciais = [];
    }

    const updateEmptyState = () => {
        if (emptyState) {
            emptyState.classList.toggle('hidden', camposList.children.length > 0);
        }
    };

    const addCampoRow = (data = {}) => {
        const i = campoIndex++;
        const row = document.createElement('div');
        row.className = 'form-campo-row';
        row.dataset.campoRow = '';

        if (data.id) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = `campos[${i}][id]`;
            hidden.value = data.id;
            row.appendChild(hidden);
        }

        const tipos = ['texto', 'numero', 'data', 'select', 'textarea'];
        const tipoOptions = tipos.map((t) => `<option value="${t}" ${data.tipo_campo === t ? 'selected' : ''}>${t}</option>`).join('');

        row.innerHTML += `
            <div class="form-field"><label class="form-label">Nome</label><input name="campos[${i}][nome_campo]" value="${data.nome_campo || ''}" class="form-input"></div>
            <div class="form-field"><label class="form-label">Label</label><input name="campos[${i}][label]" value="${data.label || ''}" class="form-input"></div>
            <div class="form-field"><label class="form-label">Tipo</label>
                <select name="campos[${i}][tipo_campo]" class="form-input">${tipoOptions}</select>
            </div>
            <div class="form-field"><label class="form-label">Opções (select)</label><input name="campos[${i}][opcoes_select]" value="${data.opcoes_select || ''}" class="form-input" placeholder="Op1, Op2, Op3"></div>
            <div class="form-campo-row__actions">
                <label class="form-toggle flex-1 py-2">
                    <input type="checkbox" name="campos[${i}][obrigatorio]" value="1" class="form-checkbox" ${data.obrigatorio ? 'checked' : ''}>
                    <span class="text-sm font-semibold text-oassab-blue">Obrigatório</span>
                </label>
                <button type="button" data-remove-campo class="inline-flex items-center gap-1 text-sm font-semibold text-red-600 transition hover:text-red-700">
                    <i class="bi bi-trash"></i> Remover
                </button>
            </div>
        `;

        row.querySelector('[data-remove-campo]')?.addEventListener('click', () => {
            row.remove();
            updateEmptyState();
        });
        camposList.appendChild(row);
        updateEmptyState();
    };

    addCampoBtn.addEventListener('click', () => addCampoRow());
    camposIniciais.forEach((campo) => addCampoRow(campo));

    if (iconeInput && iconePreview) {
        iconeInput.addEventListener('input', () => {
            const value = iconeInput.value.trim() || 'bi-tag';
            iconePreview.innerHTML = `<i class="bi ${value.startsWith('bi-') ? value : `bi-${value}`}"></i>`;
        });
    }
}

export function initFormContent(container) {
    const form = container.querySelector('form[data-form-modal]');

    if (! form) {
        return;
    }

    if (form.dataset.patrimonioForm !== undefined) {
        initPatrimonioForm(form);
    }

    if (form.dataset.categoriaForm !== undefined) {
        initCategoriaForm(form);
    }
}
