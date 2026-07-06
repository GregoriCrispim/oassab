<div id="qrcode-modal" class="fixed inset-0 z-50 hidden overflow-hidden overscroll-contain" aria-hidden="true">
    <div class="qrcode-modal__overlay absolute inset-0 bg-oassab-blue-dark/60 backdrop-blur-sm" data-qrcode-close></div>

    <div class="relative flex h-full max-h-[100dvh] items-center justify-center overflow-hidden p-4 sm:p-6">
        <div
            role="dialog"
            aria-modal="true"
            aria-labelledby="qrcode-modal-title"
            class="qrcode-modal__dialog flex w-full max-w-6xl flex-col overflow-hidden rounded-2xl border border-oassab-border bg-white shadow-2xl"
        >
            <div class="flex shrink-0 items-start justify-between gap-4 border-b border-oassab-border px-6 py-4">
                <div>
                    <h2 id="qrcode-modal-title" class="font-heading text-xl font-bold text-oassab-blue">QR Codes</h2>
                    <p id="qrcode-modal-subtitle" class="mt-1 text-sm text-oassab-gray"></p>
                </div>
                <button type="button" class="rounded-lg p-2 text-oassab-gray transition hover:bg-oassab-cream hover:text-oassab-blue" data-qrcode-close aria-label="Fechar">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="flex min-h-0 flex-1 flex-col overflow-y-auto overscroll-contain lg:flex-row lg:overflow-hidden">
                <aside class="shrink-0 border-b border-oassab-border bg-oassab-cream/40 p-5 lg:w-72 lg:max-h-full lg:overflow-y-auto lg:border-b-0 lg:border-r">
                    <p class="mb-4 text-xs font-semibold uppercase tracking-wider text-oassab-gray">Impressão</p>

                    <div class="space-y-4">
                        <div>
                            <label for="qrcode-setting-size" class="mb-1 block text-sm font-semibold text-oassab-blue">Tamanho do QR</label>
                            <select id="qrcode-setting-size" class="w-full rounded-lg border border-oassab-border px-3 py-2 text-sm">
                                <option value="140">Pequeno (140 px)</option>
                                <option value="180" selected>Médio (180 px)</option>
                                <option value="220">Grande (220 px)</option>
                                <option value="280">Extra (280 px)</option>
                            </select>
                        </div>

                        <div>
                            <label for="qrcode-setting-gap" class="mb-1 block text-sm font-semibold text-oassab-blue">Espaçamento</label>
                            <select id="qrcode-setting-gap" class="w-full rounded-lg border border-oassab-border px-3 py-2 text-sm">
                                <option value="8">Compacto</option>
                                <option value="16" selected>Normal</option>
                                <option value="24">Amplo</option>
                                <option value="32">Muito amplo</option>
                            </select>
                        </div>

                        <div>
                            <label for="qrcode-setting-cols" class="mb-1 block text-sm font-semibold text-oassab-blue">Colunas</label>
                            <select id="qrcode-setting-cols" class="w-full rounded-lg border border-oassab-border px-3 py-2 text-sm">
                                <option value="auto" selected>Automático</option>
                                <option value="2">2 colunas</option>
                                <option value="3">3 colunas</option>
                                <option value="4">4 colunas</option>
                                <option value="5">5 colunas</option>
                            </select>
                        </div>

                        <div>
                            <label for="qrcode-setting-label" class="mb-1 block text-sm font-semibold text-oassab-blue">Tamanho do código</label>
                            <select id="qrcode-setting-label" class="w-full rounded-lg border border-oassab-border px-3 py-2 text-sm">
                                <option value="12">Pequeno</option>
                                <option value="14" selected>Normal</option>
                                <option value="16">Grande</option>
                            </select>
                        </div>
                    </div>
                </aside>

                <div class="min-h-0 flex-1 p-5 lg:overflow-y-auto lg:overscroll-contain">
                    <div id="qrcode-modal-loading" class="hidden py-16 text-center text-sm text-oassab-gray">
                        <i class="bi bi-arrow-repeat animate-spin text-2xl"></i>
                        <p class="mt-3">Carregando QR codes...</p>
                    </div>
                    <div id="qrcode-modal-grid" class="qrcode-modal__grid"></div>
                </div>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-3 border-t border-oassab-border px-6 py-4">
                <label id="qrcode-select-all-wrap" class="hidden items-center gap-2 text-sm text-oassab-blue">
                    <input type="checkbox" id="qrcode-select-all" class="h-4 w-4 rounded border-oassab-border text-oassab-blue focus:ring-oassab-blue">
                    Selecionar todos
                </label>
                <span id="qrcode-modal-status" class="text-sm text-oassab-gray" role="status" aria-live="polite"></span>

                <div class="ml-auto flex flex-wrap items-center gap-3">
                    <button type="button" class="rounded-lg border border-oassab-border px-4 py-2 text-sm font-semibold text-oassab-blue transition hover:bg-oassab-cream" data-qrcode-close>
                        Fechar
                    </button>
                    <button type="button" id="qrcode-modal-regenerate" class="hidden rounded-lg border border-oassab-orange px-4 py-2 text-sm font-semibold text-oassab-orange transition hover:bg-oassab-orange/10">
                        <i class="bi bi-arrow-repeat"></i> <span data-regenerate-label>Regenerar todos</span>
                    </button>
                    <button type="button" id="qrcode-modal-print" class="btn-blue">
                        <i class="bi bi-printer"></i> Imprimir
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="qrcode-print-root" class="hidden"></div>
