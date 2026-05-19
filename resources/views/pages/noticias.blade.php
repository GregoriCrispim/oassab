@extends('layouts.app')

@section('title', 'Notícias')
@section('description', 'Acompanhe as notícias e atualizações sobre as ações, eventos e editais da OASSAB.')

@section('content')
    <x-page-hero
        eyebrow="Comunicação"
        title="Notícias da OASSAB"
        subtitle="Fique por dentro dos eventos, projetos, festas e muito mais."
        image="/images/about-bg.jpg"
    />

    <section class="bg-oassab-cream">
        <div class="container-oassab py-16 md:py-20">
            @if (count($posts) === 0)
                <p class="text-center text-oassab-gray">Nenhuma notícia publicada no momento.</p>
            @else
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($posts as $post)
                        <x-post-card :post="$post" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <x-cta-section />
@endsection
