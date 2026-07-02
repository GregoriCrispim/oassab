<div id="form-modal" class="fixed inset-0 z-50 hidden overflow-hidden overscroll-contain" aria-hidden="true">
    <div class="form-modal__overlay absolute inset-0 bg-oassab-blue-dark/60 backdrop-blur-sm" data-form-modal-close></div>

    <div class="relative flex h-full max-h-[100dvh] items-center justify-center overflow-hidden p-4 sm:p-6">
        <div
            role="dialog"
            aria-modal="true"
            aria-labelledby="form-modal-title"
            class="form-modal__dialog flex w-full max-w-5xl flex-col overflow-hidden rounded-2xl border border-oassab-border bg-white shadow-2xl"
        >
            <div class="flex shrink-0 items-start justify-between gap-4 border-b border-oassab-border px-6 py-4">
                <div>
                    <h2 id="form-modal-title" class="font-heading text-xl font-bold text-oassab-blue"></h2>
                </div>
                <button type="button" class="rounded-lg p-2 text-oassab-gray transition hover:bg-oassab-cream hover:text-oassab-blue" data-form-modal-close aria-label="Fechar">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div id="form-modal-loading" class="hidden shrink-0 px-6 py-16 text-center text-sm text-oassab-gray">
                <i class="bi bi-arrow-repeat animate-spin text-2xl"></i>
                <p class="mt-3">Carregando formulário...</p>
            </div>

            <div id="form-modal-errors" class="hidden shrink-0 border-b border-red-200 bg-red-50 px-6 py-3 text-sm text-red-800"></div>

            <div id="form-modal-body" class="min-h-0 flex-1 overflow-y-auto overscroll-contain bg-oassab-cream/40 px-6 py-5"></div>
        </div>
    </div>
</div>
