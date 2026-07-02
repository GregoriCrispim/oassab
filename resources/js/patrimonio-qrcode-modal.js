const modal = document.getElementById('qrcode-modal');
if (modal) {
    const overlay = modal.querySelector('.qrcode-modal__overlay');
    const titleEl = document.getElementById('qrcode-modal-title');
    const subtitleEl = document.getElementById('qrcode-modal-subtitle');
    const gridEl = document.getElementById('qrcode-modal-grid');
    const loadingEl = document.getElementById('qrcode-modal-loading');
    const printBtn = document.getElementById('qrcode-modal-print');
    const printRoot = document.getElementById('qrcode-print-root');

    const settings = {
        size: document.getElementById('qrcode-setting-size'),
        gap: document.getElementById('qrcode-setting-gap'),
        cols: document.getElementById('qrcode-setting-cols'),
        label: document.getElementById('qrcode-setting-label'),
    };

    let state = {
        nome: '',
        qrcodeBase: '',
        codigos: [],
    };

    const getSize = () => parseInt(settings.size?.value || '180', 10);
    const getGap = () => parseInt(settings.gap?.value || '16', 10);
    const getLabelSize = () => parseInt(settings.label?.value || '14', 10);
    const getColumns = () => settings.cols?.value || 'auto';

    const qrcodeUrl = (codigo) => {
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

    const renderGrid = () => {
        applyGridStyles();
        gridEl.innerHTML = '';

        state.codigos.forEach((codigo) => {
            const card = document.createElement('div');
            card.className = 'qrcode-modal__item';

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
    };

    const openModal = async (url) => {
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
        loadingEl.classList.remove('hidden');
        gridEl.innerHTML = '';

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
                codigos: data.codigos || [],
            };

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
}
