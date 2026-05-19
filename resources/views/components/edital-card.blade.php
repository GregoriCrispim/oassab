@props(['edital'])

<a href="{{ route('edital', $edital) }}"
   class="group flex h-full flex-col overflow-hidden rounded-2xl border border-oassab-border bg-white p-8 shadow-sm transition hover:-translate-y-1 hover:border-oassab-orange/40 hover:shadow-xl">
    <div class="flex items-start gap-4">
        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-oassab-orange text-white shadow-md">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" class="h-7 w-7" aria-hidden="true">
                <path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/>
                <path d="M14 3v6h6M9 14h6M9 18h6M9 10h2"/>
            </svg>
        </div>
        <div class="flex-1">
            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-oassab-orange">Edital · {{ $edital->formattedDate() }}</p>
            <h3 class="mt-2 font-heading text-xl font-bold text-oassab-blue transition group-hover:text-oassab-orange">
                {{ $edital->title }}
            </h3>
            @if ($edital->excerpt)
                <p class="mt-2 line-clamp-3 text-sm text-oassab-gray">{{ $edital->excerpt }}</p>
            @endif
            @php
                $pdfCount = ($edital->file_path ? 1 : 0) + ($edital->attachments_count ?? $edital->attachments?->count() ?? 0);
            @endphp
            @if ($pdfCount > 0)
                <p class="mt-3 text-xs font-semibold uppercase tracking-wider text-oassab-gray">
                    {{ $pdfCount }} {{ $pdfCount === 1 ? 'documento PDF' : 'documentos PDF' }}
                </p>
            @endif
        </div>
    </div>
    <span class="mt-6 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-oassab-orange">
        Ver edital
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 transition group-hover:translate-x-1" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
    </span>
</a>
