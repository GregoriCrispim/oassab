<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/images/favicon-32x32.png" sizes="32x32">
    <title>Login — OASSAB Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-oassab-blue-dark">
    <div class="absolute inset-0 -z-10">
        <img src="/images/hero-bg.jpg" alt="" aria-hidden="true" class="h-full w-full object-cover opacity-30">
        <div class="absolute inset-0 hero-overlay"></div>
    </div>

    <div class="flex min-h-screen items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            <div class="mb-8 flex justify-center">
                <a href="{{ url('/') }}" class="flex items-center gap-3 text-white">
                    <img src="/images/icone.png" alt="OASSAB" class="h-12 w-auto">
                    <span class="font-heading text-xl font-semibold">OASSAB Admin</span>
                </a>
            </div>

            <div class="rounded-3xl bg-white p-8 shadow-2xl">
                <h1 class="font-heading text-2xl font-bold text-oassab-blue">Acessar painel</h1>
                <p class="mt-1 text-sm text-oassab-gray">Informe suas credenciais de administrador.</p>

                @if ($errors->any())
                    <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login') }}" class="mt-6 space-y-5">
                    @csrf

                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-wider text-oassab-blue">E-mail</span>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                               class="mt-2 w-full rounded-xl border border-oassab-border bg-white px-4 py-3 text-sm text-oassab-blue shadow-sm focus:border-oassab-orange focus:outline-none focus:ring-2 focus:ring-oassab-orange/30">
                    </label>

                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-wider text-oassab-blue">Senha</span>
                        <input type="password" name="password" required
                               class="mt-2 w-full rounded-xl border border-oassab-border bg-white px-4 py-3 text-sm text-oassab-blue shadow-sm focus:border-oassab-orange focus:outline-none focus:ring-2 focus:ring-oassab-orange/30">
                    </label>

                    <label class="flex items-center gap-2 text-sm text-oassab-gray">
                        <input type="checkbox" name="remember" value="1"
                               class="h-4 w-4 rounded border-oassab-border text-oassab-orange focus:ring-oassab-orange">
                        Lembrar de mim
                    </label>

                    <button type="submit" class="btn-primary w-full justify-center">
                        Entrar
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 5l7 7-7 7"/></svg>
                    </button>
                </form>
            </div>

            <p class="mt-6 text-center text-xs text-white/70">
                <a href="{{ url('/') }}" class="hover:text-oassab-orange">&larr; voltar para o site</a>
            </p>
        </div>
    </div>
</body>
</html>
