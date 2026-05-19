@extends('layouts.app')

@section('title', 'Editais')
@section('description', 'Acompanhe os editais do Programa Edital Aberto da OASSAB — seleção de projetos sociais das entidades associadas.')

@section('content')
    <x-page-hero
        eyebrow="Programa Edital Aberto"
        title="Editais"
        subtitle="Confira os editais publicados, critérios, prazos e documentos para participação."
        image="/images/services-bg.jpg"
    />

    <section class="bg-white">
        <div class="container-oassab py-12">
            <a href="{{ route('projetos') }}"
               class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.25em] text-oassab-orange hover:text-oassab-blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                Voltar para Projetos
            </a>
        </div>
    </section>

    <section class="bg-oassab-cream">
        <div class="container-oassab py-16 md:py-20">
            @if ($editais->isEmpty())
                <p class="text-center text-sm text-oassab-gray">Nenhum edital publicado no momento.</p>
            @else
                <div class="grid gap-6 md:grid-cols-2">
                    @foreach ($editais as $edital)
                        <x-edital-card :edital="$edital" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <x-cta-section />
@endsection
