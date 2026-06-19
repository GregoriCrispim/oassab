@extends('layouts.app')

@php
    $primary = $post->primaryCategorySlug();
    $primaryLabels = [
        'noticias' => ['label' => 'Notícias', 'url' => url('/noticias')],
        'projetos' => ['label' => 'Projetos', 'url' => url('/projetos')],
        'transparencia' => ['label' => 'Transparência', 'url' => url('/transparencia')],
    ];
    $primaryInfo = $primaryLabels[$primary] ?? $primaryLabels['noticias'];
    $heroSet = $post->imageSet();
@endphp

@section('title', $post->title)
@section('description', $post->excerpt ?: $post->title)

@section('content')
    <article>
        <section class="relative isolate overflow-hidden bg-oassab-blue-dark text-white">
            <div class="absolute inset-0 -z-10">
                <x-responsive-image
                    :set="$heroSet"
                    alt=""
                    sizes="100vw"
                    class="h-full w-full object-cover opacity-40"
                    :priority="true"
                    fallback="/images/hero-bg.jpg"
                />
                <div class="absolute inset-0 hero-overlay"></div>
            </div>
            <div class="container-oassab py-20 md:py-28">
                <div class="max-w-3xl space-y-5">
                    <a href="{{ $primaryInfo['url'] }}"
                       class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.25em] text-oassab-orange hover:text-white">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                        Voltar para {{ $primaryInfo['label'] }}
                    </a>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($post->categories as $cat)
                            <span class="inline-block rounded-full bg-oassab-orange/15 px-4 py-1 text-xs font-semibold uppercase tracking-[0.25em] text-oassab-orange">
                                {{ $cat->name }}
                            </span>
                        @endforeach
                        <span class="inline-block rounded-full bg-white/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.25em] text-white/80">
                            {{ $post->formattedDate() }}
                        </span>
                    </div>
                    <h1 class="font-heading text-3xl font-bold leading-tight text-white sm:text-4xl md:text-5xl">
                        {{ $post->title }}
                    </h1>
                </div>
            </div>
        </section>

        <div class="bg-white">
            <div class="container-oassab grid gap-10 py-16 lg:grid-cols-[2fr,1fr] lg:py-20">
                <div class="prose-oassab max-w-3xl text-base text-oassab-gray md:text-lg">
                    @if (! empty($post->body))
                        {!! $post->body !!}
                    @elseif (! empty($post->excerpt))
                        <p>{{ $post->excerpt }}</p>
                    @else
                        <p>Conteúdo em breve.</p>
                    @endif
                </div>

                <aside class="space-y-6">
                    @if ($post->hasAttachment())
                        <div class="rounded-3xl border border-oassab-border bg-oassab-cream p-6">
                            <h3 class="mb-3 text-xs font-semibold uppercase tracking-[0.25em] text-oassab-orange">Documento</h3>
                            <a href="{{ $post->attachmentUrl() }}"
                               class="group flex items-center gap-4 rounded-2xl border border-oassab-border bg-white p-4 transition hover:border-oassab-orange/40 hover:shadow-md">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-oassab-orange text-white">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-6 w-6" aria-hidden="true">
                                        <path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><path d="M14 3v6h6"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="truncate text-sm font-semibold text-oassab-blue group-hover:text-oassab-orange">
                                        {{ $post->attachment_original_filename ?: 'Baixar resultado' }}
                                    </p>
                                    <p class="text-xs text-oassab-gray">Clique para baixar</p>
                                </div>
                            </a>
                        </div>
                    @elseif ($post->edital)
                        <div class="rounded-3xl border border-oassab-border bg-oassab-cream p-6">
                            <h3 class="mb-3 text-xs font-semibold uppercase tracking-[0.25em] text-oassab-orange">Edital</h3>
                            <a href="{{ route('edital', $post->edital) }}"
                               class="group flex items-center gap-4 rounded-2xl border border-oassab-border bg-white p-4 transition hover:border-oassab-orange/40 hover:shadow-md">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-oassab-blue text-white">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-6 w-6" aria-hidden="true">
                                        <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-oassab-blue group-hover:text-oassab-orange">Ver edital</p>
                                    <p class="truncate text-xs text-oassab-gray">{{ $post->edital->title }}</p>
                                </div>
                            </a>
                        </div>
                    @endif

                    <div class="rounded-3xl border border-oassab-border bg-oassab-cream p-6">
                        <h3 class="mb-3 text-xs font-semibold uppercase tracking-[0.25em] text-oassab-orange">Compartilhar</h3>
                        <div class="flex flex-wrap gap-3">
                            <a href="https://wa.me/?text={{ urlencode($post->title.' — '.url()->current()) }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-2 rounded-full bg-oassab-blue px-4 py-2 text-xs font-semibold uppercase tracking-wider text-white transition hover:bg-oassab-orange">
                                WhatsApp
                            </a>
                            <a href="mailto:?subject={{ urlencode($post->title) }}&body={{ urlencode(url()->current()) }}"
                               class="inline-flex items-center gap-2 rounded-full border border-oassab-blue/20 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-oassab-blue transition hover:border-oassab-orange hover:bg-oassab-orange hover:text-white">
                                E-mail
                            </a>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-oassab-border bg-oassab-cream p-6">
                        <h3 class="mb-3 text-xs font-semibold uppercase tracking-[0.25em] text-oassab-orange">Quer apoiar?</h3>
                        <p class="text-sm text-oassab-gray">
                            Entre em contato com a OASSAB pelo WhatsApp ou conheça as nossas ações nas redes sociais.
                        </p>
                        <a href="https://wa.me/556132238431" target="_blank" rel="noopener" class="btn-primary mt-4 w-full justify-center">
                            Fale com a gente
                        </a>
                    </div>
                </aside>
            </div>
        </div>

        <nav class="bg-oassab-cream" aria-label="Navegação entre publicações">
            <div class="container-oassab grid gap-4 py-10 md:grid-cols-2">
                @if ($previous)
                    <a href="{{ route('post', ['slug' => $previous->slug]) }}"
                       class="group flex flex-col gap-2 rounded-2xl border border-oassab-border bg-white p-5 transition hover:-translate-y-0.5 hover:border-oassab-orange/40 hover:shadow-md">
                        <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.25em] text-oassab-orange">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                            Publicação anterior
                        </span>
                        <span class="font-heading text-base font-semibold text-oassab-blue group-hover:text-oassab-orange">{{ $previous->title }}</span>
                    </a>
                @else
                    <span></span>
                @endif

                @if ($next)
                    <a href="{{ route('post', ['slug' => $next->slug]) }}"
                       class="group flex flex-col gap-2 rounded-2xl border border-oassab-border bg-white p-5 text-right transition hover:-translate-y-0.5 hover:border-oassab-orange/40 hover:shadow-md md:items-end">
                        <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.25em] text-oassab-orange">
                            Próxima publicação
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
