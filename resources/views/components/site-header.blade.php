@php
    $current = trim(request()->path(), '/');
    $nav = [
        ['label' => 'Home', 'href' => url('/'), 'active' => $current === ''],
        ['label' => 'Quem somos', 'href' => url('/quem-somos'), 'active' => str_starts_with($current, 'quem-somos')],
        ['label' => 'Projetos', 'href' => url('/projetos'), 'active' => str_starts_with($current, 'projetos')],
        ['label' => 'Portal Transparência', 'href' => url('/transparencia'), 'active' => str_starts_with($current, 'transparencia')],
        ['label' => 'Contato', 'href' => url('/contato'), 'active' => str_starts_with($current, 'contato')],
    ];
@endphp

<header class="sticky top-0 z-40 border-b border-oassab-border bg-white/95 backdrop-blur" id="masthead">
    <div class="container-oassab grid grid-cols-2 items-center gap-4 py-4 md:grid-cols-[auto,1fr,auto] md:gap-10 md:py-5">
        <a href="{{ url('/') }}" class="flex items-center" aria-label="OASSAB">
            <img src="/images/logo-com-texto.png"
                 alt="OASSAB — Obras de Assistência e Serviço Social da Arquidiocese de Brasília"
                 width="240" height="64"
                 fetchpriority="high"
                 decoding="async"
                 class="h-12 w-auto md:h-14 lg:h-16">
        </a>

        <nav class="hidden md:block" aria-label="Menu principal">
            <ul class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-[13px] font-semibold uppercase tracking-wider">
                @foreach ($nav as $item)
                    <li>
                        <a href="{{ $item['href'] }}"
                           class="relative py-2 transition-colors {{ $item['active'] ? 'text-oassab-orange' : 'text-oassab-blue hover:text-oassab-orange' }}">
                            {{ $item['label'] }}
                            @if ($item['active'])
                                <span class="absolute -bottom-1 left-0 h-0.5 w-full bg-oassab-orange"></span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="flex items-center justify-end gap-3">
            <a href="https://www.instagram.com/oassabdf/" target="_blank" rel="noopener" aria-label="Instagram"
               class="flex h-9 w-9 items-center justify-center rounded-full bg-oassab-blue text-white transition hover:bg-oassab-orange hover:text-white">
                <svg viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4" aria-hidden="true">
                    <path d="M12 2.16c3.2 0 3.58.01 4.85.07 1.17.05 1.8.25 2.23.41.56.21.96.47 1.38.89.42.42.68.82.89 1.38.16.43.36 1.06.41 2.23.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.23-.21.56-.47.96-.89 1.38-.42.42-.82.68-1.38.89-.43.16-1.06.36-2.23.41-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-1.17-.05-1.8-.25-2.23-.41a3.7 3.7 0 0 1-1.38-.89 3.7 3.7 0 0 1-.89-1.38c-.16-.43-.36-1.06-.41-2.23C2.17 15.58 2.16 15.2 2.16 12s.01-3.58.07-4.85c.05-1.17.25-1.8.41-2.23.21-.56.47-.96.89-1.38.42-.42.82-.68 1.38-.89.43-.16 1.06-.36 2.23-.41C8.42 2.17 8.8 2.16 12 2.16M12 0C8.74 0 8.33.01 7.05.07 5.78.13 4.91.33 4.15.63a5.86 5.86 0 0 0-2.13 1.39 5.86 5.86 0 0 0-1.39 2.13C.33 4.91.13 5.78.07 7.05.01 8.33 0 8.74 0 12s.01 3.67.07 4.95c.06 1.27.26 2.14.56 2.9a5.86 5.86 0 0 0 1.39 2.13 5.86 5.86 0 0 0 2.13 1.39c.76.3 1.63.5 2.9.56C8.33 23.99 8.74 24 12 24s3.67-.01 4.95-.07c1.27-.06 2.14-.26 2.9-.56a5.86 5.86 0 0 0 2.13-1.39 5.86 5.86 0 0 0 1.39-2.13c.3-.76.5-1.63.56-2.9.06-1.28.07-1.69.07-4.95s-.01-3.67-.07-4.95c-.06-1.27-.26-2.14-.56-2.9a5.86 5.86 0 0 0-1.39-2.13A5.86 5.86 0 0 0 19.85.63c-.76-.3-1.63-.5-2.9-.56C15.67.01 15.26 0 12 0zm0 5.84A6.16 6.16 0 1 0 12 18.16 6.16 6.16 0 0 0 12 5.84zm0 10.16A4 4 0 1 1 12 8a4 4 0 0 1 0 8zm6.41-11.84a1.44 1.44 0 1 0 0 2.88 1.44 1.44 0 0 0 0-2.88z"/>
                </svg>
            </a>
            <a href="https://wa.me/556132238431" target="_blank" rel="noopener" aria-label="WhatsApp"
               class="flex h-9 w-9 items-center justify-center rounded-full bg-oassab-blue text-white transition hover:bg-oassab-orange hover:text-white">
                <svg viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4" aria-hidden="true">
                    <path d="M17.47 14.38c-.3-.15-1.74-.86-2.01-.96-.27-.1-.46-.15-.66.15-.2.3-.76.96-.93 1.16-.17.2-.34.22-.63.07-.3-.15-1.25-.46-2.39-1.46a8.96 8.96 0 0 1-1.65-2.06c-.17-.3-.02-.46.13-.6.13-.13.3-.34.45-.5.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.07-.15-.66-1.6-.91-2.18-.24-.57-.48-.49-.66-.5h-.56c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48 0 1.46 1.06 2.87 1.21 3.07.15.2 2.1 3.2 5.07 4.49.71.3 1.26.49 1.69.62.71.22 1.35.19 1.86.12.57-.08 1.74-.71 1.99-1.4.25-.69.25-1.28.17-1.4-.07-.13-.27-.2-.56-.35zM12.04 2C6.58 2 2.16 6.42 2.16 11.88c0 1.92.5 3.71 1.45 5.27L2 22l4.99-1.59a9.85 9.85 0 0 0 5.05 1.38h.01c5.46 0 9.88-4.42 9.88-9.89 0-2.64-1.03-5.13-2.9-7-1.86-1.87-4.34-2.9-6.99-2.9zm0 18c-1.62 0-3.21-.43-4.6-1.26l-.33-.2-3.42 1.09 1.11-3.32-.21-.34a8.13 8.13 0 0 1-1.26-4.34c0-4.49 3.66-8.14 8.14-8.14 2.18 0 4.22.85 5.76 2.39a8.07 8.07 0 0 1 2.39 5.75c.02 4.5-3.64 8.15-8.13 8.15z"/>
                </svg>
            </a>
            <button type="button" data-mobile-toggle
                    class="ml-1 inline-flex h-10 w-10 items-center justify-center rounded-md border border-oassab-blue/20 text-oassab-blue md:hidden"
                    aria-expanded="false" aria-controls="mobile-nav" aria-label="Abrir menu">
                <svg data-mobile-icon-open xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg data-mobile-icon-close hidden xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    <nav id="mobile-nav" data-mobile-nav hidden class="border-t border-oassab-border md:hidden" aria-label="Menu mobile">
        <ul class="container-oassab flex flex-col gap-1 py-3 text-sm font-semibold">
            @foreach ($nav as $item)
                <li>
                    <a href="{{ $item['href'] }}"
                       class="block rounded px-3 py-2 transition-colors {{ $item['active'] ? 'bg-oassab-orange/10 text-oassab-orange' : 'text-oassab-blue hover:bg-oassab-blue/5' }}">
                        {{ $item['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
</header>
