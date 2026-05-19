@extends('layouts.app')

@section('title', 'OASSAB — Obras de Assistência e de Serviço Social da Arquidiocese de Brasília')
@section('description', 'A OASSAB reúne 166 paróquias e 22 entidades associadas, promovendo ações sociais, cursos de capacitação e apoio direto a milhares de famílias em situação de vulnerabilidade no Distrito Federal.')

@push('head')
    @if (file_exists(public_path('images/hero-bg-1200.webp')))
        <link rel="preload" as="image"
              href="/images/hero-bg.jpg"
              imagesrcset="/images/hero-bg-800.webp 800w, /images/hero-bg-1200.webp 1200w, /images/hero-bg-1600.webp 1600w"
              imagesizes="100vw"
              type="image/webp"
              fetchpriority="high">
    @else
        <link rel="preload" as="image" href="/images/hero-bg.jpg" fetchpriority="high">
    @endif
@endpush

@section('content')
    <section class="relative isolate overflow-hidden bg-oassab-blue-dark text-white">
        <div class="absolute inset-0 -z-10">
            <x-static-picture
                src="/images/hero-bg.jpg"
                :widths="[800, 1200, 1600]"
                alt=""
                sizes="100vw"
                class="h-full w-full object-cover"
                :priority="true"
                :width="1920"
                :height="1080"
                imgAttrs='aria-hidden="true"'
            />
            <div class="absolute inset-0 hero-overlay"></div>
        </div>
        <div class="container-oassab relative py-24 md:py-32 lg:py-40">
            <div class="max-w-3xl animate-fade-in-up">
                <span class="mb-5 inline-block rounded-full bg-oassab-orange/15 px-4 py-1 text-xs font-semibold uppercase tracking-[0.25em] text-oassab-orange">
                    OASSAB · Desde 1960
                </span>
                <h1 class="font-heading text-3xl font-bold leading-tight text-white sm:text-4xl md:text-5xl lg:text-6xl">
                    Obras de Assistência e de Serviço Social da Arquidiocese de Brasília
                </h1>
                <p class="mt-6 max-w-2xl text-base text-white/85 md:text-lg">
                    Fé que se transforma em ação há mais de 60 anos, promovendo inclusão, cidadania e justiça social no Distrito Federal.
                </p>
                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="#topo" class="btn-primary">
                        conheça a OASSAB
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
                    </a>
                    <a href="{{ url('/quem-somos') }}" class="btn-outline">
                        Quem somos
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section id="topo" class="bg-white">
        <div class="container-oassab grid items-center gap-12 py-20 lg:grid-cols-2 lg:py-28">
            <div class="space-y-6">
                <span class="section-eyebrow">Nossa missão</span>
                <h2 class="section-title">Conheça mais sobre nós</h2>
                <p class="text-base leading-relaxed text-oassab-gray md:text-lg">
                    A OASSAB reúne <strong class="text-oassab-blue">166 paróquias</strong> e <strong class="text-oassab-blue">22 entidades associadas</strong>, promovendo ações sociais, cursos de capacitação e apoio direto a milhares de famílias em situação de vulnerabilidade no Distrito Federal.
                </p>
                <p class="text-base leading-relaxed text-oassab-gray md:text-lg">
                    A OASSAB foi criada em <strong>22/12/1960</strong> por Dom José Newton de Almeida Baptista, primeiro arcebispo de Brasília, com o intuito de dar caráter oficial às obras sociais da Igreja Católica no Distrito Federal. É vinculada à Mitra Arquidiocesana de Brasília e é expressão concreta do compromisso da Igreja com a promoção da dignidade humana.
                </p>
                <a href="{{ url('/quem-somos') }}" class="btn-ghost">
                    Saiba mais
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                </a>
            </div>

            <div class="relative">
                <div class="overflow-hidden rounded-3xl shadow-xl">
                    <x-static-picture
                        src="/images/about-bg.jpg"
                        :widths="[480, 800, 1200]"
                        alt="OASSAB em ação"
                        sizes="(max-width: 1024px) 100vw, 50vw"
                        class="h-full w-full object-cover"
                        :width="1000"
                        :height="1500"
                    />
                </div>
                <div class="absolute -bottom-8 -left-6 hidden w-44 rounded-2xl bg-oassab-orange p-5 text-white shadow-2xl md:block">
                    <div class="text-3xl font-extrabold leading-none">+60</div>
                    <p class="mt-2 text-xs font-semibold uppercase tracking-wider">anos transformando vidas</p>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-oassab-cream">
        <div class="container-oassab py-16">
            <div class="grid gap-6 rounded-3xl bg-white p-8 shadow-sm md:grid-cols-3 md:p-12">
                <x-stat-card label="Projetos Realizados">
                    <x-slot:number>
                        + de <span data-counter="123">123</span>
                    </x-slot:number>
                </x-stat-card>
                <x-stat-card label="Projetos em andamento">
                    <x-slot:number>
                        <span data-counter="12">12</span>
                    </x-slot:number>
                </x-stat-card>
                <x-stat-card label="Pessoas atendidas">
                    <x-slot:number>
                        + de <span data-counter="5000">5 mil</span>
                    </x-slot:number>
                </x-stat-card>
            </div>
        </div>
    </section>

    <section class="relative isolate overflow-hidden bg-oassab-blue text-white">
        <div class="absolute inset-0 -z-10 opacity-20">
            <x-static-picture
                src="/images/services-bg.jpg"
                :widths="[800, 1200, 1600]"
                alt=""
                sizes="100vw"
                class="h-full w-full object-cover"
                :width="2048"
                :height="1365"
                imgAttrs='aria-hidden="true"'
            />
        </div>
        <div class="absolute inset-0 -z-10 bg-gradient-to-b from-oassab-blue-dark/95 via-oassab-blue/90 to-oassab-blue-dark/95"></div>

        <div class="container-oassab py-20 md:py-24">
            <div class="mx-auto max-w-3xl text-center">
                <span class="mb-4 inline-block text-xs font-semibold uppercase tracking-[0.3em] text-oassab-orange">
                    Assessoramento Assistencial
                </span>
                <h2 class="font-heading text-3xl font-bold text-white md:text-4xl">
                    Ações de atendimento direto às pessoas em situação de risco e vulnerabilidade social
                </h2>
            </div>

            <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <x-service-card title="Orientação" icon="users">
                    Na sede da própria instituição, às pessoas em situação de vulnerabilidade que procuram a entidade com alguma demanda assistencial (cesta de alimentos, roupas, cobertor, problemas com documentos pessoais), e ou orientações.
                </x-service-card>

                <x-service-card title="Encaminhamentos" icon="briefcase">
                    Encaminhamentos para a rede SUAS, entidades parceiras que promovam projetos, programas e serviços de suporte (CRAS, CREAS, Conselho Tutelar), benefícios sociais (BPC), segunda via de documentos e cursos de qualificação para geração de renda — como o <strong>Projeto Café Empreendedor</strong>.
                </x-service-card>

                <x-service-card title="Doação de Cestas Básicas" icon="box">
                    Roupas, sapatos, cobertores, itens de higiene pessoal para pessoas em situação de vulnerabilidade social, que buscam ajuda assistencial na sede da OASSAB.
                </x-service-card>

                <x-service-card title="Caravana Partilha Brasília" icon="hand">
                    Mutirão de cuidado e cidadania (corte de cabelo, atendimento médico, odontológico, nutricional, esporte, lazer e cultura), realizado em parceria com o Sesi a cada 3 meses em regiões de maior vulnerabilidade social do DF, com encaminhamentos para a rede SUAS.
                </x-service-card>

                <x-service-card title="Mesa Brasil" icon="apple">
                    Distribuição de alimentos da parceria Mesa Brasil — alface, abacate, abobrinha, abóbora, banana, batata-doce, berinjela, beterraba, cebolinha, couve, limão, morango, mandioca, pimentão, repolho — proporcionando segurança alimentar e mais nutrientes aos nossos assistidos.
                </x-service-card>

                <a href="https://wa.me/556132238431" target="_blank" rel="noopener"
                   class="group flex h-full flex-col items-start justify-between rounded-2xl bg-oassab-orange p-7 text-white shadow-lg transition hover:-translate-y-1 hover:shadow-2xl">
                    <div class="space-y-3">
                        <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-white/15">
                            <svg viewBox="0 0 24 24" fill="currentColor" class="h-7 w-7" aria-hidden="true"><path d="M17.47 14.38c-.3-.15-1.74-.86-2.01-.96-.27-.1-.46-.15-.66.15-.2.3-.76.96-.93 1.16-.17.2-.34.22-.63.07-.3-.15-1.25-.46-2.39-1.46a8.96 8.96 0 0 1-1.65-2.06c-.17-.3-.02-.46.13-.6.13-.13.3-.34.45-.5.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.07-.15-.66-1.6-.91-2.18-.24-.57-.48-.49-.66-.5h-.56c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48 0 1.46 1.06 2.87 1.21 3.07.15.2 2.1 3.2 5.07 4.49.71.3 1.26.49 1.69.62.71.22 1.35.19 1.86.12.57-.08 1.74-.71 1.99-1.4.25-.69.25-1.28.17-1.4-.07-.13-.27-.2-.56-.35z"/></svg>
                        </div>
                        <h3 class="font-heading text-lg font-semibold uppercase tracking-wide">
                            Quer falar com a gente?
                        </h3>
                        <p class="text-sm text-white/85">
                            Tire dúvidas, conheça nossas ações ou colabore com a OASSAB.
                        </p>
                    </div>
                    <span class="mt-6 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider">
                        Fale com a gente
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 transition group-hover:translate-x-1" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                    </span>
                </a>
            </div>
        </div>
    </section>

    <section class="bg-oassab-cream">
        <div class="container-oassab py-20 md:py-24">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <div class="max-w-2xl">
                    <span class="section-eyebrow">Acompanhe</span>
                    <h2 class="section-title">Principais Acontecimentos</h2>
                    <p class="mt-3 text-oassab-gray md:text-lg">
                        Fique por dentro dos eventos, projetos, festas e muito mais.
                    </p>
                </div>
                <a href="{{ url('/noticias') }}" class="btn-ghost">
                    Ver todas as notícias
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                </a>
            </div>

            <div class="mt-12 grid gap-6 md:grid-cols-3">
                @foreach ($latestNews as $post)
                    <x-post-card :post="$post" />
                @endforeach
            </div>
        </div>
    </section>

    <x-cta-section />
@endsection
