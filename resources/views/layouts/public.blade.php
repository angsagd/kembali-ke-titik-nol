<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title ?? config('app.name', 'Geodesi 96') }}</title>

        <link rel="icon" type="image/png" sizes="48x48" href="/images/icon/favicon48.png">
        <link rel="icon" type="image/png" sizes="96x96" href="/images/icon/favicon96.png">
        <link rel="icon" type="image/png" sizes="192x192" href="/images/icon/favicon192.png">
        <link rel="apple-touch-icon" sizes="192x192" href="/images/icon/favicon192.png">
        <link rel="manifest" href="/site.webmanifest">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|inter:400,500,600,700|jetbrains-mono:500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-ktn-surface text-ktn-ink font-sans antialiased">
        {{ $slot }}

        @fluxScripts
    </body>
</html>
