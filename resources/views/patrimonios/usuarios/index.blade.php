@extends('patrimonios.layouts.app')

@section('title', 'Usuários Patrimoniais')

@section('content')
    <div class="mb-6 flex justify-stretch sm:justify-end">
        <x-patrimonios.form-modal-trigger :url="route('patrimonios.usuarios.create')" title="Novo Usuário" class="w-full sm:w-auto">
            Novo Usuário
        </x-patrimonios.form-modal-trigger>
    </div>

    <x-patrimonios.responsive-table>
        <table>
            <thead class="bg-oassab-cream">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-oassab-blue">Nome</th>
                    <th class="px-4 py-3 text-left font-semibold text-oassab-blue">E-mail</th>
                    <th class="px-4 py-3 text-left font-semibold text-oassab-blue">Papel</th>
                    <th class="px-4 py-3 text-right font-semibold text-oassab-blue">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-oassab-border">
                @forelse ($usuarios as $u)
                    <tr>
                        <td data-label="Nome" class="px-4 py-3">{{ $u->name }}</td>
                        <td data-label="E-mail" class="px-4 py-3 break-all">{{ $u->email }}</td>
                        <td data-label="Papel" class="px-4 py-3">{{ $u->patrimonioRoleLabel() }}</td>
                        <td data-label="Ações" class="patrimonio-table__actions px-4 py-3 text-right">
                            <x-patrimonios.form-modal-trigger :url="route('patrimonios.usuarios.edit', $u)" title="Editar Usuário" variant="link">
                                Editar
                            </x-patrimonios.form-modal-trigger>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" data-label="" class="patrimonio-table__empty px-4 py-8 text-center text-oassab-gray">Nenhum usuário encontrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-patrimonios.responsive-table>
    <x-pagination :paginator="$usuarios" />
@endsection
