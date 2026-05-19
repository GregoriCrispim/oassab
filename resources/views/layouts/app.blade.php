<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('description', 'Obras de Assistência e de Serviço Social da Arquidiocese de Brasília — fé que se transforma em ação há mais de 60 anos.')">

    <link rel="icon" href="/images/favicon-32x32.png" sizes="32x32">
    <link rel="icon" href="/images/favicon-192x192.png" sizes="192x192">
    <link rel="apple-touch-icon" href="/images/favicon-180x180.png">

    <title>@yield('title', 'OASSAB') — OASSAB</title>

    @stack('head')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col">
    <x-site-header />

    <main class="flex-1">
        @yield('content')
    </main>

    <x-site-footer />
</body>
</html>
