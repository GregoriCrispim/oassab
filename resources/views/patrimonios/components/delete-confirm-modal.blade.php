<div id="delete-confirm-modal" class="fixed inset-0 z-[60] hidden overflow-hidden overscroll-contain" aria-hidden="true">
    <div class="absolute inset-0 bg-oassab-blue-dark/60 backdrop-blur-sm" data-delete-modal-close></div>

    <div class="relative flex h-full max-h-[100dvh] items-center justify-center overflow-hidden p-4">
        <div
            role="alertdialog"
            aria-modal="true"
            aria-labelledby="delete-modal-title"
            aria-describedby="delete-modal-message"
            class="w-full max-w-md rounded-2xl border border-oassab-border bg-white p-6 shadow-2xl"
        >
            <div class="flex gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-100">
                    <i class="bi bi-exclamation-triangle text-xl text-red-600"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h2 id="delete-modal-title" class="font-heading text-lg font-bold text-oassab-blue">Excluir patrimônio</h2>
                    <p id="delete-modal-message" class="mt-2 text-sm leading-relaxed text-oassab-gray"></p>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap justify-end gap-3">
                <button
                    type="button"
                    class="rounded-lg border border-oassab-border bg-white px-4 py-2 text-sm font-semibold text-oassab-blue transition hover:bg-oassab-cream"
                    data-delete-modal-close
                >
                    Cancelar
                </button>
                <button
                    type="button"
                    id="delete-modal-confirm"
                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700"
                >
                    Excluir patrimônio
                </button>
            </div>
        </div>
    </div>
</div>
