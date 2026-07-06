const modal = document.getElementById('qrcode-modal');
if (modal) {
    const overlay = modal.querySelector('.qrcode-modal__overlay');
    const titleEl = document.getElementById('qrcode-modal-title');
    const subtitleEl = document.getElementById('qrcode-modal-subtitle');
    const gridEl = document.getElementById('qrcode-modal-grid');
    const loadingEl = document.getElementById('qrcode-modal-loading');
    const printBtn = document.getElementById('qrcode-modal-print');
    const printRoot = document.getElementById('qrcode-print-root');
    const regenerateBtn = document.getElementById('qrcode-modal-regenerate');
    const regenerateLabel = regenerateBtn?.querySelector('[data-regenerate-label]');
    const selectAllWrap = document.getElementById('qrcode-select-all-wrap');
    const selectAllInput = document.getElementById('qrcode-select-all');
    const statusEl = document.getElementById('qrcode-modal-status');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    const settings = {
        size: document.getElementById('qrcode-setting-size'),
        gap: document.getElementById('qrcode-setting-gap'),
        cols: document.getElementById('qrcode-setting-cols'),
        label: document.getElementById('qrcode-setting-label'),
    };

    let state = {
        nome: '',
        qrcodeBase: '',
        qrcodes: {},
        codigos: [],
        regenerarUrl: '',
        podeRegenerar: false,
    };

    const selecionados = new Set();
    let regenerando = false;

    const setStatus = (msg = '', tipo = 'info') => {
        if (! statusEl) {
            return;
        }
        statusEl.textContent = msg;
        statusEl.className = tipo === 'erro'
            ? 'text-sm text-red-600'
            : (tipo === 'ok' ? 'text-sm text-green-600' : 'text-sm text-oassab-gray');
    };

    const getSize = () => parseInt(settings.size?.value || '180', 10);
    const getGap = () => parseInt(settings.gap?.value || '16', 10);
    const getLabelSize = () => parseInt(settings.label?.value || '14', 10);
    const getColumns = () => settings.cols?.value || 'auto';

    const qrcodeUrl = (codigo) => {
        if (state.qrcodes[codigo]) {
            return state.qrcodes[codigo];
        }

        const params = new URLSearchParams({
            codigo,
            size: String(getSize()),
        });

        return `${state.qrcodeBase}?${params.toString()}`;
    };

    const applyGridStyles = () => {
        const gap = `${getGap()}px`;
        const labelSize = `${getLabelSize()}px`;
        const cols = getColumns();

        gridEl.style.setProperty('--qr-gap', gap);
        gridEl.style.setProperty('--qr-label-size', labelSize);
        gridEl.style.gridTemplateColumns = cols === 'auto'
            ? `repeat(auto-fill, minmax(${getSize() + 28}px, 1fr))`
            : `repeat(${cols}, minmax(0, 1fr))`;
    };

    const updateRegenerateLabel = () => {
        if (! regenerateLabel) {
            return;
        }
        regenerateLabel.textContent = selecionados.size > 0
            ? `Regenerar selecionados (${selecionados.size})`
            : 'Regenerar todos';
    };

    const syncSelectAll = () => {
        if (! selectAllInput) {
            return;
        }
        const total = state.codigos.length;
        selectAllInput.checked = total > 0 && selecionados.size === total;
        selectAllInput.indeterminate = selecionados.size > 0 && selecionados.size < total;
    };

    const renderGrid = () => {
        applyGridStyles();
        gridEl.innerHTML = '';

        state.codigos.forEach((codigo) => {
            const card = document.createElement('div');
            card.className = 'qrcode-modal__item';

            if (state.podeRegenerar) {
                const check = document.createElement('label');
                check.className = 'qrcode-modal__select';
                const input = document.createElement('input');
                input.type = 'checkbox';
                input.className = 'js-qrcode-select h-4 w-4 rounded border-oassab-border text-oassab-blue focus:ring-oassab-blue';
                input.value = codigo;
                input.checked = selecionados.has(codigo);
                input.addEventListener('change', () => {
                    if (input.checked) {
                        selecionados.add(codigo);
                    } else {
                        selecionados.delete(codigo);
                    }
                    updateRegenerateLabel();
                    syncSelectAll();
                });
                check.appendChild(input);
                card.appendChild(check);
            }

            const label = document.createElement('p');
            label.className = 'qrcode-modal__code';
            label.textContent = codigo;

            const imageWrap = document.createElement('div');
            imageWrap.className = 'qrcode-modal__image';

            const img = document.createElement('img');
            img.src = qrcodeUrl(codigo);
            img.alt = `QR Code ${codigo}`;
            img.width = getSize();
            img.height = getSize();
            img.loading = 'lazy';

            imageWrap.appendChild(img);
            card.append(label, imageWrap);
            gridEl.appendChild(card);
        });

        updateRegenerateLabel();
        syncSelectAll();
    };

    const regenerate = async () => {
        if (regenerando || ! state.regenerarUrl) {
            return;
        }

        const alvo = selecionados.size > 0 ? [...selecionados] : [...state.codigos];

        regenerando = true;
        regenerateBtn?.setAttribute('disabled', 'disabled');
        setStatus('Regenerando QR codes...');

        try {
            const response = await fetch(state.regenerarUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ codigos: alvo }),
            });

            if (! response.ok) {
                throw new Error('Falha ao regenerar.');
            }

            const data = await response.json();
            Object.entries(data.qrcodes || {}).forEach(([codigo, url]) => {
                state.qrcodes[codigo] = url;
            });

            renderGrid();

            const total = (data.regerados || []).length;
            const falhas = (data.falhas || []).length;
            setStatus(
                falhas > 0
                    ? `${total} regenerado(s), ${falhas} com falha (usando geração dinâmica).`
                    : `${total} QR code(s) regenerado(s).`,
                falhas > 0 ? 'erro' : 'ok',
            );
        } catch (error) {
            setStatus('Não foi possível regenerar os QR codes.', 'erro');
        } finally {
            regenerando = false;
            regenerateBtn?.removeAttribute('disabled');
        }
    };

    const openModal = async (url) => {
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
        loadingEl.classList.remove('hidden');
        gridEl.innerHTML = '';
        selecionados.clear();
        setStatus('');

        try {
            const response = await fetch(url, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (! response.ok) {
                throw new Error('Não foi possível carregar os QR codes.');
            }

            const data = await response.json();
            state = {
                nome: data.nome,
                qrcodeBase: data.qrcode_base,
                qrcodes: data.qrcodes || {},
                codigos: data.codigos || [],
                regenerarUrl: data.regenerar_url || '',
                podeRegenerar: Boolean(data.pode_regenerar),
            };

            const mostrarControles = state.podeRegenerar && state.codigos.length > 0;
            regenerateBtn?.classList.toggle('hidden', ! mostrarControles);
            selectAllWrap?.classList.toggle('hidden', ! mostrarControles);
            selectAllWrap?.classList.toggle('flex', mostrarControles);

            titleEl.textContent = data.nome;
            subtitleEl.textContent = `${data.codigos.length} QR code(s) de inventário`;
            renderGrid();
        } catch (error) {
            titleEl.textContent = 'Erro';
            subtitleEl.textContent = error.message;
        } finally {
            loadingEl.classList.add('hidden');
        }
    };

    const closeModal = () => {
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
    };

    const printQrcodes = () => {
        const gap = getGap();
        const labelSize = getLabelSize();
        const cols = getColumns();
        const size = getSize();
        const columnsCss = cols === 'auto'
            ? `repeat(auto-fill, minmax(${size + 28}px, 1fr))`
            : `repeat(${cols}, minmax(0, 1fr))`;

        const items = state.codigos.map((codigo) => `
            <div class="print-item">
                <p class="print-code">${codigo}</p>
                <img src="${qrcodeUrl(codigo)}" alt="${codigo}" width="${size}" height="${size}">
            </div>
        `).join('');

        printRoot.innerHTML = `
            <div class="print-sheet">
                <h1>${state.nome}</h1>
                <p class="print-meta">${state.codigos.length} QR code(s)</p>
                <div class="print-grid">${items}</div>
            </div>
        `;

        const style = document.createElement('style');
        style.textContent = `
            @media print {
                body * { visibility: hidden !important; }
                #qrcode-print-root, #qrcode-print-root * { visibility: visible !important; }
                #qrcode-print-root {
                    display: block !important;
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 100%;
                }
            }
            @media print {
                .print-sheet { padding: 12mm; font-family: Jost, sans-serif; color: #1f2754; }
                .print-sheet h1 { font-size: 18px; margin: 0 0 4px; }
                .print-meta { font-size: 12px; color: #4f5366; margin: 0 0 16px; }
                .print-grid {
                    display: grid;
                    grid-template-columns: ${columnsCss};
                    gap: ${gap}px;
                }
                .print-item {
                    break-inside: avoid;
                    page-break-inside: avoid;
                    text-align: center;
                    border: 1px solid #efefef;
                    border-radius: 4px;
                    padding: 4px;
                }
                .print-code {
                    font-family: monospace;
                    font-size: ${labelSize}px;
                    font-weight: 600;
                    margin: 0 0 2px;
                    color: #1f2754;
                    line-height: 1.1;
                }
                .print-item img { display: block; margin: 0 auto; line-height: 0; }
            }
        `;

        printRoot.appendChild(style);
        window.print();

        setTimeout(() => {
            printRoot.innerHTML = '';
        }, 500);
    };

    document.addEventListener('click', (event) => {
        const button = event.target.closest('.js-open-qrcode-modal');

        if (! button) {
            return;
        }

        const url = button.dataset.url;

        if (url) {
            openModal(url);
        }
    });

    modal.querySelectorAll('[data-qrcode-close]').forEach((el) => {
        el.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && ! modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    Object.values(settings).forEach((input) => {
        input?.addEventListener('change', () => {
            if (state.codigos.length) {
                renderGrid();
            }
        });
    });

    printBtn?.addEventListener('click', printQrcodes);
    regenerateBtn?.addEventListener('click', regenerate);

    selectAllInput?.addEventListener('change', () => {
        selecionados.clear();
        if (selectAllInput.checked) {
            state.codigos.forEach((codigo) => selecionados.add(codigo));
        }
        renderGrid();
    });
}
