const DEBOUNCE_MS = 300;

const debounce = (fn, wait) => {
    let timer = null;

    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), wait);
    };
};

document.querySelectorAll('[data-auto-submit]').forEach((element) => {
    element.addEventListener('change', () => {
        const form = element.closest('form');

        if (! form || form.hasAttribute('data-patrimonio-live-filters')) {
            return;
        }

        form.requestSubmit();
    });
});

const form = document.getElementById('patrimonios-filter-form');
const listEl = document.getElementById('patrimonios-list');
const searchInput = document.getElementById('patrimonio-busca');
const clearBtn = document.getElementById('patrimonio-busca-clear');

if (form && listEl && searchInput && clearBtn) {
    let activeController = null;

    const toggleClearButton = () => {
        const hasValue = searchInput.value.trim().length > 0;
        clearBtn.classList.toggle('hidden', ! hasValue);
        clearBtn.disabled = ! hasValue;
    };

    const buildParams = (pageUrl = null) => {
        const params = pageUrl
            ? new URL(pageUrl, window.location.origin).searchParams
            : new URLSearchParams(new FormData(form));

        params.delete('page');

        if (pageUrl) {
            const page = new URL(pageUrl, window.location.origin).searchParams.get('page');
            if (page) {
                params.set('page', page);
            }
        }

        return params;
    };

    const updateUrl = (params) => {
        const url = new URL(window.location.href);
        url.search = params.toString();
        history.replaceState({}, '', url);
    };

    const fetchList = async (pageUrl = null) => {
        const params = buildParams(pageUrl);
        const url = `${form.action || window.location.pathname}?${params.toString()}`;

        if (activeController) {
            activeController.abort();
        }

        activeController = new AbortController();
        listEl.classList.add('opacity-60', 'pointer-events-none');

        try {
            const response = await fetch(url, {
                signal: activeController.signal,
                headers: {
                    Accept: 'text/html',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-Patrimonio-List': '1',
                },
            });

            if (! response.ok) {
                throw new Error('Não foi possível carregar os patrimônios.');
            }

            listEl.innerHTML = await response.text();
            updateUrl(params);
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error(error);
            }
        } finally {
            listEl.classList.remove('opacity-60', 'pointer-events-none');
            activeController = null;
        }
    };

    const debouncedFetch = debounce(() => fetchList(), DEBOUNCE_MS);

    searchInput.addEventListener('input', () => {
        toggleClearButton();
        debouncedFetch();
    });

    clearBtn.addEventListener('click', () => {
        searchInput.value = '';
        toggleClearButton();
        searchInput.focus();
        fetchList();
    });

    form.querySelectorAll('[data-auto-submit]').forEach((select) => {
        select.addEventListener('change', () => fetchList());
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        fetchList();
    });

    listEl.addEventListener('click', (event) => {
        const link = event.target.closest('.bootstrap-pagination .page-link[href]');

        if (! link || ! listEl.contains(link)) {
            return;
        }

        event.preventDefault();
        fetchList(link.href);
    });

    toggleClearButton();
}
