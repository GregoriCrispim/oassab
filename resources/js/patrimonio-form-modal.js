import { initFormContent } from './patrimonio-form-inits';

const modal = document.getElementById('form-modal');

if (modal) {
    const titleEl = document.getElementById('form-modal-title');
    const bodyEl = document.getElementById('form-modal-body');
    const loadingEl = document.getElementById('form-modal-loading');
    const errorsEl = document.getElementById('form-modal-errors');

    const hideErrors = () => {
        errorsEl.classList.add('hidden');
        errorsEl.innerHTML = '';
    };

    const showErrors = (errors) => {
        const messages = Object.values(errors || {}).flat();

        if (! messages.length) {
            hideErrors();

            return;
        }

        errorsEl.innerHTML = `<ul class="list-disc pl-5">${messages.map((msg) => `<li>${msg}</li>`).join('')}</ul>`;
        errorsEl.classList.remove('hidden');
    };

    const closeModal = () => {
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
        bodyEl.innerHTML = '';
        hideErrors();
    };

    const bindForm = (form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            hideErrors();

            const submitBtn = form.querySelector('[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
            }

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-Form-Modal': '1',
                    },
                });

                const data = await response.json().catch(() => ({}));

                if (response.status === 422) {
                    showErrors(data.errors || { form: [data.message || 'Verifique os campos do formulário.'] });

                    return;
                }

                if (! response.ok) {
                    showErrors({ form: [data.message || 'Não foi possível salvar. Tente novamente.'] });

                    return;
                }

                closeModal();

                if (data.redirect) {
                    window.location.assign(data.redirect);
                } else {
                    window.location.reload();
                }
            } catch {
                showErrors({ form: ['Erro de conexão. Tente novamente.'] });
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                }
            }
        });

        form.querySelectorAll('[data-form-modal-close]').forEach((el) => {
            el.addEventListener('click', closeModal);
        });
    };

    const openModal = async (url, title = '') => {
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
        loadingEl.classList.remove('hidden');
        bodyEl.innerHTML = '';
        hideErrors();
        titleEl.textContent = title || 'Carregando...';

        try {
            const response = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-Form-Modal': '1',
                },
            });

            if (! response.ok) {
                throw new Error('Não foi possível carregar o formulário.');
            }

            const data = await response.json();
            titleEl.textContent = data.title || title || 'Formulário';
            bodyEl.innerHTML = data.html;

            const form = bodyEl.querySelector('form[data-form-modal]');
            bodyEl.querySelectorAll('form[data-form-modal]').forEach((formEl) => {
                bindForm(formEl);
            });

            if (form) {
                initFormContent(bodyEl);
            }
        } catch (error) {
            titleEl.textContent = 'Erro';
            bodyEl.innerHTML = `<p class="py-8 text-center text-sm text-red-600">${error.message}</p>`;
        } finally {
            loadingEl.classList.add('hidden');
        }
    };

    document.addEventListener('click', (event) => {
        const button = event.target.closest('.js-open-form-modal');

        if (! button) {
            return;
        }

        const url = button.dataset.url;

        if (url) {
            openModal(url, button.dataset.title || '');
        }
    });

    modal.querySelectorAll('[data-form-modal-close]').forEach((el) => {
        el.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && ! modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    const params = new URLSearchParams(window.location.search);
    const modalUrl = params.get('modal');

    if (modalUrl) {
        const cleanUrl = new URL(window.location.href);
        cleanUrl.searchParams.delete('modal');
        history.replaceState({}, '', cleanUrl);

        openModal(decodeURIComponent(modalUrl));
    }
}
