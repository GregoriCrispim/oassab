@extends('layouts.app')

@section('title', 'Portal Transparência')
@section('description', 'Transparência e compromisso com cada recurso. Acompanhe como cada recurso é aplicado nos projetos da OASSAB.')

@section('content')
    <x-page-hero
        eyebrow="Portal Transparência"
        title="Transparência e compromisso com cada recurso"
        subtitle="Aqui você acompanha como cada recurso é aplicado nos projetos da OASSAB."
        image="/images/hero-bg.jpg"
    />

    <section class="bg-white">
        <div class="container-oassab grid items-center gap-10 py-16 md:grid-cols-[1fr,auto]">
            <div class="space-y-3">
                <h2 class="font-heading text-2xl font-bold text-oassab-blue md:text-3xl">Termos de Fomento e Parcerias</h2>
                <p class="max-w-2xl text-oassab-gray">
                    Listamos abaixo os principais projetos e parcerias firmados pela OASSAB com o Distrito Federal e demais órgãos públicos. Cada item conta com a descrição do objeto, valor global e processo correspondente.
                </p>
            </div>
            <a href="{{ url('/relatorios-de-atividades') }}" class="btn-primary shrink-0">
                Relatórios de Atividades
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
            </a>
        </div>
    </section>

    <section class="bg-oassab-cream">
        <div class="container-oassab py-16 md:py-20">
            @if ($documents->isEmpty())
                <p class="text-center text-sm text-oassab-gray">Nenhum documento publicado no momento.</p>
            @else
                <div class="grid gap-6">
                    @foreach ($documents as $document)
                        <x-transparency-document-card :document="$document" />
                    @endforeach
                </div>
                <p class="mt-12 text-center text-sm text-oassab-gray">
                    Os arquivos abrem em uma nova aba. Caso prefira, clique com o botão direito e escolha “Salvar link como...”.
                </p>
            @endif
        </div>
    </section>

    <x-cta-section />
@endsection
