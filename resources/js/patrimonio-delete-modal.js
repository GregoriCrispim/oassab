const modal = document.getElementById('delete-confirm-modal');

if (modal) {
    const messageEl = document.getElementById('delete-modal-message');
    const confirmBtn = document.getElementById('delete-modal-confirm');
    let pendingForm = null;

    const escapeHtml = (value) => {
        const el = document.createElement('div');
        el.textContent = value;
        return el.innerHTML;
    };

    const openModal = (form, name, unidades) => {
        pendingForm = form;

        let message = `Tem certeza que deseja excluir o patrimônio <strong>${escapeHtml(name)}</strong>? Esta ação não pode ser desfeita.`;

        if (unidades > 1) {
            message += `<span class="mt-2 block text-xs text-oassab-gray">Serão removidas ${unidades} unidades de inventário.</span>`;
        }

        messageEl.innerHTML = message;
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
        confirmBtn?.focus();
    };

    const closeModal = () => {
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
        pendingForm = null;
    };

    document.addEventListener('click', (event) => {
        const button = event.target.closest('.js-open-delete-modal');

        if (! button) {
            return;
        }

        const form = button.closest('form');

        if (! form) {
            return;
        }

        openModal(
            form,
            button.dataset.name || '',
            parseInt(button.dataset.unidades || '1', 10),
        );
    });

    confirmBtn?.addEventListener('click', () => {
        if (pendingForm) {
            pendingForm.submit();
        }
    });

    modal.querySelectorAll('[data-delete-modal-close]').forEach((el) => {
        el.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && ! modal.classList.contains('hidden')) {
            closeModal();
        }
    });
}
