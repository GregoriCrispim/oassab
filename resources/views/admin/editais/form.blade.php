@extends('admin.layouts.admin')

@php
    $isEditing = $edital->exists;
    $action = $isEditing ? route('admin.editais.update', $edital) : route('admin.editais.store');
@endphp

@section('title', $isEditing ? 'Editar edital' : 'Novo edital')
@section('subtitle', $isEditing ? 'Atualize o conteúdo e os PDFs do edital' : 'Cadastre um edital do Programa Edital Aberto')

@push('head')
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
    <style>
        .ql-toolbar.ql-snow, .ql-container.ql-snow { border-color: #efefef; }
        .ql-container { min-height: 220px; font-family: inherit; font-size: 15px; }
        .ql-editor { min-height: 220px; }
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
                    <input type="text" name="title" value="{{ old('title', $edital->title) }}" required
                           class="mt-2 w-full rounded-lg border border-oassab-border bg-white px-4 py-3 text-base text-oassab-blue focus:border-oassab-orange focus:outline-none">
                </label>

                <label class="mt-4 block">
                    <span class="text-xs font-semibold uppercase tracking-wider text-oassab-blue">Slug (URL)</span>
                    <input type="text" name="slug" value="{{ old('slug', $edital->slug) }}" placeholder="deixe vazio para gerar a partir do título"
                           data-slug-input data-slug-source="title"
                           class="mt-2 w-full rounded-lg border border-oassab-border bg-white px-4 py-3 text-sm text-oassab-blue focus:border-oassab-orange focus:outline-none">
                    <span class="mt-1 block text-xs text-oassab-gray">Acentos, espaços e maiúsculas são ajustados automaticamente (ex.: «Meu Edital» → meu-edital).</span>
                    @if ($isEditing)
                        <span class="mt-1 block text-xs text-oassab-gray">URL: /editais/{{ $edital->slug }}</span>
                    @endif
                </label>

                <label class="mt-4 block">
                    <span class="text-xs font-semibold uppercase tracking-wider text-oassab-blue">Resumo</span>
                    <textarea name="excerpt" rows="2" placeholder="Texto curto exibido na listagem de editais"
                              class="mt-2 w-full rounded-lg border border-oassab-border bg-white px-4 py-3 text-sm text-oassab-blue focus:border-oassab-orange focus:outline-none">{{ old('excerpt', $edital->excerpt) }}</textarea>
                </label>
            </div>

            <div class="rounded-2xl border border-oassab-border bg-white p-6 shadow-sm">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-oassab-blue">Conteúdo</p>
                <div id="edital-editor"></div>
                <textarea name="body" id="edital-body-input" hidden>{{ old('body', $edital->body) }}</textarea>
            </div>
        </div>

        <aside class="space-y-6">
            <div class="rounded-2xl border border-oassab-border bg-white p-6 shadow-sm">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-oassab-blue">Publicação</p>

                <label class="block">
                    <span class="text-xs font-medium text-oassab-gray">Data</span>
                    <input type="date" name="date" required
                           value="{{ old('date', optional($edital->date)->format('Y-m-d') ?? now()->toDateString()) }}"
                           class="mt-1 w-full rounded-lg border border-oassab-border bg-white px-3 py-2 text-sm text-oassab-blue focus:border-oassab-orange focus:outline-none">
                </label>

                <label class="mt-4 block">
                    <span class="text-xs font-medium text-oassab-gray">Ordem de exibição</span>
                    <input type="number" name="sort_order" min="0" max="9999"
                           value="{{ old('sort_order', $edital->sort_order ?? 0) }}"
                           class="mt-1 w-full rounded-lg border border-oassab-border bg-white px-3 py-2 text-sm text-oassab-blue focus:border-oassab-orange focus:outline-none">
                </label>

                <label class="mt-4 flex items-center gap-2 text-sm text-oassab-blue">
                    <input type="hidden" name="is_published" value="0">
                    <input type="checkbox" name="is_published" value="1"
                           @checked(old('is_published', $edital->is_published))
                           class="h-4 w-4 rounded border-oassab-border text-oassab-orange focus:ring-oassab-orange">
                    Publicado (visível no site)
                </label>
            </div>

            <div class="rounded-2xl border border-oassab-border bg-white p-6 shadow-sm">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-oassab-blue">PDF do edital</p>
                <p class="mb-3 text-xs text-oassab-gray">Documento principal do edital (opcional).
                    @if (app(\App\Services\GoogleDriveService::class)->isConfigured())
                        Os PDFs são armazenados no Google Drive.
                    @endif
                </p>

                @if ($isEditing && $edital->hasMainFile())
                    <p class="mb-3 text-sm text-oassab-gray">
                        Atual:
                        <a href="{{ $edital->mainFileUrl() }}" target="_blank" rel="noopener" class="font-semibold text-oassab-orange hover:underline">
                            {{ $edital->original_filename ?: 'Ver PDF' }}
                        </a>
                    </p>
                    <label class="mb-3 flex items-center gap-2 text-sm text-oassab-gray">
                        <input type="checkbox" name="remove_file" value="1"
                               class="h-4 w-4 rounded border-oassab-border text-red-500 focus:ring-red-500">
                        Remover PDF principal
                    </label>
                @endif

                <input type="file" name="file" accept="application/pdf"
                       class="block w-full text-xs text-oassab-gray file:mr-3 file:rounded-full file:border-0 file:bg-oassab-blue file:px-4 file:py-2 file:text-xs file:font-semibold file:uppercase file:tracking-wider file:text-white hover:file:bg-oassab-orange">
                <p class="mt-2 text-xs text-oassab-gray">PDF — até 20 MB.</p>
            </div>

            <div class="rounded-2xl border border-oassab-border bg-white p-6 shadow-sm">
                <div class="mb-3 flex items-center justify-between gap-2">
                    <p class="text-xs font-semibold uppercase tracking-wider text-oassab-blue">Anexos (PDFs)</p>
                    <button type="button" id="add-attachment-btn"
                            class="text-xs font-semibold uppercase tracking-wider text-oassab-orange hover:underline">
                        + Adicionar anexo
                    </button>
                </div>
                <p class="mb-4 text-xs text-oassab-gray">Documentos correlatos — formulários, retificações, resultados, etc. (PDF, DOC ou DOCX).</p>

                @if ($isEditing && $edital->attachments->isNotEmpty())
                    <div class="mb-4 space-y-2">
                        <p class="text-xs font-semibold uppercase tracking-wider text-oassab-gray">Anexos atuais</p>
                        @foreach ($edital->attachments as $attachment)
                            <label class="flex items-start gap-2 rounded-lg border border-oassab-border bg-oassab-cream/50 p-3 text-sm">
                                <input type="checkbox" name="remove_attachments[]" value="{{ $attachment->id }}"
                                       class="mt-1 h-4 w-4 rounded border-oassab-border text-red-500 focus:ring-red-500">
                                <span class="flex-1 text-oassab-blue">
                                    {{ $attachment->title }}
                                    <a href="{{ route('editais.files.attachment', [$edital, $attachment]) }}" target="_blank" rel="noopener" class="ml-1 text-xs text-oassab-orange hover:underline">(ver)</a>
                                </span>
                            </label>
                        @endforeach
                        <p class="text-xs text-oassab-gray">Marque para remover ao salvar.</p>
                    </div>
                @endif

                <div id="attachment-rows" class="space-y-3"></div>
            </div>

            <div class="flex flex-col gap-2">
                <button type="submit" class="btn-primary justify-center">
                    {{ $isEditing ? 'Salvar alterações' : 'Criar edital' }}
                </button>
                <a href="{{ route('admin.editais.index') }}"
                   class="text-center text-xs font-semibold uppercase tracking-wider text-oassab-gray hover:text-oassab-orange">
                    Cancelar
                </a>
            </div>
        </aside>
    </form>

    <template id="attachment-row-template">
        <div class="attachment-row rounded-lg border border-dashed border-oassab-border p-3">
            <label class="block">
                <span class="text-xs font-medium text-oassab-gray">Nome do anexo</span>
                <input type="text" name="attachment_titles[]" placeholder="Ex.: Formulário de inscrição"
                       class="mt-1 w-full rounded-lg border border-oassab-border bg-white px-3 py-2 text-sm text-oassab-blue focus:border-oassab-orange focus:outline-none">
            </label>
            <label class="mt-2 block">
                <span class="text-xs font-medium text-oassab-gray">Arquivo PDF</span>
                <input type="file" name="attachment_files[]" accept="application/pdf,.doc,.docx"
                       class="mt-1 block w-full text-xs text-oassab-gray file:mr-3 file:rounded-full file:border-0 file:bg-oassab-blue/90 file:px-3 file:py-1.5 file:text-[10px] file:font-semibold file:uppercase file:text-white">
            </label>
            <button type="button" class="attachment-remove mt-2 text-xs font-semibold uppercase tracking-wider text-red-600 hover:underline">Remover</button>
        </div>
    </template>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const editor = new Quill('#edital-editor', {
                theme: 'snow',
                placeholder: 'Informações do edital, critérios, prazos...',
                modules: {
                    toolbar: [
                        [{ header: [false, 2, 3] }],
                        ['bold', 'italic', 'underline'],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        ['blockquote', 'link'],
                        ['clean'],
                    ],
                },
            });

            const hidden = document.getElementById('edital-body-input');
            if (hidden.value) editor.root.innerHTML = hidden.value;
            editor.on('text-change', () => { hidden.value = editor.root.innerHTML; });
            editor.root.closest('form')?.addEventListener('submit', () => {
                hidden.value = editor.root.innerHTML;
            });

            const container = document.getElementById('attachment-rows');
            const template = document.getElementById('attachment-row-template');

            const addRow = () => {
                const node = template.content.cloneNode(true);
                node.querySelector('.attachment-remove')?.addEventListener('click', (e) => {
                    e.target.closest('.attachment-row')?.remove();
                });
                container.appendChild(node);
            };

            document.getElementById('add-attachment-btn')?.addEventListener('click', addRow);
            addRow();
        });
    </script>
@endpush
