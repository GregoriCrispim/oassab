@props(['number', 'label'])

<div class="border-l border-oassab-blue/10 px-6 py-2 text-center first:border-l-0 md:px-8 md:text-left">
    <div class="font-heading text-4xl font-extrabold leading-none text-oassab-orange md:text-5xl lg:text-6xl">
        {{ $number }}
    </div>
    <p class="mt-3 text-sm font-semibold uppercase tracking-[0.2em] text-oassab-blue">
        {{ $label }}
    </p>
</div>
