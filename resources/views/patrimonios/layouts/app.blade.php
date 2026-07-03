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
    <div id="patrimonio-sidebar-backdrop" class="fixed inset-0 z-40 hidden bg-oassab-blue-dark/60 backdrop-blur-sm md:hidden" aria-hidden="true"></div>

    <aside
        id="patrimonio-sidebar-drawer"
        class="fixed inset-y-0 left-0 z-50 flex w-72 max-w-[85vw] -translate-x-full flex-col bg-oassab-blue-dark text-white shadow-2xl transition-transform duration-300 ease-out md:hidden"
        aria-label="Menu de navegação"
        aria-hidden="true"
    >
        <div class="flex h-16 items-center justify-between border-b border-white/10 px-4">
            <a href="{{ route('patrimonios.dashboard') }}" class="flex items-center gap-2" data-patrimonio-sidebar-close>
                <img src="/images/icone.png" alt="OASSAB" class="h-9 w-auto">
                <span class="text-xs font-semibold uppercase tracking-[0.2em] text-oassab-orange">Patrimônio</span>
            </a>
            <button type="button" class="rounded-lg p-2 text-white/80 transition hover:bg-white/10 hover:text-white" data-patrimonio-sidebar-close aria-label="Fechar menu">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <x-patrimonios.sidebar-nav :sidebar="$sidebar" :user="$user" on-navigate class="overflow-y-auto" />
    </aside>

    <div class="flex min-h-screen">
        <aside class="hidden w-64 shrink-0 flex-col bg-oassab-blue-dark text-white md:flex">
            <div class="flex h-20 items-center justify-center border-b border-white/10 px-6">
                <a href="{{ route('patrimonios.dashboard') }}" class="flex items-center gap-3">
                    <img src="/images/icone.png" alt="OASSAB" class="h-10 w-auto">
                    <span class="text-sm font-semibold uppercase tracking-[0.2em] text-oassab-orange">Patrimônio</span>
                </a>
            </div>
            <x-patrimonios.sidebar-nav :sidebar="$sidebar" :user="$user" />
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex min-h-16 items-center justify-between gap-3 border-b border-oassab-border bg-white px-4 py-3 shadow-sm sm:px-6 md:h-20 md:py-0">
                <div class="flex min-w-0 items-center gap-3">
                    <button
                        type="button"
                        data-patrimonio-sidebar-toggle
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-oassab-border text-oassab-blue transition hover:bg-oassab-cream md:hidden"
                        aria-expanded="false"
                        aria-controls="patrimonio-sidebar-drawer"
                        aria-label="Abrir menu"
                    >
                        <i class="bi bi-list text-xl"></i>
                    </button>
                    <div class="min-w-0">
                        <h1 class="truncate font-heading text-lg font-bold text-oassab-blue sm:text-xl">@yield('title', 'Patrimônios')</h1>
                        @hasSection('subtitle')
                            <p class="truncate text-xs text-oassab-gray sm:text-sm">@yield('subtitle')</p>
                        @endif
                    </div>
                </div>
                <div class="flex shrink-0 items-center gap-2 sm:gap-3">
                    <div class="hidden text-right md:block">
                        <p class="text-xs uppercase tracking-wider text-oassab-gray">Logado como</p>
                        <p class="text-sm font-semibold text-oassab-blue">{{ $user->name }}</p>
                        <p class="text-xs text-oassab-gray">{{ $user->patrimonioRoleLabel() }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg border border-oassab-border px-3 py-2 text-xs font-semibold text-oassab-blue transition hover:bg-oassab-cream sm:px-4 sm:text-sm">
                            Sair
                        </button>
                    </form>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6">
                @if (session('status') || request('status'))
                    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 sm:mb-6">
                        {{ session('status') ?? request('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 sm:mb-6">
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
