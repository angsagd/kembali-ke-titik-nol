<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" type="image/png" sizes="48x48" href="/images/icon/favicon48.png">
<link rel="icon" type="image/png" sizes="96x96" href="/images/icon/favicon96.png">
<link rel="icon" type="image/png" sizes="192x192" href="/images/icon/favicon192.png">
<link rel="apple-touch-icon" sizes="192x192" href="/images/icon/favicon192.png">
<link rel="manifest" href="/site.webmanifest">

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
