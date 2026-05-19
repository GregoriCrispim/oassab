@php
    $current = trim(request()->path(), '/');
    $sidebar = [
        ['label' => 'Dashboard', 'href' => route('admin.dashboard'), 'active' => $current === 'admin', 'icon' => 'home'],
        ['label' => 'Posts', 'href' => route('admin.posts.index'), 'active' => str_starts_with($current, 'admin/posts'), 'icon' => 'doc'],
        ['label' => 'Transparência', 'href' => route('admin.transparency-documents.index'), 'active' => str_starts_with($current, 'admin/transparency-documents'), 'icon' => 'pdf'],
        ['label' => 'Editais', 'href' => route('admin.editais.index'), 'active' => str_starts_with($current, 'admin/editais'), 'icon' => 'edital'],
        ['label' => 'Meu perfil', 'href' => route('admin.profile.edit'), 'active' => str_starts_with($current, 'admin/profile'), 'icon' => 'user'],
    ];
    $icons = [
        'home' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10a1 1 0 0 0 1 1h4v-7h4v7h4a1 1 0 0 0 1-1V10"/>',
        'doc'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M14 3v6h6M9 14h6M9 18h6"/>',
        'pdf'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M14 3v6h6M9 14h6M9 18h4"/>',
        'edital' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2"/>',
        'user' => '<circle cx="12" cy="8" r="4" stroke-linecap="round" stroke-linejoin="round"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 21c0-4 4-7 8-7s8 3 8 7"/>',
    ];
@endphp

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" href="/images/favicon-32x32.png" sizes="32x32">

    <title>@yield('title', 'Painel') — OASSAB Admin</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>
<body class="min-h-screen bg-oassab-cream">
    <div class="flex min-h-screen">
        <aside class="hidden w-64 shrink-0 flex-col bg-oassab-blue-dark text-white md:flex">
            <div class="flex h-20 items-center justify-center border-b border-white/10 px-6">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    <img src="/images/icone.png" alt="OASSAB" class="h-10 w-auto">
                    <span class="text-sm font-semibold uppercase tracking-[0.2em] text-oassab-orange">Admin</span>
                </a>
            </div>

            <nav class="flex-1 space-y-1 p-4 text-sm">
                @foreach ($sidebar as $item)
                    <a href="{{ $item['href'] }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2.5 font-medium transition {{ $item['active'] ? 'bg-oassab-orange text-white' : 'text-white/80 hover:bg-white/5 hover:text-white' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="h-5 w-5">
                            {!! $icons[$item['icon']] !!}
                        </svg>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="border-t border-white/10 p-4">
                <a href="{{ url('/') }}" target="_blank" rel="noopener"
                   class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white/60 transition hover:text-white">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M14 3h7v7M21 3l-9 9M5 5h6V3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-6h-2v6H5z"/></svg>
                    Ver site público
                </a>
            </div>
        </aside>

        <div class="flex flex-1 flex-col">
            <header class="flex h-20 items-center justify-between border-b border-oassab-border bg-white px-6 shadow-sm">
                <div>
                    <h1 class="font-heading text-xl font-bold text-oassab-blue">@yield('title', 'Painel')</h1>
                    @hasSection('subtitle')
                        <p class="text-sm text-oassab-gray">@yield('subtitle')</p>
                    @endif
                </div>

                <div class="flex items-center gap-3">
                    <div class="hidden text-right md:block">
                        <p class="text-xs uppercase tracking-wider text-oassab-gray">Logado como</p>
                        <p class="text-sm font-semibold text-oassab-blue">{{ auth()->user()->name }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-full border border-oassab-blue/20 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-oassab-blue transition hover:border-oassab-orange hover:bg-oassab-orange hover:text-white">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M16 17l5-5-5-5M21 12H9M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/></svg>
                            Sair
                        </button>
                    </form>
                </div>
            </header>

            <main class="flex-1 px-6 py-8">
                @if (session('status'))
                    <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-5 py-3 text-sm text-green-800">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any() && ! request()->routeIs(['admin.posts.*', 'admin.transparency-documents.*', 'admin.editais.*']))
                    @if (! request()->routeIs(['admin.posts.*', 'admin.transparency-documents.*', 'admin.editais.*']))
                        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-3 text-sm text-red-800">
                            <p class="mb-1 font-semibold">Corrija os erros abaixo:</p>
                            <ul class="list-disc space-y-1 pl-5">
                                @foreach ($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
