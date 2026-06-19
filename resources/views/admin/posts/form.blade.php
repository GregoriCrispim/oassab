@extends('admin.layouts.admin')

@php
    $isEditing = $post->exists;
    $action = $isEditing ? route('admin.posts.update', $post) : route('admin.posts.store');
    $coverImageUrl = $post->coverImageUrl();
@endphp

@section('title', $isEditing ? 'Editar post' : 'Novo post')
@section('subtitle', $isEditing ? 'Atualize o conteúdo da publicação' : 'Crie uma nova publicação')

@push('head')
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
    <style>
        .ql-toolbar.ql-snow,
        .ql-container.ql-snow {
            border-color: #efefef;
        }
        .ql-container {
            min-height: 280px;
            font-family: inherit;
            font-size: 15px;
        }
        .ql-editor {
            min-height: 280px;
        }
    </style>
@endpush

@section('content')
    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-3 text-sm text-red-800">
            <p class="mb-1 font-semibold">Corrija os erros abaixo:</p>
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="grid gap-6 lg:grid-cols-[2fr,1fr]">
        @csrf

        <div class="space-y-6">
            <div class="rounded-2xl border border-oassab-border bg-white p-6 shadow-sm">
                <label class="block">
                    <span class="text-xs font-semibold uppercase tracking-wider text-oassab-blue">Título</span>
                    <input type="text" name="title" value="{{ old('title', $post->title) }}" required
                           class="mt-2 w-full rounded-lg border border-oassab-border bg-white px-4 py-3 text-base text-oassab-blue focus:border-oassab-orange focus:outline-none">
                </label>

                <label class="mt-4 block">
                    <span class="text-xs font-semibold uppercase tracking-wider text-oassab-blue">Slug (URL)</span>
                    <input type="text" name="slug" value="{{ old('slug', $post->slug) }}" placeholder="deixe vazio para gerar a partir do título"
                           data-slug-input data-slug-source="title"
                           class="mt-2 w-full rounded-lg border border-oassab-border bg-white px-4 py-3 text-sm text-oassab-blue focus:border-oassab-orange focus:outline-none">
                    <span class="mt-1 block text-xs text-oassab-gray">Acentos, espaços e maiúsculas são ajustados automaticamente (ex.: «Meu Post» → meu-post).</span>
                    @if ($isEditing)
                        <span class="mt-1 block text-xs text-oassab-gray">URL atual: /posts/{{ $post->slug }}</span>
                    @endif
                </label>

                <label class="mt-4 block">
                    <span class="text-xs font-semibold uppercase tracking-wider text-oassab-blue">Resumo</span>
                    <textarea name="excerpt" rows="2" placeholder="Texto curto que aparece nos cards e no SEO"
                              class="mt-2 w-full rounded-lg border border-oassab-border bg-white px-4 py-3 text-sm text-oassab-blue focus:border-oassab-orange focus:outline-none">{{ old('excerpt', $post->excerpt) }}</textarea>
                </label>
            </div>

            <div class="rounded-2xl border border-oassab-border bg-white p-6 shadow-sm">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-oassab-blue">Conteúdo</p>
                <div id="post-editor"></div>
                <textarea name="body" id="post-body-input" hidden>{{ old('body', $post->body) }}</textarea>
            </div>
        </div>

        <aside class="space-y-6">
            <div class="rounded-2xl border border-oassab-border bg-white p-6 shadow-sm">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-oassab-blue">Publicação</p>

                <label class="block">
                    <span class="text-xs font-medium text-oassab-gray">Data</span>
                    <input type="date" name="date" required
                           value="{{ old('date', optional($post->date)->format('Y-m-d') ?? now()->toDateString()) }}"
                           class="mt-1 w-full rounded-lg border border-oassab-border bg-white px-3 py-2 text-sm text-oassab-blue focus:border-oassab-orange focus:outline-none">
                </label>

                <label class="mt-4 flex items-center gap-2 text-sm text-oassab-blue">
                    <input type="hidden" name="is_published" value="0">
                    <input type="checkbox" name="is_published" value="1"
                           @checked(old('is_published', $post->is_published))
                           class="h-4 w-4 rounded border-oassab-border text-oassab-orange focus:ring-oassab-orange">
                    Publicado (visível no site)
                </label>

                <label class="mt-4 block">
                    <span class="text-xs font-medium text-oassab-gray">Edital relacionado</span>
                    <select name="edital_id"
                            class="mt-1 w-full rounded-lg border border-oassab-border bg-white px-3 py-2 text-sm text-oassab-blue focus:border-oassab-orange focus:outline-none">
                        <option value="">Nenhum</option>
                        @foreach ($editais as $editalOption)
                            <option value="{{ $editalOption->id }}"
                                @selected((string) old('edital_id', $post->edital_id) === (string) $editalOption->id)>
                                {{ $editalOption->title }}
                            </option>
                        @endforeach
                    </select>
                    <span class="mt-1 block text-xs text-oassab-gray">Exibe um link «Ver edital» na página da notícia.</span>
                </label>
            </div>

            <div class="rounded-2xl border border-oassab-border bg-white p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-oassab-blue">Categorias</p>
                <p class="mt-2 text-sm text-oassab-gray">Selecione onde o post será exibido no site.</p>

                <p class="mt-4 rounded-xl border border-oassab-blue/10 bg-oassab-cream/80 px-4 py-3 text-xs leading-relaxed text-oassab-gray">
                    Documentos do Portal da Transparência usam outro formato — cadastre em
                    <a href="{{ route('admin.transparency-documents.index') }}" class="font-semibold text-oassab-orange hover:underline">Admin → Transparência</a>.
                </p>

                @php
                    $current = old('categories', $selectedCategoryIds);
                    $categoryMeta = [
                        'noticias' => [
                            'description' => 'Listagem de notícias; entre as mais recentes, pode aparecer na página inicial.',
                            'page_url' => route('noticias'),
                            'page_label' => 'Ver notícias',
                        ],
                        'projetos' => [
                            'description' => 'Seção «Projetos em andamento» na página de projetos.',
                            'page_url' => route('projetos'),
                            'page_label' => 'Ver projetos',
                        ],
                    ];
                @endphp

                <div class="mt-5 space-y-3">
                    @foreach ($categories as $cat)
                        @php $meta = $categoryMeta[$cat->slug] ?? null; @endphp
                        <div class="flex flex-col gap-3 rounded-xl border border-oassab-border bg-white p-4 transition hover:border-oassab-orange/30 hover:bg-oassab-cream/40 sm:flex-row sm:items-center sm:gap-4">
                            <label class="flex min-w-0 flex-1 cursor-pointer items-start gap-3">
                                <input type="checkbox" name="categories[]" value="{{ $cat->id }}"
                                       @checked(in_array($cat->id, (array) $current))
                                       class="mt-1 h-4 w-4 shrink-0 rounded border-oassab-border text-oassab-orange focus:ring-oassab-orange">
                                <span class="min-w-0">
                                    <span class="block text-sm font-semibold text-oassab-blue">{{ $cat->name }}</span>
                                    @if ($meta)
                                        <span class="mt-1 block text-xs leading-relaxed text-oassab-gray">{{ $meta['description'] }}</span>
                                    @endif
                                </span>
                            </label>
                            @if ($meta)
                                <a href="{{ $meta['page_url'] }}" target="_blank" rel="noopener"
                                   class="inline-flex shrink-0 items-center justify-center gap-1.5 self-start rounded-full border border-oassab-blue/15 bg-white px-4 py-2 text-[11px] font-semibold uppercase tracking-wider text-oassab-blue transition hover:border-oassab-orange hover:bg-oassab-orange hover:text-white sm:self-center">
                                    {{ $meta['page_label'] }}
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5" aria-hidden="true">
                                        <path d="M14 3h7v7M21 3l-9 9M5 5h6V3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-6h-2v6H5z"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-oassab-border bg-white p-6 shadow-sm">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-oassab-blue">Capa</p>

                <div data-image-preview-box
                     class="mb-4 overflow-hidden rounded-xl border border-oassab-border bg-oassab-cream/50 {{ $coverImageUrl ? '' : 'hidden' }}">
                    <p class="border-b border-oassab-border bg-white px-3 py-2 text-[10px] font-semibold uppercase tracking-wider text-oassab-gray">
                        <span data-image-preview-label>{{ $coverImageUrl ? 'Imagem atual' : 'Pré-visualização' }}</span>
                    </p>
                    <img data-image-preview-img
                         src="{{ $coverImageUrl ?? '' }}"
                         alt="Pré-visualização da capa"
                         class="h-48 w-full object-cover"
                         @if (! $coverImageUrl) hidden @endif>
                </div>

                @if ($coverImageUrl)
                    <label class="mb-3 flex items-center gap-2 text-sm text-oassab-gray">
                        <input type="checkbox" name="remove_image" value="1"
                               data-image-preview-remove
                               class="h-4 w-4 rounded border-oassab-border text-red-500 focus:ring-red-500">
                        Remover imagem atual
                    </label>
                @endif

                <input type="file" name="image" accept="image/png,image/jpeg,image/webp"
                       data-image-preview-input
                       data-existing-src="{{ $coverImageUrl ?? '' }}"
                       class="block w-full text-xs text-oassab-gray file:mr-3 file:rounded-full file:border-0 file:bg-oassab-blue file:px-4 file:py-2 file:text-xs file:font-semibold file:uppercase file:tracking-wider file:text-white hover:file:bg-oassab-orange">
                <p class="mt-2 text-xs text-oassab-gray">JPG, PNG ou WEBP — até 4 MB. A pré-visualização aparece ao selecionar o arquivo.</p>
            </div>

            <div class="flex flex-col gap-2">
                <button type="submit" class="btn-primary justify-center">
                    {{ $isEditing ? 'Salvar alterações' : 'Criar post' }}
                </button>
                <a href="{{ route('admin.posts.index') }}"
                   class="text-center text-xs font-semibold uppercase tracking-wider text-oassab-gray hover:text-oassab-orange">
                    Cancelar
                </a>
            </div>
        </aside>
    </form>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const editor = new Quill('#post-editor', {
                theme: 'snow',
                placeholder: 'Escreva o conteúdo do post...',
                modules: {
                    toolbar: [
                        [{ header: [false, 2, 3, 4] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        ['blockquote', 'link'],
                        [{ align: [] }],
                        ['clean'],
                    ],
                },
            });

            const hidden = document.getElementById('post-body-input');
            if (hidden.value) {
                editor.root.innerHTML = hidden.value;
            }

            editor.on('text-change', () => {
                hidden.value = editor.root.innerHTML;
            });

            editor.root.closest('form')?.addEventListener('submit', () => {
                hidden.value = editor.root.innerHTML;
            });
        });
    </script>
@endpush
