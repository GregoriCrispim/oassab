@extends('patrimonios.layouts.app')

@section('title', 'Usuários Patrimoniais')

@section('content')
    <div class="mb-6 flex justify-end">
        <x-patrimonios.form-modal-trigger :url="route('patrimonios.usuarios.create')" title="Novo Usuário">
            Novo Usuário
        </x-patrimonios.form-modal-trigger>
    </div>

    <div class="overflow-hidden rounded-xl border border-oassab-border bg-white shadow-sm">
        <table class="min-w-full divide-y divide-oassab-border text-sm">
            <thead class="bg-oassab-cream">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-oassab-blue">Nome</th>
                    <th class="px-4 py-3 text-left font-semibold text-oassab-blue">E-mail</th>
                    <th class="px-4 py-3 text-left font-semibold text-oassab-blue">Papel</th>
                    <th class="px-4 py-3 text-right font-semibold text-oassab-blue">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-oassab-border">
                @foreach ($usuarios as $u)
                    <tr>
                        <td class="px-4 py-3">{{ $u->name }}</td>
                        <td class="px-4 py-3">{{ $u->email }}</td>
                        <td class="px-4 py-3">{{ $u->patrimonioRoleLabel() }}</td>
                        <td class="px-4 py-3 text-right">
                            <x-patrimonios.form-modal-trigger :url="route('patrimonios.usuarios.edit', $u)" title="Editar Usuário" variant="link">
                                Editar
                            </x-patrimonios.form-modal-trigger>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <x-pagination :paginator="$usuarios" />
@endsection
