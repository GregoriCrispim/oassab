document.addEventListener('DOMContentLoaded', () => {
    const drawer = document.getElementById('patrimonio-sidebar-drawer');
    const backdrop = document.getElementById('patrimonio-sidebar-backdrop');
    const toggle = document.querySelector('[data-patrimonio-sidebar-toggle]');

    if (!drawer || !backdrop || !toggle) {
        return;
    }

    const open = () => {
        drawer.classList.remove('-translate-x-full');
        backdrop.classList.remove('hidden');
        toggle.setAttribute('aria-expanded', 'true');
        document.body.classList.add('overflow-hidden');
    };

    const close = () => {
        drawer.classList.add('-translate-x-full');
        backdrop.classList.add('hidden');
        toggle.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('overflow-hidden');
    };

    const isOpen = () => !drawer.classList.contains('-translate-x-full');

    toggle.addEventListener('click', () => {
        if (isOpen()) {
            close();
        } else {
            open();
        }
    });

    backdrop.addEventListener('click', close);

    document.querySelectorAll('[data-patrimonio-sidebar-close]').forEach((el) => {
        el.addEventListener('click', close);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && isOpen()) {
            close();
        }
    });

    window.matchMedia('(min-width: 768px)').addEventListener('change', (event) => {
        if (event.matches) {
            close();
        }
    });
});
