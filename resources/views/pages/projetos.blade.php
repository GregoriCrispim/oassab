@extends('layouts.app')

@section('title', 'Projetos')
@section('description', 'A força dos projetos sociais da OASSAB — parcerias, fé e trabalho conjunto em prol de quem mais precisa.')

@section('content')
    <x-page-hero
        eyebrow="Projetos"
        title="A força dos projetos sociais da OASSAB"
        subtitle="Parcerias, fé e trabalho conjunto em prol de quem mais precisa."
        image="/images/services-bg.jpg"
    />

    <section class="bg-white">
        <div class="container-oassab py-20 md:py-24">
            <div class="mx-auto max-w-3xl text-center">
                <span class="section-eyebrow">Como atuamos</span>
                <h2 class="section-title">Modalidades de assessoramento</h2>
                <p class="mt-4 text-oassab-gray md:text-lg">
                    A OASSAB realiza diferentes formas de assessoramento institucional, técnico e financeiro às suas filiadas e parceiras.
                </p>
            </div>

            <div class="mt-14 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <x-service-card title="Assessoramento Assistencial" icon="hand">
                    Doação de itens às filiadas — paróquias e entidades associadas — de itens apreendidos pela Receita Federal e doados à OASSAB (roupas, eletrônicos, materiais de escritório, maquiagem, itens diversos), cestas básicas e cobertores.
                </x-service-card>

                <x-service-card title="Assessoramento Administrativo" icon="briefcase">
                    Apoio a atividades administrativas, criação de estatutos, gestão financeira, reorganização, jurídica, contabilidade, captação de recursos públicos, recebimento de doações e realização de bazares.
                </x-service-card>

                <x-service-card title="Assessoramento Financeiro" icon="document">
                    Aporte financeiro para projetos que promovam a defesa e efetivação de Direitos Socioassistenciais, a cidadania e o enfrentamento das desigualdades sociais — emergencial ou continuado, via editais internos e termos de fomento.
                </x-service-card>

                <x-service-card title="Fomento a Projetos" icon="star">
                    Apoio a paróquias e entidades que solicitam verba para eventos e projetos culturais, sociais, esportivos, de lazer e turismo, com captação junto a parlamentares e administração responsável dos recursos.
                </x-service-card>

                <x-service-card title="Assessoramento Técnico" icon="users">
                    Capacitação de gestores, lideranças e colaboradores das entidades — formação em captação de recursos, emendas parlamentares, atendimento de editais e realização de campanhas.
                </x-service-card>

                <x-service-card title="Visitas Institucionais" icon="building">
                    A OASSAB vai ao encontro de suas entidades parceiras em suas sedes para estreitar laços, conhecer o território onde atuam, identificar necessidades e acompanhar a execução de projetos em parceria.
                </x-service-card>

                <div class="rounded-2xl border-2 border-dashed border-oassab-orange/40 bg-oassab-orange/5 p-7 lg:col-span-3">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between md:gap-8">
                        <div>
                            <h3 class="font-heading text-xl font-bold text-oassab-blue">Programa Edital Aberto</h3>
                            <p class="mt-2 max-w-3xl text-sm text-oassab-gray">
                                Apoia financeiramente projetos na área de Assistência Social das entidades associadas da OASSAB. Cada instituição contemplada recebe atualmente <strong class="text-oassab-blue">R$ 10 mil</strong> para aplicar em seu projeto.
                            </p>
                        </div>
                        <a href="{{ url('/quem-somos') }}#programa-edital" class="btn-ghost shrink-0">
                            Saiba mais
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-oassab-cream">
        <div class="container-oassab py-20 md:py-24">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <div class="max-w-2xl">
                    <span class="section-eyebrow">Acompanhe</span>
                    <h2 class="section-title">Projetos em andamento</h2>
                    <p class="mt-3 text-oassab-gray md:text-lg">
                        Conheça os projetos sociais e culturais que a OASSAB apoia em parceria com paróquias, entidades e o Distrito Federal.
                    </p>
                </div>
                <a href="{{ url('/transparencia') }}" class="btn-ghost">
                    Portal Transparência
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                </a>
            </div>

            <div class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <x-post-card :post="$post" />
                @endforeach
            </div>
        </div>
    </section>

    <x-cta-section />
@endsection
