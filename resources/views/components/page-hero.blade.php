@props([
    'title',
    'subtitle' => null,
    'eyebrow' => null,
    'image' => '/images/hero-bg.jpg',
])

@php
    $isDefaultHero = $image === '/images/hero-bg.jpg';
@endphp

<section class="relative isolate overflow-hidden bg-oassab-blue-dark text-white">
    <div class="absolute inset-0 -z-10">
        @if ($isDefaultHero)
            <x-static-picture
                src="/images/hero-bg.jpg"
                :widths="[800, 1200, 1600]"
                alt=""
                sizes="100vw"
                class="h-full w-full object-cover opacity-40"
                :priority="true"
                :width="1920"
                :height="1080"
                imgAttrs='aria-hidden="true"'
            />
        @else
            <img src="{{ $image }}" alt="" aria-hidden="true"
                 fetchpriority="high" decoding="async"
                 class="h-full w-full object-cover opacity-40">
        @endif
        <div class="absolute inset-0 hero-overlay"></div>
    </div>

    <div class="container-oassab py-20 md:py-28 lg:py-32">
        <div class="max-w-3xl animate-fade-in-up">
            @if ($eyebrow)
                <span class="mb-4 inline-block rounded-full bg-oassab-orange/15 px-4 py-1 text-xs font-semibold uppercase tracking-[0.25em] text-oassab-orange">
                    {{ $eyebrow }}
                </span>
            @endif
            <h1 class="font-heading text-3xl font-bold leading-tight text-white sm:text-4xl md:text-5xl lg:text-6xl">
                {{ $title }}
            </h1>
            @if ($subtitle)
                <p class="mt-5 max-w-2xl text-base text-white/80 md:text-lg">
                    {{ $subtitle }}
                </p>
            @endif
            {{ $slot }}
        </div>
    </div>
</section>
