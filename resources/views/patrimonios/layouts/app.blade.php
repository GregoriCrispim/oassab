@php
    $current = trim(request()->path(), '/');
    $user = auth()->user();
    $sidebar = [
        ['label' => 'Dashboard', 'href' => route('patrimonios.dashboard'), 'active' => $current === 'patrimonios/dashboard', 'icon' => 'speedometer2', 'show' => true],
        ['label' => 'Patrimônios', 'href' => route('patrimonios.patrimonios.index'), 'active' => str_starts_with($current, 'patrimonios/patrimonios'), 'icon' => 'box', 'show' => true],
        ['label' => 'Categorias', 'href' => route('patrimonios.categorias.index'), 'active' => str_starts_with($current, 'patrimonios/categorias'), 'icon' => 'tags', 'show' => $user->isPatrimonioAdmin()],
        ['label' => 'Manutenções', 'href' => route('patrimonios.manutencoes.index'), 'active' => str_starts_with($current, 'patrimonios/manutencoes'), 'icon' => 'wrench', 'show' => true],
        ['label' => 'Orçamentos', 'href' => route('patrimonios.orcamentos.index'), 'active' => str_starts_with($current, 'patrimonios/orcamentos'), 'icon' => 'cart3', 'show' => true],
        ['label' => 'QR Scanner', 'href' => route('patrimonios.qr-scanner'), 'active' => $current === 'patrimonios/qr-scanner', 'icon' => 'qr-code-scan', 'show' => true],
        ['label' => 'Logs', 'href' => route('patrimonios.logs.index'), 'active' => str_starts_with($current, 'patrimonios/logs'), 'icon' => 'clipboard-data', 'show' => $user->isPatrimonioAdmin()],
        ['label' => 'Usuários', 'href' => route('patrimonios.usuarios.index'), 'active' => str_starts_with($current, 'patrimonios/usuarios'), 'icon' => 'people', 'show' => $user->isPatrimonioAdmin()],
    ];
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="/images/favicon-32x32.png" sizes="32x32">
    <title>@yield('title', 'Patrimônios') — OASSAB</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-oassab-cream">
    <div class="flex min-h-screen">
        <aside class="hidden w-64 shrink-0 flex-col bg-oassab-blue-dark text-white md:flex">
            <div class="flex h-20 items-center justify-center border-b border-white/10 px-6">
                <a href="{{ route('patrimonios.dashboard') }}" class="flex items-center gap-3">
                    <img src="/images/icone.png" alt="OASSAB" class="h-10 w-auto">
                    <span class="text-sm font-semibold uppercase tracking-[0.2em] text-oassab-orange">Patrimônio</span>
                </a>
            </div>
            <nav class="flex-1 space-y-1 p-4 text-sm">
                @foreach ($sidebar as $item)
                    @if ($item['show'])
                        <a href="{{ $item['href'] }}"
                           class="flex items-center gap-3 rounded-lg px-3 py-2.5 font-medium transition {{ $item['active'] ? 'bg-oassab-orange text-white hover:text-white' : 'text-white/80 hover:bg-white/5 hover:text-white' }}">
                            <i class="bi bi-{{ $item['icon'] }} text-lg"></i>
                            {{ $item['label'] }}
                        </a>
                    @endif
                @endforeach
            </nav>
            <div class="border-t border-white/10 p-4 space-y-2">
                @if ($user->is_admin)
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white/60 transition hover:text-white">
                        <i class="bi bi-grid"></i> Painel CMS
                    </a>
                @endif
                <a href="{{ url('/') }}" target="_blank" rel="noopener" class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white/60 transition hover:text-white">
                    <i class="bi bi-box-arrow-up-right"></i> Site público
                </a>
            </div>
        </aside>

        <div class="flex flex-1 flex-col">
            <header class="flex h-20 items-center justify-between border-b border-oassab-border bg-white px-6 shadow-sm">
                <div>
                    <h1 class="font-heading text-xl font-bold text-oassab-blue">@yield('title', 'Patrimônios')</h1>
                    @hasSection('subtitle')
                        <p class="text-sm text-oassab-gray">@yield('subtitle')</p>
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    <div class="hidden text-right md:block">
                        <p class="text-xs uppercase tracking-wider text-oassab-gray">Logado como</p>
                        <p class="text-sm font-semibold text-oassab-blue">{{ $user->name }}</p>
                        <p class="text-xs text-oassab-gray">{{ $user->patrimonioRoleLabel() }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg border border-oassab-border px-4 py-2 text-sm font-semibold text-oassab-blue transition hover:bg-oassab-cream">
                            Sair
                        </button>
                    </form>
                </div>
            </header>

            <main class="flex-1 p-6">
                @if (session('status') || request('status'))
                    <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                        {{ session('status') ?? request('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
    @include('patrimonios.components.qrcode-modal')
    @include('patrimonios.components.form-modal')
    @include('patrimonios.components.delete-confirm-modal')
</body>
</html>
