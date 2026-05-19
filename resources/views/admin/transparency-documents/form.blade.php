@extends('admin.layouts.admin')

@php
    $isEditing = $document->exists;
    $action = $isEditing
        ? route('admin.transparency-documents.update', $document)
        : route('admin.transparency-documents.store');
@endphp

@section('title', $isEditing ? 'Editar documento' : 'Novo documento')
@section('subtitle', $isEditing ? 'Atualize o PDF e as informações exibidas no portal' : 'Cadastre um novo PDF no Portal Transparência')

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
                    <span class="text-xs font-semibold uppercase tracking-wider text-oassab-blue">Nome</span>
                    <input type="text" name="title" value="{{ old('title', $document->title) }}" required
                           class="mt-2 w-full rounded-lg border border-oassab-border bg-white px-4 py-3 text-base text-oassab-blue focus:border-oassab-orange focus:outline-none"
                           placeholder="Ex.: Termo de Fomento — Projeto X">
                </label>

                <label class="mt-4 block">
                    <span class="text-xs font-semibold uppercase tracking-wider text-oassab-blue">Slug (identificador interno)</span>
                    <input type="text" name="slug" value="{{ old('slug', $document->slug) }}" placeholder="deixe vazio para gerar a partir do nome"
                           class="mt-2 w-full rounded-lg border border-oassab-border bg-white px-4 py-3 text-sm text-oassab-blue focus:border-oassab-orange focus:outline-none">
                </label>

                <label class="mt-4 block">
                    <span class="text-xs font-semibold uppercase tracking-wider text-oassab-blue">Descrição do objeto</span>
                    <textarea name="description" rows="4" placeholder="Breve descrição exibida no card do portal"
                              class="mt-2 w-full rounded-lg border border-oassab-border bg-white px-4 py-3 text-sm text-oassab-blue focus:border-oassab-orange focus:outline-none">{{ old('description', $document->description) }}</textarea>
                </label>
            </div>

            <div class="rounded-2xl border border-oassab-border bg-white p-6 shadow-sm">
                <p class="mb-4 text-xs font-semibold uppercase tracking-wider text-oassab-blue">Detalhes da parceria</p>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="text-xs font-medium text-oassab-gray">Nº do processo</span>
                        <input type="text" name="processo" value="{{ old('processo', $document->processo) }}"
                               class="mt-1 w-full rounded-lg border border-oassab-border bg-white px-3 py-2 text-sm text-oassab-blue focus:border-oassab-orange focus:outline-none"
                               placeholder="Ex.: 12345/2024">
                    </label>

                    <label class="block">
                        <span class="text-xs font-medium text-oassab-gray">Valor global</span>
                        <input type="text" name="valor_global" value="{{ old('valor_global', $document->valor_global) }}"
                               class="mt-1 w-full rounded-lg border border-oassab-border bg-white px-3 py-2 text-sm text-oassab-blue focus:border-oassab-orange focus:outline-none"
                               placeholder="Ex.: R$ 1.500.000,00">
                    </label>
                </div>
            </div>
        </div>

        <aside class="space-y-6">
            <div class="rounded-2xl border border-oassab-border bg-white p-6 shadow-sm">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-oassab-blue">Publicação</p>

                <label class="block">
                    <span class="text-xs font-medium text-oassab-gray">Ano (exibição)</span>
                    <input type="text" name="year" maxlength="4" pattern="\d{4}"
                           value="{{ old('year', $document->year) }}"
                           class="mt-1 w-full rounded-lg border border-oassab-border bg-white px-3 py-2 text-sm text-oassab-blue focus:border-oassab-orange focus:outline-none"
                           placeholder="2024">
                </label>

                <label class="mt-4 block">
                    <span class="text-xs font-medium text-oassab-gray">Ordem de exibição</span>
                    <input type="number" name="sort_order" min="0" max="9999"
                           value="{{ old('sort_order', $document->sort_order ?? 0) }}"
                           class="mt-1 w-full rounded-lg border border-oassab-border bg-white px-3 py-2 text-sm text-oassab-blue focus:border-oassab-orange focus:outline-none">
                    <span class="mt-1 block text-xs text-oassab-gray">Menor número aparece primeiro.</span>
                </label>

                <label class="mt-4 flex items-center gap-2 text-sm text-oassab-blue">
                    <input type="hidden" name="is_published" value="0">
                    <input type="checkbox" name="is_published" value="1"
                           @checked(old('is_published', $document->is_published))
                           class="h-4 w-4 rounded border-oassab-border text-oassab-orange focus:ring-oassab-orange">
                    Publicado (visível no site)
                </label>
            </div>

            <div class="rounded-2xl border border-oassab-border bg-white p-6 shadow-sm">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-oassab-blue">Arquivo PDF</p>

                @if ($isEditing && $document->file_path)
                    <p class="mb-3 text-sm text-oassab-gray">
                        Arquivo atual:
                        <a href="{{ $document->file_path }}" target="_blank" rel="noopener" class="font-semibold text-oassab-orange hover:underline">
                            {{ $document->original_filename ?: 'Ver PDF' }}
                        </a>
                    </p>
                @endif

                <input type="file" name="file" accept="application/pdf"
                       class="block w-full text-xs text-oassab-gray file:mr-3 file:rounded-full file:border-0 file:bg-oassab-blue file:px-4 file:py-2 file:text-xs file:font-semibold file:uppercase file:tracking-wider file:text-white hover:file:bg-oassab-orange">
                <p class="mt-2 text-xs text-oassab-gray">
                    @if ($isEditing)
                        Envie um novo PDF apenas se quiser substituir o atual. PDF — até 20 MB.
                    @else
                        PDF obrigatório — até 20 MB.
                    @endif
                </p>
            </div>

            <div class="flex flex-col gap-2">
                <button type="submit" class="btn-primary justify-center">
                    {{ $isEditing ? 'Salvar alterações' : 'Criar documento' }}
                </button>
                <a href="{{ route('admin.transparency-documents.index') }}"
                   class="text-center text-xs font-semibold uppercase tracking-wider text-oassab-gray hover:text-oassab-orange">
                    Cancelar
                </a>
            </div>
        </aside>
    </form>
@endsection
