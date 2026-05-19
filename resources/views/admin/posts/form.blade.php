@extends('admin.layouts.admin')

@php
    $isEditing = $post->exists;
    $action = $isEditing ? route('admin.posts.update', $post) : route('admin.posts.store');
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
        @if ($isEditing)
            @method('PUT')
        @endif

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
                           class="mt-2 w-full rounded-lg border border-oassab-border bg-white px-4 py-3 text-sm text-oassab-blue focus:border-oassab-orange focus:outline-none">
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
            </div>

            <div class="rounded-2xl border border-oassab-border bg-white p-6 shadow-sm">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-oassab-blue">Categorias</p>
                <p class="mb-3 text-xs text-oassab-gray">Selecione pelo menos uma. O post aparecerá em todas as páginas das categorias marcadas.</p>

                @php $current = old('categories', $selectedCategoryIds); @endphp
                <div class="space-y-2">
                    @foreach ($categories as $cat)
                        <label class="flex items-center gap-2 text-sm text-oassab-blue">
                            <input type="checkbox" name="categories[]" value="{{ $cat->id }}"
                                   @checked(in_array($cat->id, (array) $current))
                                   class="h-4 w-4 rounded border-oassab-border text-oassab-orange focus:ring-oassab-orange">
                            {{ $cat->name }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-oassab-border bg-white p-6 shadow-sm">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-oassab-blue">Capa</p>

                @if ($post->image)
                    <div class="mb-3 overflow-hidden rounded-lg border border-oassab-border">
                        <img src="{{ $post->image }}" alt="" class="h-40 w-full object-cover">
                    </div>
                    <label class="mb-3 flex items-center gap-2 text-sm text-oassab-gray">
                        <input type="checkbox" name="remove_image" value="1"
                               class="h-4 w-4 rounded border-oassab-border text-red-500 focus:ring-red-500">
                        Remover imagem atual
                    </label>
                @endif

                <input type="file" name="image" accept="image/png,image/jpeg,image/webp"
                       class="block w-full text-xs text-oassab-gray file:mr-3 file:rounded-full file:border-0 file:bg-oassab-blue file:px-4 file:py-2 file:text-xs file:font-semibold file:uppercase file:tracking-wider file:text-white hover:file:bg-oassab-orange">
                <p class="mt-2 text-xs text-oassab-gray">JPG, PNG ou WEBP — até 4 MB.</p>
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
