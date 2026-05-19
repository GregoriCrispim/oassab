@extends('layouts.app')

@section('title', 'Quem somos')
@section('description', 'A OASSAB é uma organização da sociedade civil de assistência social, sem fins lucrativos, vinculada à Mitra Arquidiocesana de Brasília — atuando no DF desde 1960.')

@section('content')
    <x-page-hero
        eyebrow="Nossa história"
        title="Quem somos e por quem lutamos."
        subtitle="Desde 1960, a presença viva da Igreja Católica no cuidado com os mais vulneráveis do Distrito Federal."
        image="/images/about-bg.jpg"
    />

    <section class="bg-white">
        <div class="container-oassab grid gap-12 py-20 lg:grid-cols-[1.1fr,1fr] lg:py-24">
            <div class="space-y-6">
                <span class="section-eyebrow">Nossa História</span>
                <h2 class="section-title">Quase 65 anos de fé e ação</h2>
                <div class="prose-oassab space-y-4">
                    <p>A OASSAB foi criada em <strong>1960</strong> por Dom José Newton de Almeida Baptista com a missão de dar caráter oficial às obras sociais da Igreja Católica no Distrito Federal.</p>
                    <p>Ao longo de quase 65 anos, tem atuado em eventos religiosos e projetos sociais, promovendo ações como distribuição de enxovais e cestas básicas, pagamento de contas, apoio a exames e medicamentos, além de cursos de capacitação para jovens e adultos.</p>
                    <p>Em parceria com entidades filiadas, realiza projetos educativos e de inclusão em diversas regiões do DF. Atualmente, seu trabalho foca na promoção da cidadania, qualificação profissional, escuta ativa e fortalecimento dos direitos sociais — sempre em comunhão com a Arquidiocese de Brasília.</p>
                </div>
            </div>
            <div class="overflow-hidden rounded-3xl shadow-xl">
                <img src="/images/services-bg.jpg" alt="OASSAB em ação" class="h-full w-full object-cover">
            </div>
        </div>
    </section>

    <section class="bg-oassab-cream">
        <div class="container-oassab grid gap-10 py-20 lg:grid-cols-2">
            <article class="rounded-3xl border border-oassab-border bg-white p-8 shadow-sm md:p-10">
                <span class="section-eyebrow">Quem Somos</span>
                <h3 class="font-heading text-2xl font-bold text-oassab-blue md:text-3xl">Organização da sociedade civil</h3>
                <div class="prose-oassab mt-5">
                    <p>A OASSAB é uma organização da sociedade civil de assistência social, sem fins lucrativos, que atua de forma contínua, planejada e comprometida com a promoção da cidadania e dos direitos sociais no Distrito Federal.</p>
                    <p>Vinculada à Mitra Arquidiocesana de Brasília, reúne <strong>166 paróquias</strong> e <strong>22 entidades associadas</strong>. Por meio de atendimentos diretos, assessoramento técnico e projetos sociais, fortalece movimentos sociais, forma lideranças comunitárias e contribui para a superação das desigualdades.</p>
                    <p>Atua com base nas normas legais, prezando pela transparência na gestão dos recursos e conquistando o reconhecimento de parceiros e órgãos públicos em todas as esferas.</p>
                </div>
            </article>

            <article class="rounded-3xl border border-oassab-border bg-white p-8 shadow-sm md:p-10">
                <span class="section-eyebrow">Finalidade da Entidade</span>
                <h3 class="font-heading text-2xl font-bold text-oassab-blue md:text-3xl">Assistência, educação e cultura</h3>
                <div class="prose-oassab mt-5">
                    <p>A OASSAB é uma associação beneficente e filantrópica, sem fins lucrativos, que atua nas áreas de assistência social, educação e cultura em todo o Distrito Federal.</p>
                    <p>Sua missão é promover o atendimento e o assessoramento a pessoas em situação de vulnerabilidade, apoiar famílias, crianças, adolescentes e idosos, e fortalecer a atuação de suas entidades filiadas.</p>
                    <p>Desenvolve ações que incentivam a convivência social, a cultura religiosa, o turismo educativo e a defesa dos direitos sociais, sempre alinhada à Política Nacional de Assistência Social.</p>
                </div>
            </article>
        </div>
    </section>

    <section class="bg-white">
        <div class="container-oassab py-20 md:py-24">
            <div class="mx-auto max-w-2xl text-center">
                <span class="section-eyebrow">Equipe Atual da OASSAB</span>
                <h2 class="section-title">Pessoas que conduzem nossa missão</h2>
            </div>

            <div class="mt-14 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-oassab-border bg-oassab-cream p-7 shadow-sm">
                    <h4 class="mb-4 font-heading text-base font-semibold uppercase tracking-wider text-oassab-orange">Diretoria</h4>
                    <ul class="space-y-3 text-sm text-oassab-gray">
                        <li><strong class="text-oassab-blue">Presidente:</strong> Padre Rafael Souza dos Santos</li>
                        <li><strong class="text-oassab-blue">Vice-presidente:</strong> Padre José Roberto Angelotto</li>
                        <li><strong class="text-oassab-blue">1º Secretário:</strong> Aresio Teixeira Peixoto</li>
                        <li><strong class="text-oassab-blue">2ª Secretária:</strong> Ivette Maria Fleury Charmillot</li>
                        <li><strong class="text-oassab-blue">1º Tesoureiro:</strong> José Donizetti de Melo</li>
                        <li><strong class="text-oassab-blue">2º Tesoureiro:</strong> Luiz Carlos Santhiago Fontes</li>
                    </ul>
                </div>

                <div class="rounded-2xl border border-oassab-border bg-oassab-cream p-7 shadow-sm">
                    <h4 class="mb-4 font-heading text-base font-semibold uppercase tracking-wider text-oassab-orange">Conselho Fiscal</h4>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-oassab-gray">Titulares</p>
                    <ul class="mb-4 space-y-2 text-sm text-oassab-gray">
                        <li>Alexandre César Fontenelle Pinheiro da Silva</li>
                        <li>Maria Ida Assunção Xavier Alves</li>
                        <li>Paulo Cesar Campos</li>
                    </ul>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-oassab-gray">Suplentes</p>
                    <ul class="space-y-2 text-sm text-oassab-gray">
                        <li>Ana Cristina Coelho Maia de Souza e Silva</li>
                        <li>Maria Amélia Furtado Horta</li>
                        <li>Maria Inês de Oliveira Aguiar Barbosa</li>
                    </ul>
                </div>

                <div class="rounded-2xl border border-oassab-border bg-oassab-cream p-7 shadow-sm">
                    <h4 class="mb-4 font-heading text-base font-semibold uppercase tracking-wider text-oassab-orange">Gerência Executiva</h4>
                    <ul class="space-y-3 text-sm text-oassab-gray">
                        <li><strong class="text-oassab-blue">Gerente-Executivo:</strong> Aridney Loyelo Barcellos</li>
                    </ul>
                </div>

                <div class="rounded-2xl border border-oassab-border bg-oassab-cream p-7 shadow-sm">
                    <h4 class="mb-4 font-heading text-base font-semibold uppercase tracking-wider text-oassab-orange">Apoio</h4>
                    <ul class="space-y-3 text-sm text-oassab-gray">
                        <li><strong class="text-oassab-blue">Ana Paula</strong> — Coordenadora Administrativa</li>
                        <li><strong class="text-oassab-blue">Helen</strong> — Auxiliar Administrativa</li>
                        <li><strong class="text-oassab-blue">Valério</strong> — Atendente Social</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-oassab-cream">
        <div class="container-oassab py-20 md:py-24">
            <div class="mx-auto max-w-3xl text-center">
                <span class="section-eyebrow">Assessoramento Institucional</span>
                <h2 class="section-title">Como atuamos junto às nossas entidades</h2>
            </div>

            <div class="mt-14 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <x-service-card title="Assessoramento Assistencial" icon="hand">
                    Doação de itens às filiadas — paróquias e entidades associadas — de itens apreendidos pela Receita Federal e doados à OASSAB (roupas, eletrônicos, materiais de escritório, maquiagem, itens diversos), cestas básicas e cobertores.
                </x-service-card>

                <x-service-card title="Assessoramento Administrativo" icon="briefcase">
                    Apoio à entidade quanto a atividades administrativas, criação de estatutos, gestão financeira, reorganização, jurídica, contabilidade, captação de recursos públicos, recebimento de doações e realização de bazares.
                </x-service-card>

                <x-service-card title="Assessoramento Financeiro" icon="document">
                    Aporte financeiro para projetos que promovam a defesa e efetivação de Direitos Socioassistenciais, a cidadania e o enfrentamento das desigualdades sociais — de forma emergencial ou continuada, via editais internos e termos de fomento.
                </x-service-card>

                <x-service-card title="Fomento a Projetos" icon="star">
                    Apoio a paróquias e entidades que solicitam verba para eventos e projetos culturais, sociais, esportivos, de lazer e turismo, com captação junto a parlamentares da Câmara Distrital e Federal e administração responsável dos recursos.
                </x-service-card>

                <x-service-card title="Assessoramento Técnico" icon="users">
                    Capacitação de gestores, lideranças e colaboradores das entidades. Em 2023 foi realizada formação em Captação de Recursos — Emendas e Campanhas, para 49 instituições (24 entidades de Assistência Social, movimentos sociais e paróquias).
                </x-service-card>

                <x-service-card title="Visitas Institucionais" icon="building">
                    A OASSAB vai ao encontro de suas entidades parceiras em suas sedes para estreitar laços, conhecer o território onde atuam, identificar necessidades e acompanhar a execução de projetos em parceria.
                </x-service-card>
            </div>
        </div>
    </section>

    <section class="bg-white">
        <div class="container-oassab grid gap-12 py-20 lg:grid-cols-[1fr,1.2fr] lg:py-24">
            <div class="space-y-5">
                <span class="section-eyebrow">Programa Edital Aberto</span>
                <h2 class="section-title">Apoio financeiro a projetos sociais</h2>
                <p class="text-base leading-relaxed text-oassab-gray md:text-lg">
                    O programa apoia financeiramente projetos na área de Assistência Social de nossas entidades associadas, que promovam a defesa de direitos socioassistenciais, a cidadania e apontem soluções para o enfrentamento das desigualdades sociais.
                </p>
            </div>
            <div class="rounded-3xl border border-oassab-border bg-oassab-cream p-8 md:p-10">
                <div class="prose-oassab">
                    <p>O Edital é publicado anualmente pela OASSAB para seleção de projetos de cunho social, de entidades e paróquias que necessitam de aporte financeiro. Após chamamento, as entidades apresentam seus projetos sociais e a OASSAB seleciona conforme critérios do edital.</p>
                    <p>Os beneficiados são <strong>famílias e indivíduos em situação de vulnerabilidade e riscos pessoais e sociais</strong>, como crianças, adolescentes e jovens adultos.</p>
                    <p>O recurso disponibilizado vem da renda de bazares beneficentes, doações e voluntariado. Atualmente cada instituição contemplada recebe <strong>R$ 10 mil</strong> para aplicar em seu projeto.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-oassab-cream">
        <div class="container-oassab py-20 md:py-24">
            <div class="mx-auto max-w-3xl text-center">
                <span class="section-eyebrow">Parcerias e vínculos</span>
                <h2 class="section-title">Entidades associadas</h2>
                <p class="mt-4 text-oassab-gray md:text-lg">
                    A OASSAB é vinculada à Mitra Arquidiocesana de Brasília. São filiadas a esta Obra Social <strong class="text-oassab-blue">166 paróquias do Distrito Federal</strong> e as seguintes 25 instituições associadas:
                </p>
            </div>

            <ul class="mx-auto mt-12 grid max-w-5xl gap-3 text-sm text-oassab-gray sm:grid-cols-2">
                @foreach ([
                    'Ação Social Nossa Senhora do Perpétuo Socorro',
                    'AFAGO – Associação de apoio à Família, ao grupo e à comunidade',
                    'Associação Cristã do Lago Norte – Casa São José',
                    'Associação das Obras Pavonianas de Assistência',
                    'Associação Nossa Senhora do Rosário – Casa da Sopa de São Francisco de Assis',
                    'Associação Santos Inocentes Mártires – ASSIM',
                    'Associação Sociocultural São Luis Orione Itapoã – ASLOI',
                    'Associação Promessa de Rute',
                    'Cáritas Arquidiocesana de Brasília',
                    'Cáritas Paroquial São José',
                    'Carmelo de Nossa Senhora do Carmo',
                    'Centro Social Formar',
                    'Centro Social João Paulo II (Creche-Escola)',
                    'Comunidade Católica Shalom',
                    'Conselho Metropolitano de Brasília da SSVP',
                    'ILEM – Instituto Leonardo Murialdo',
                    'Instituto Abraço Solidário',
                    'Instituto Dom Orione',
                    'Instituto Nossa Senhora da Piedade',
                    'Instituto Nossa Senhora do Brasil – Instituto Santa Teresinha',
                    'Instituto Rainha dos Corações',
                    'Programa Providência de Elevação da Renda Familiar',
                    'Seminário Missionário Arquidiocesano Redemptoris Mater',
                    'Soc. de Empenho na Recuperação de Vidas (SERVOS)',
                    'Vila do Pequenino Jesus',
                ] as $entity)
                    <li class="flex items-start gap-3 rounded-xl border border-oassab-border bg-white px-4 py-3">
                        <span class="mt-1 inline-block h-2 w-2 flex-shrink-0 rounded-full bg-oassab-orange"></span>
                        <span>{{ $entity }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    <x-cta-section />
@endsection
