@extends('layouts.app')

@section('title', 'Contato')
@section('description', 'Estamos aqui para acolher, escutar e responder. Fale com a OASSAB pelos nossos canais de atendimento.')

@section('content')
    <x-page-hero
        eyebrow="Fale com a OASSAB"
        title="Estamos aqui para acolher, escutar e responder"
        subtitle="Se você quer saber mais, colaborar ou tirar dúvidas, fale com a gente."
        image="/images/cta-bg.jpg"
    />

    <section class="bg-white">
        <div class="container-oassab py-20 md:py-24">
            <div class="mx-auto max-w-2xl text-center">
                <span class="section-eyebrow">Nossos Contatos</span>
                <h2 class="section-title">Como nos encontrar</h2>
            </div>

            <div class="mt-14 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <article class="rounded-2xl border border-oassab-border bg-oassab-cream p-7 text-center shadow-sm">
                    <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-xl bg-oassab-blue/5 text-oassab-blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" class="h-7 w-7" aria-hidden="true">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                        </svg>
                    </div>
                    <h3 class="font-heading text-lg font-semibold uppercase tracking-wide text-oassab-blue">Endereço</h3>
                    <p class="mt-3 text-sm text-oassab-gray">SGAS 601 — Asa Sul<br>Brasília — DF</p>
                </article>

                <article class="rounded-2xl border border-oassab-border bg-oassab-cream p-7 text-center shadow-sm">
                    <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-xl bg-oassab-blue/5 text-oassab-blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" class="h-7 w-7" aria-hidden="true">
                            <path d="M22 16.92V21a1 1 0 0 1-1.1 1 19.9 19.9 0 0 1-8.6-3.07 19.5 19.5 0 0 1-6-6A19.9 19.9 0 0 1 3.07 4.1 1 1 0 0 1 4 3h4a1 1 0 0 1 1 .76l1 4a1 1 0 0 1-.27.97l-2 2a16 16 0 0 0 6 6l2-2a1 1 0 0 1 .97-.27l4 1a1 1 0 0 1 .76 1z"/>
                        </svg>
                    </div>
                    <h3 class="font-heading text-lg font-semibold uppercase tracking-wide text-oassab-blue">Telefones</h3>
                    <p class="mt-3 space-y-1 text-sm text-oassab-gray">
                        <a class="block hover:text-oassab-orange" href="tel:+556120992218">(61) 2099-2218</a>
                        <a class="block hover:text-oassab-orange" href="tel:+556120992219">(61) 2099-2219</a>
                    </p>
                </article>

                <article class="rounded-2xl border border-oassab-border bg-oassab-cream p-7 text-center shadow-sm">
                    <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-xl bg-oassab-blue/5 text-oassab-blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" class="h-7 w-7" aria-hidden="true">
                            <rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>
                        </svg>
                    </div>
                    <h3 class="font-heading text-lg font-semibold uppercase tracking-wide text-oassab-blue">E-mail</h3>
                    <p class="mt-3 text-sm">
                        <a class="break-all text-oassab-gray hover:text-oassab-orange" href="mailto:contato@oassab.org.br">contato@oassab.org.br</a>
                    </p>
                </article>

                <article class="rounded-2xl border border-oassab-border bg-oassab-cream p-7 text-center shadow-sm">
                    <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-xl bg-oassab-blue/5 text-oassab-blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" class="h-7 w-7" aria-hidden="true">
                            <rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5h.01"/>
                        </svg>
                    </div>
                    <h3 class="font-heading text-lg font-semibold uppercase tracking-wide text-oassab-blue">Redes sociais</h3>
                    <p class="mt-3 space-y-1 text-sm">
                        <a class="block text-oassab-gray hover:text-oassab-orange" href="https://www.instagram.com/oassabdf/" target="_blank" rel="noopener">@oassabdf</a>
                        <a class="block text-oassab-gray hover:text-oassab-orange" href="https://wa.me/556132238431" target="_blank" rel="noopener">WhatsApp</a>
                    </p>
                </article>
            </div>

            <div class="mt-16 grid gap-10 rounded-3xl bg-oassab-blue p-8 text-white shadow-xl md:grid-cols-[1.2fr,1fr] md:p-12">
                <div class="space-y-5">
                    <h3 class="font-heading text-2xl font-bold leading-tight md:text-3xl">
                        Quer colaborar com a missão da OASSAB?
                    </h3>
                    <p class="text-white/80">
                        Doe tempo, recursos ou ideias. Toda ajuda importa para que possamos seguir promovendo cidadania, qualificação profissional e dignidade humana no DF.
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <a href="https://wa.me/556132238431" target="_blank" rel="noopener" class="btn-primary">
                            Fale no WhatsApp
                        </a>
                        <a href="mailto:contato@oassab.org.br" class="btn-outline">
                            Enviar e-mail
                        </a>
                    </div>
                </div>

                <div class="rounded-2xl bg-white/10 p-6 text-sm text-white/85">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-[0.25em] text-oassab-orange">Atendimento</p>
                    <p class="mb-2"><strong class="text-white">Segunda a sexta-feira</strong></p>
                    <p>Das 09h às 17h, na sede da OASSAB.</p>
                    <p class="mt-4">Atendimento social com agendamento prévio pelos telefones (61) 2099-2218 / 2099-2219.</p>
                </div>
            </div>
        </div>
    </section>

    <x-cta-section />
@endsection
