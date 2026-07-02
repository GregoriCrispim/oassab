<form method="POST"
      action="{{ $usuario->exists ? route('patrimonios.usuarios.update', $usuario) : route('patrimonios.usuarios.store') }}"
      data-form-modal
      class="space-y-6">
    @csrf
    @if ($usuario->exists) @method('PUT') @endif

    <x-patrimonios.form-section title="Dados Pessoais" icon="person" subtitle="Informações de identificação do usuário.">
        <div class="space-y-5">
            <x-patrimonios.form-field label="Nome" name="name" required>
                <input type="text" name="name" id="name" value="{{ old('name', $usuario->name) }}" required class="form-input" placeholder="Nome completo">
            </x-patrimonios.form-field>

            <x-patrimonios.form-field label="E-mail" name="email" required>
                <input type="email" name="email" id="email" value="{{ old('email', $usuario->email) }}" required class="form-input" placeholder="usuario@exemplo.org">
            </x-patrimonios.form-field>
        </div>
    </x-patrimonios.form-section>

    <x-patrimonios.form-section
        title="Acesso e Permissões"
        icon="shield-lock"
        subtitle="{{ $usuario->exists ? 'Deixe a senha em branco para mantê-la inalterada.' : 'Defina a senha inicial de acesso.' }}"
    >
        <div class="space-y-5">
            <div class="form-grid">
                <x-patrimonios.form-field
                    label="Senha"
                    name="password"
                    :required="! $usuario->exists"
                    :hint="$usuario->exists ? 'Preencha apenas se desejar alterar a senha atual.' : null"
                >
                    <input type="password" name="password" id="password" {{ $usuario->exists ? '' : 'required' }} class="form-input" autocomplete="new-password">
                </x-patrimonios.form-field>

                <x-patrimonios.form-field label="Confirmar Senha" name="password_confirmation">
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-input" autocomplete="new-password">
                </x-patrimonios.form-field>
            </div>

            <x-patrimonios.form-field label="Papel Patrimonial" name="patrimonio_role" required>
                <select name="patrimonio_role" id="patrimonio_role" required class="form-input" {{ $usuario->is_admin ? 'disabled' : '' }}>
                    @foreach ($roles as $role)
                        <option value="{{ $role->value }}" @selected(old('patrimonio_role', $usuario->patrimonio_role?->value) === $role->value)>{{ $role->label() }}</option>
                    @endforeach
                </select>
                @if ($usuario->is_admin)
                    <input type="hidden" name="patrimonio_role" value="admin">
                    <p class="form-hint">Administradores CMS têm acesso total ao módulo patrimonial.</p>
                @endif
            </x-patrimonios.form-field>
        </div>
    </x-patrimonios.form-section>

    <x-patrimonios.form-actions />
</form>
