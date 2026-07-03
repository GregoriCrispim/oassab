@extends('patrimonios.layouts.app')

@section('title', 'Categorias')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-oassab-gray">Categorias de patrimônio com campos customizados</p>
        @can('create', App\Models\PatrimonioCategoria::class)
            <x-patrimonios.form-modal-trigger :url="route('patrimonios.categorias.create')" title="Nova Categoria" class="w-full sm:w-auto">
                Nova Categoria
            </x-patrimonios.form-modal-trigger>
        @endcan
    </div>

    <x-patrimonios.responsive-table>
        <table>
            <thead class="bg-oassab-cream">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-oassab-blue">Nome</th>
                    <th class="px-4 py-3 text-left font-semibold text-oassab-blue">Depreciação</th>
                    <th class="px-4 py-3 text-left font-semibold text-oassab-blue">Patrimônios</th>
                    <th class="px-4 py-3 text-left font-semibold text-oassab-blue">Status</th>
                    <th class="px-4 py-3 text-right font-semibold text-oassab-blue">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-oassab-border">
                @forelse ($categorias as $cat)
                    <tr>
                        <td data-label="Nome" class="px-4 py-3">
                            <span class="inline-flex items-center gap-2">
                                <i class="{{ $cat->iconeBootstrap() }} text-lg" style="color: {{ $cat->cor }}"></i>
                                {{ $cat->nome }}
                            </span>
                        </td>
                        <td data-label="Depreciação" class="px-4 py-3">{{ $cat->indice_depreciacao_padrao }}%/ano</td>
                        <td data-label="Patrimônios" class="px-4 py-3">{{ $cat->patrimonios_count }}</td>
                        <td data-label="Status" class="px-4 py-3">{{ $cat->ativo ? 'Ativa' : 'Inativa' }}</td>
                        <td data-label="Ações" class="patrimonio-table__actions px-4 py-3 text-right">
                            @can('update', $cat)
                                <x-patrimonios.form-modal-trigger :url="route('patrimonios.categorias.edit', $cat)" title="Editar Categoria" variant="link">
                                    Editar
                                </x-patrimonios.form-modal-trigger>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" data-label="" class="patrimonio-table__empty px-4 py-8 text-center text-oassab-gray">Nenhuma categoria cadastrada.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-patrimonios.responsive-table>
    <x-pagination :paginator="$categorias" />
@endsection
