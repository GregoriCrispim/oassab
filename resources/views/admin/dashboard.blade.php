@extends('admin.layouts.admin')

@section('title', 'Dashboard')
@section('subtitle', 'Visão geral do conteúdo do site')

@section('content')
    <div class="grid gap-5 md:grid-cols-3">
        <div class="rounded-2xl border border-oassab-border bg-white p-6 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-oassab-orange">Total de posts</p>
            <p class="mt-2 font-heading text-4xl font-bold text-oassab-blue">{{ $totalPosts }}</p>
            <p class="mt-2 text-xs text-oassab-gray">{{ $publishedPosts }} publicados · {{ $draftPosts }} rascunhos</p>
        </div>

        @foreach ($byCategory as $cat)
            <div class="rounded-2xl border border-oassab-border bg-white p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-oassab-orange">{{ $cat->name }}</p>
                <p class="mt-2 font-heading text-4xl font-bold text-oassab-blue">{{ $cat->posts_count }}</p>
                <a href="{{ route('admin.posts.index', ['category' => $cat->slug]) }}"
                   class="mt-2 inline-flex items-center gap-1 text-xs font-semibold uppercase tracking-wider text-oassab-orange hover:underline">
                    Ver todos
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3 w-3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 5l7 7-7 7"/></svg>
                </a>
            </div>
        @endforeach
    </div>

    <div class="mt-8 rounded-2xl border border-oassab-border bg-white p-6 shadow-sm">
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h2 class="font-heading text-lg font-semibold text-oassab-blue">Últimos posts</h2>
                <p class="text-sm text-oassab-gray">5 publicações mais recentes</p>
            </div>
            <a href="{{ route('admin.posts.create') }}" class="btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
                Novo post
            </a>
        </div>

        @if ($recentPosts->isEmpty())
            <p class="text-sm text-oassab-gray">Nenhum post cadastrado ainda.</p>
        @else
            <div class="overflow-hidden rounded-xl border border-oassab-border">
                <table class="min-w-full divide-y divide-oassab-border text-sm">
                    <thead class="bg-oassab-cream text-xs uppercase tracking-wider text-oassab-gray">
                        <tr>
                            <th class="px-4 py-3 text-left">Título</th>
                            <th class="px-4 py-3 text-left">Categorias</th>
                            <th class="px-4 py-3 text-left">Data</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-oassab-border bg-white">
                        @foreach ($recentPosts as $post)
                            <tr>
                                <td class="px-4 py-3 font-medium text-oassab-blue">{{ $post->title }}</td>
                                <td class="px-4 py-3">
                                    @foreach ($post->categories as $cat)
                                        <span class="mr-1 inline-block rounded-full bg-oassab-blue/5 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wider text-oassab-blue">{{ $cat->name }}</span>
                                    @endforeach
                                </td>
                                <td class="px-4 py-3 text-oassab-gray">{{ $post->date?->format('d/m/Y') }}</td>
                                <td class="px-4 py-3">
                                    @if ($post->is_published)
                                        <span class="rounded-full bg-green-100 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wider text-green-800">Publicado</span>
                                    @else
                                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wider text-amber-800">Rascunho</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.posts.edit', $post) }}"
                                       class="text-xs font-semibold uppercase tracking-wider text-oassab-orange hover:underline">Editar</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
