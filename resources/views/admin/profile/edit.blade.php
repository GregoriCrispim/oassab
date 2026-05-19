@extends('admin.layouts.admin')

@section('title', 'Meu perfil')
@section('subtitle', 'Atualize seus dados de acesso')

@section('content')
    <form method="POST" action="{{ route('admin.profile.update') }}" class="grid gap-6 lg:grid-cols-2">
        @csrf
        @method('PUT')

        <div class="rounded-2xl border border-oassab-border bg-white p-6 shadow-sm">
            <h2 class="mb-4 font-heading text-base font-semibold text-oassab-blue">Informações da conta</h2>

            <label class="block">
                <span class="text-xs font-semibold uppercase tracking-wider text-oassab-blue">Nome</span>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="mt-2 w-full rounded-lg border border-oassab-border bg-white px-4 py-3 text-sm text-oassab-blue focus:border-oassab-orange focus:outline-none">
            </label>

            <label class="mt-4 block">
                <span class="text-xs font-semibold uppercase tracking-wider text-oassab-blue">E-mail</span>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="mt-2 w-full rounded-lg border border-oassab-border bg-white px-4 py-3 text-sm text-oassab-blue focus:border-oassab-orange focus:outline-none">
            </label>
        </div>

        <div class="rounded-2xl border border-oassab-border bg-white p-6 shadow-sm">
            <h2 class="mb-4 font-heading text-base font-semibold text-oassab-blue">Alterar senha</h2>
            <p class="mb-4 text-xs text-oassab-gray">Deixe os campos em branco para manter a senha atual.</p>

            <label class="block">
                <span class="text-xs font-semibold uppercase tracking-wider text-oassab-blue">Senha atual</span>
                <input type="password" name="current_password" autocomplete="current-password"
                       class="mt-2 w-full rounded-lg border border-oassab-border bg-white px-4 py-3 text-sm text-oassab-blue focus:border-oassab-orange focus:outline-none">
            </label>

            <label class="mt-4 block">
                <span class="text-xs font-semibold uppercase tracking-wider text-oassab-blue">Nova senha</span>
                <input type="password" name="password" autocomplete="new-password"
                       class="mt-2 w-full rounded-lg border border-oassab-border bg-white px-4 py-3 text-sm text-oassab-blue focus:border-oassab-orange focus:outline-none">
            </label>

            <label class="mt-4 block">
                <span class="text-xs font-semibold uppercase tracking-wider text-oassab-blue">Confirmar nova senha</span>
                <input type="password" name="password_confirmation" autocomplete="new-password"
                       class="mt-2 w-full rounded-lg border border-oassab-border bg-white px-4 py-3 text-sm text-oassab-blue focus:border-oassab-orange focus:outline-none">
            </label>
        </div>

        <div class="lg:col-span-2">
            <button type="submit" class="btn-primary">Salvar alterações</button>
        </div>
    </form>
@endsection
