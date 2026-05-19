@props(['document'])

<a href="{{ $document->file_path }}" target="_blank" rel="noopener"
   class="group flex flex-col gap-4 rounded-3xl border border-oassab-border bg-white p-8 shadow-sm transition hover:-translate-y-1 hover:border-oassab-orange/40 hover:shadow-xl md:flex-row md:items-center md:gap-6">
    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-oassab-orange text-white shadow-md">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" class="h-8 w-8" aria-hidden="true">
            <path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/>
            <path d="M14 3v6h6M9 14h6M9 18h6M9 10h2"/>
        </svg>
    </div>
    <div class="flex-1">
        @if ($document->year)
            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-oassab-orange">PDF · {{ $document->year }}</p>
        @else
            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-oassab-orange">PDF</p>
        @endif
        <h3 class="mt-2 font-heading text-xl font-bold text-oassab-blue transition group-hover:text-oassab-orange">
            {{ $document->title }}
        </h3>
        @if ($document->description)
            <p class="mt-1 text-sm text-oassab-gray">{{ $document->description }}</p>
        @endif
        @if ($document->processo || $document->valor_global)
            <dl class="mt-3 space-y-1 text-xs text-oassab-gray">
                @if ($document->processo)
                    <div><span class="font-semibold text-oassab-blue">Processo:</span> {{ $document->processo }}</div>
                @endif
                @if ($document->valor_global)
                    <div><span class="font-semibold text-oassab-blue">Valor global:</span> {{ $document->valor_global }}</div>
                @endif
            </dl>
        @endif
    </div>
    <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-oassab-blue transition group-hover:text-oassab-orange">
        Baixar
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 transition group-hover:translate-y-0.5" aria-hidden="true">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>
        </svg>
    </span>
</a>
