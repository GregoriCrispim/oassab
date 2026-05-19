document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('[data-mobile-toggle]');
    const nav = document.querySelector('[data-mobile-nav]');
    const iconOpen = document.querySelector('[data-mobile-icon-open]');
    const iconClose = document.querySelector('[data-mobile-icon-close]');

    if (toggle && nav) {
        toggle.addEventListener('click', () => {
            const isHidden = nav.hasAttribute('hidden');
            if (isHidden) {
                nav.removeAttribute('hidden');
                toggle.setAttribute('aria-expanded', 'true');
                iconOpen?.setAttribute('hidden', '');
                iconClose?.removeAttribute('hidden');
            } else {
                nav.setAttribute('hidden', '');
                toggle.setAttribute('aria-expanded', 'false');
                iconClose?.setAttribute('hidden', '');
                iconOpen?.removeAttribute('hidden');
            }
        });
    }

    document.querySelectorAll('[data-counter]').forEach((el) => {
        const target = parseInt(el.dataset.counter || '0', 10);
        const prefix = el.dataset.prefix || '';
        const suffix = el.dataset.suffix || '';
        if (Number.isNaN(target) || target === 0) {
            return;
        }
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                let current = 0;
                const stepCount = 40;
                const step = Math.max(1, Math.ceil(target / stepCount));
                const interval = setInterval(() => {
                    current += step;
                    if (current >= target) {
                        current = target;
                        clearInterval(interval);
                    }
                    el.textContent = prefix + current.toLocaleString('pt-BR') + suffix;
                }, 30);
                observer.unobserve(el);
            });
        }, { threshold: 0.4 });
        observer.observe(el);
    });
});
