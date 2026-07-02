@extends('patrimonios.layouts.app')

@section('title', 'Categorias')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <p class="text-sm text-oassab-gray">Categorias de patrimônio com campos customizados</p>
        @can('create', App\Models\PatrimonioCategoria::class)
            <x-patrimonios.form-modal-trigger :url="route('patrimonios.categorias.create')" title="Nova Categoria">
                Nova Categoria
            </x-patrimonios.form-modal-trigger>
        @endcan
    </div>

    <div class="overflow-hidden rounded-xl border border-oassab-border bg-white shadow-sm">
        <table class="min-w-full divide-y divide-oassab-border text-sm">
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
                @foreach ($categorias as $cat)
                    <tr>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-2">
                                <i class="{{ $cat->icone }} text-lg" style="color: {{ $cat->cor }}"></i>
                                {{ $cat->nome }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ $cat->indice_depreciacao_padrao }}%/ano</td>
                        <td class="px-4 py-3">{{ $cat->patrimonios_count }}</td>
                        <td class="px-4 py-3">{{ $cat->ativo ? 'Ativa' : 'Inativa' }}</td>
                        <td class="px-4 py-3 text-right">
                            @can('update', $cat)
                                <x-patrimonios.form-modal-trigger :url="route('patrimonios.categorias.edit', $cat)" title="Editar Categoria" variant="link">
                                    Editar
                                </x-patrimonios.form-modal-trigger>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <x-pagination :paginator="$categorias" />
@endsection
