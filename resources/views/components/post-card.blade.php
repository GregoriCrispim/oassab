@props(['post', 'priority' => false])

@php
    $primary = method_exists($post, 'primaryCategorySlug') ? $post->primaryCategorySlug() : 'noticias';
    $categoryLabel = ['noticias' => 'Notícias', 'projetos' => 'Projetos', 'transparencia' => 'Transparência'][$primary] ?? 'Publicação';
    $imageSet = method_exists($post, 'imageSet') ? $post->imageSet() : null;
    $excerpt = $post->excerpt;
@endphp

<a href="{{ route('post', ['slug' => $post->slug]) }}"
   class="group flex h-full flex-col overflow-hidden rounded-2xl border border-oassab-border bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
    <div class="relative aspect-[4/3] w-full overflow-hidden bg-oassab-blue/5">
        <x-responsive-image
            :set="$imageSet"
            :alt="$post->title"
            sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"
            class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
            :priority="$priority"
            fallback="/images/posts/placeholder.jpg"
        />
        <span class="absolute left-4 top-4 rounded-full bg-oassab-orange px-3 py-1 text-[11px] font-semibold uppercase tracking-wider text-white">
            {{ $categoryLabel }}
        </span>
    </div>
    <div class="flex flex-1 flex-col p-6">
        <p class="mb-2 text-xs font-medium uppercase tracking-wider text-oassab-gray/80">
            {{ \App\Models\Post::formatDate(optional($post->date)->format('Y-m-d') ?? '') }}
        </p>
        <h3 class="mb-3 font-heading text-lg font-semibold leading-snug text-oassab-blue transition group-hover:text-oassab-orange">
            {{ $post->title }}
        </h3>
        @if (! empty($excerpt))
            <p class="line-clamp-3 text-sm text-oassab-gray">{{ $excerpt }}</p>
        @endif
        <span class="mt-5 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-oassab-orange">
            Ler mais
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 transition group-hover:translate-x-1" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
        </span>
    </div>
</a>
