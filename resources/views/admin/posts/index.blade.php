@extends('admin.layouts.admin')

@section('title', 'Posts')
@section('subtitle', 'Notícias, projetos e itens do Portal Transparência')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <form method="GET" action="{{ route('admin.posts.index') }}" class="flex flex-wrap items-end gap-3">
            <label class="block">
                <span class="text-xs font-semibold uppercase tracking-wider text-oassab-blue">Categoria</span>
                <select name="category" class="mt-1 rounded-lg border border-oassab-border bg-white px-3 py-2 text-sm text-oassab-blue focus:border-oassab-orange focus:outline-none">
                    <option value="">Todas</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->slug }}" @selected($currentCategory === $cat->slug)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="text-xs font-semibold uppercase tracking-wider text-oassab-blue">Buscar</span>
                <input type="search" name="search" value="{{ $currentSearch }}" placeholder="Título ou slug"
                       class="mt-1 rounded-lg border border-oassab-border bg-white px-3 py-2 text-sm text-oassab-blue focus:border-oassab-orange focus:outline-none">
            </label>
            <button type="submit" class="btn-ghost">Filtrar</button>
            @if ($currentCategory || $currentSearch)
                <a href="{{ route('admin.posts.index') }}" class="text-xs font-semibold uppercase tracking-wider text-oassab-gray hover:text-oassab-orange">Limpar</a>
            @endif
        </form>

        <a href="{{ route('admin.posts.create') }}" class="btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
            Novo post
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-oassab-border bg-white shadow-sm">
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
                @forelse ($posts as $post)
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-medium text-oassab-blue">{{ $post->title }}</p>
                            <p class="text-xs text-oassab-gray">/posts/{{ $post->slug }}</p>
                        </td>
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
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('post', $post) }}" target="_blank" rel="noopener"
                                   class="text-xs font-semibold uppercase tracking-wider text-oassab-gray hover:text-oassab-orange">Ver</a>
                                <a href="{{ route('admin.posts.edit', $post) }}"
                                   class="text-xs font-semibold uppercase tracking-wider text-oassab-orange hover:underline">Editar</a>
                                <form method="POST" action="{{ route('admin.posts.destroy', $post) }}"
                                      onsubmit="return confirm('Excluir este post? Essa ação não pode ser desfeita.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold uppercase tracking-wider text-red-600 hover:underline">Excluir</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-oassab-gray">Nenhum post encontrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($posts->hasPages())
        <div class="mt-6">
            {{ $posts->links() }}
        </div>
    @endif
@endsection
