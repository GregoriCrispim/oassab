@extends('layouts.app')

@section('title', $edital->title)
@section('description', $edital->excerpt ?: $edital->title)

@section('content')
    <article>
        <section class="relative isolate overflow-hidden bg-oassab-blue-dark text-white">
            <div class="absolute inset-0 -z-10 bg-oassab-blue-dark">
                <img src="/images/services-bg.jpg" alt="" class="h-full w-full object-cover opacity-30" loading="lazy">
                <div class="absolute inset-0 hero-overlay"></div>
            </div>
            <div class="container-oassab py-20 md:py-28">
                <div class="max-w-3xl space-y-5">
                    <a href="{{ route('editais') }}"
                       class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.25em] text-oassab-orange hover:text-white">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                        Voltar para Editais
                    </a>
                    <span class="inline-block rounded-full bg-white/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.25em] text-white/80">
                        {{ $edital->formattedDate() }}
                    </span>
                    <h1 class="font-heading text-3xl font-bold leading-tight text-white sm:text-4xl md:text-5xl">
                        {{ $edital->title }}
                    </h1>
                    @if ($edital->excerpt)
                        <p class="text-lg text-white/85">{{ $edital->excerpt }}</p>
                    @endif
                </div>
            </div>
        </section>

        <div class="bg-white">
            <div class="container-oassab grid gap-10 py-16 lg:grid-cols-[2fr,1fr] lg:py-20">
                <div class="prose-oassab max-w-3xl text-base text-oassab-gray md:text-lg">
                    @if (! empty($edital->body))
                        {!! $edital->body !!}
                    @elseif (! empty($edital->excerpt))
                        <p>{{ $edital->excerpt }}</p>
                    @else
                        <p>Conteúdo em breve.</p>
                    @endif
                </div>

                <aside class="space-y-6">
                    @if ($edital->file_path)
                        <div class="rounded-3xl border border-oassab-border bg-oassab-cream p-6">
                            <h3 class="mb-3 text-xs font-semibold uppercase tracking-[0.25em] text-oassab-orange">Edital (PDF)</h3>
                            <a href="{{ $edital->file_path }}" target="_blank" rel="noopener"
                               class="group flex items-center gap-4 rounded-2xl border border-oassab-border bg-white p-4 transition hover:border-oassab-orange/40 hover:shadow-md">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-oassab-orange text-white">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-6 w-6" aria-hidden="true">
                                        <path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><path d="M14 3v6h6"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="truncate text-sm font-semibold text-oassab-blue group-hover:text-oassab-orange">
                                        {{ $edital->original_filename ?: 'Baixar edital' }}
                                    </p>
                                    <p class="text-xs text-oassab-gray">Documento principal</p>
                                </div>
                            </a>
                        </div>
                    @endif

                    @if ($edital->attachments->isNotEmpty())
                        <div class="rounded-3xl border border-oassab-border bg-oassab-cream p-6">
                            <h3 class="mb-3 text-xs font-semibold uppercase tracking-[0.25em] text-oassab-orange">Anexos</h3>
                            <ul class="space-y-3">
                                @foreach ($edital->attachments as $attachment)
                                    <li>
                                        <a href="{{ $attachment->file_path }}" target="_blank" rel="noopener"
                                           class="group flex items-center gap-3 rounded-2xl border border-oassab-border bg-white p-3 transition hover:border-oassab-orange/40 hover:shadow-sm">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-oassab-blue/10 text-oassab-blue">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true">
                                                    <path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><path d="M14 3v6h6"/>
                                                </svg>
                                            </div>
                                            <span class="flex-1 text-sm font-medium text-oassab-blue group-hover:text-oassab-orange">{{ $attachment->title }}</span>
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4 shrink-0 text-oassab-gray" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>
                                            </svg>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="rounded-3xl border border-oassab-border bg-oassab-cream p-6">
                        <h3 class="mb-3 text-xs font-semibold uppercase tracking-[0.25em] text-oassab-orange">Dúvidas?</h3>
                        <p class="text-sm text-oassab-gray">Entre em contato com a OASSAB pelo WhatsApp.</p>
                        <a href="https://wa.me/556132238431" target="_blank" rel="noopener" class="btn-primary mt-4 w-full justify-center">
                            Fale com a gente
                        </a>
                    </div>
                </aside>
            </div>
        </div>

        <nav class="bg-oassab-cream" aria-label="Navegação entre editais">
            <div class="container-oassab grid gap-4 py-10 md:grid-cols-2">
                @if ($previous)
                    <a href="{{ route('edital', $previous) }}"
                       class="group flex flex-col gap-2 rounded-2xl border border-oassab-border bg-white p-5 transition hover:-translate-y-0.5 hover:border-oassab-orange/40 hover:shadow-md">
                        <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.25em] text-oassab-orange">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                            Edital anterior
                        </span>
                        <span class="font-heading text-base font-semibold text-oassab-blue group-hover:text-oassab-orange">{{ $previous->title }}</span>
                    </a>
                @else
                    <span></span>
                @endif

                @if ($next)
                    <a href="{{ route('edital', $next) }}"
                       class="group flex flex-col gap-2 rounded-2xl border border-oassab-border bg-white p-5 text-right transition hover:-translate-y-0.5 hover:border-oassab-orange/40 hover:shadow-md md:items-end">
                        <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.25em] text-oassab-orange">
                            Próximo edital
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                        </span>
                        <span class="font-heading text-base font-semibold text-oassab-blue group-hover:text-oassab-orange">{{ $next->title }}</span>
                    </a>
                @endif
            </div>
        </nav>
    </article>

    <x-cta-section />
@endsection
