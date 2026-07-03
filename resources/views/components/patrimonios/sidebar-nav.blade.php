@props([
    'sidebar',
    'user',
    'onNavigate' => false,
])

<nav {{ $attributes->merge(['class' => 'flex-1 space-y-1 p-4 text-sm']) }}>
    @foreach ($sidebar as $item)
        @if ($item['show'])
            <a href="{{ $item['href'] }}"
               @if ($onNavigate) data-patrimonio-sidebar-close @endif
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 font-medium transition {{ $item['active'] ? 'bg-oassab-orange text-white hover:text-white' : 'text-white/80 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-{{ $item['icon'] }} text-lg"></i>
                {{ $item['label'] }}
            </a>
        @endif
    @endforeach
</nav>

<div class="border-t border-white/10 p-4 space-y-2">
    @if ($user->is_admin)
        <a href="{{ route('admin.dashboard') }}"
           @if ($onNavigate) data-patrimonio-sidebar-close @endif
           class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white/60 transition hover:text-white">
            <i class="bi bi-grid"></i> Painel CMS
        </a>
    @endif
    <a href="{{ url('/') }}" target="_blank" rel="noopener"
       class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white/60 transition hover:text-white">
        <i class="bi bi-box-arrow-up-right"></i> Site público
    </a>
</div>
