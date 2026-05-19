@extends('layouts.app')

@section('title', 'Relatórios de Atividades')
@section('description', 'Faça o download dos relatórios de atividades da OASSAB.')

@section('content')
    <x-page-hero
        eyebrow="Documentos"
        title="Relatórios de Atividades"
        subtitle="Faça o download do relatório clicando no que deseja."
        image="/images/services-bg.jpg"
    />

    <section class="bg-white">
        <div class="container-oassab py-20 md:py-24">
            @php
                $reports = [
                    [
                        'title' => 'Relatório de Atividades 2024',
                        'subtitle' => 'Simplificado — ano 2023 (CAS)',
                        'file' => '/files/relatorio-de-atividades-2024.pdf',
                        'year' => '2024',
                    ],
                    [
                        'title' => 'Relatório de Atividades 2023',
                        'subtitle' => 'Ano 2022',
                        'file' => '/files/relatorio-de-atividades-2023.pdf',
                        'year' => '2023',
                    ],
                ];
            @endphp

            <div class="grid gap-6 md:grid-cols-2">
                @foreach ($reports as $report)
                    <a href="{{ $report['file'] }}" target="_blank" rel="noopener"
                       class="group flex flex-col gap-4 rounded-3xl border border-oassab-border bg-oassab-cream p-8 shadow-sm transition hover:-translate-y-1 hover:border-oassab-orange/40 hover:shadow-xl md:flex-row md:items-center md:gap-6">
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-oassab-orange text-white shadow-md">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" class="h-8 w-8" aria-hidden="true">
                                <path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/>
                                <path d="M14 3v6h6M9 14h6M9 18h6M9 10h2"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-oassab-orange">PDF · {{ $report['year'] }}</p>
                            <h3 class="mt-2 font-heading text-xl font-bold text-oassab-blue transition group-hover:text-oassab-orange">
                                {{ $report['title'] }}
                            </h3>
                            <p class="mt-1 text-sm text-oassab-gray">{{ $report['subtitle'] }}</p>
                        </div>
                        <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-oassab-blue transition group-hover:text-oassab-orange">
                            Baixar
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 transition group-hover:translate-y-0.5" aria-hidden="true">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>
                            </svg>
                        </span>
                    </a>
                @endforeach
            </div>

            <p class="mt-12 text-center text-sm text-oassab-gray">
                Os arquivos abrem em uma nova aba. Caso prefira, clique com o botão direito e escolha “Salvar link como...”.
            </p>
        </div>
    </section>

    <x-cta-section />
@endsection
