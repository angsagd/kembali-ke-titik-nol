<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404: Koordinat Tidak Ditemukan - Geodesi 96</title>

    <link rel="shortcut icon" href="/favicon.ico">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/png" sizes="48x48" href="/images/icon/favicon48.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/images/icon/favicon96.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/images/icon/favicon192.png">
    <link rel="apple-touch-icon" sizes="192x192" href="/images/icon/favicon192.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|inter:400,500,600,700|jetbrains-mono:500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-ktn-surface text-ktn-ink font-sans antialiased min-h-screen flex items-center justify-center relative overflow-x-hidden overflow-y-auto">
    <!-- Topographic Background -->
    <div class="hero-kontur absolute inset-0 opacity-30 mix-blend-multiply pointer-events-none z-0"></div>
    <div class="absolute inset-0 bg-ktn-surface/60 z-0"></div>

    <!-- Decorative Corner Crosshairs -->
    <div class="absolute top-8 left-8 w-4 h-4 crosshair-marker crosshair-tl z-10 hidden md:block"></div>
    <div class="absolute top-8 right-8 w-4 h-4 crosshair-marker crosshair-tr z-10 hidden md:block"></div>
    <div class="absolute bottom-8 left-8 w-4 h-4 crosshair-marker crosshair-bl z-10 hidden md:block"></div>
    <div class="absolute bottom-8 right-8 w-4 h-4 crosshair-marker crosshair-br z-10 hidden md:block"></div>

    <main class="relative z-10 w-full max-w-7xl mx-auto px-6 py-16 sm:px-8 lg:px-12">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-20 lg:gap-32 items-center">
            <!-- Image Column -->
            <div class="flex justify-center lg:justify-start relative">
                <!-- Structural backdrop for the image -->
                <div class="absolute inset-0 bg-ktn-sage/10 rounded-full blur-3xl filter transform scale-75 opacity-50"></div>
                <img
                    src="{{ asset('images/errors/404.png') }}"
                    alt="Ilustrasi 404 Geodesi 96"
                    class="relative z-10 w-full max-w-[380px] sm:max-w-[500px] lg:max-w-[650px] h-auto object-contain drop-shadow-2xl animate-float"
                >
            </div>

            <!-- Text Content Column -->
            <div class="flex flex-col items-center lg:items-start text-center lg:text-left space-y-6 relative">
                <!-- Technical Accent -->
                <div class="flex items-center gap-2 text-ktn-sage mb-2">
                    <svg class="size-4 fill-current" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="3" />
                        <path d="M12 2v4M12 18v4M2 12h4M18 12h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    </svg>
                    <span class="font-mono text-xs font-semibold uppercase tracking-[0.22em]">ERR_NOT_FOUND</span>
                </div>

                <!-- Headline -->
                <h1 class="font-display text-4xl font-extrabold leading-tight tracking-tight text-ktn-forest sm:text-5xl lg:text-6xl">
                    404: Koordinat Tidak Ditemukan
                </h1>

                <!-- Hairline Divider -->
                <div class="w-16 h-px bg-ktn-sage/30 my-4 lg:my-6"></div>

                <!-- Description -->
                <p class="font-sans text-base leading-8 text-ktn-muted sm:text-lg max-w-md">
                    Sepertinya Anda telah melangkah keluar dari batas peta. Posisi titik yang Anda cari tidak ada di koordinat ini. Silakan kembali ke beranda atau ke halaman sebelumnya.
                </p>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row gap-4 mt-8 w-full sm:w-auto">
                    <a href="{{ auth()->check() ? route('dashboard') : route('home') }}" class="inline-flex items-center justify-center px-6 py-3 bg-ktn-forest text-white font-mono text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-ktn-forest-strong transition-all duration-200 shadow-md">
                        Kembali ke Titik Nol
                    </a>
                    <button onclick="window.history.back()" class="inline-flex items-center justify-center px-6 py-3 border border-ktn-forest text-ktn-forest font-mono text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-ktn-forest hover:text-white transition-all duration-200">
                        Kembali ke Titik Terakhir
                    </button>
                </div>

                <!-- Coordinate Metadata Accent -->
                <div class="absolute -left-12 top-0 bottom-0 flex-col justify-between items-center hidden xl:flex py-4">
                    <div class="h-1/3 w-px bg-ktn-sage/20"></div>
                    <span class="font-mono text-xs text-ktn-sage/40 transform -rotate-90 whitespace-nowrap">LAT: -7.7681° LON: 110.3734°</span>
                    <div class="h-1/3 w-px bg-ktn-sage/20"></div>
                </div>
            </div>
        </div>
    </main>

    <style>
        .crosshair-marker {
            position: absolute;
        }
        .crosshair-marker::before, .crosshair-marker::after {
            content: '';
            position: absolute;
            background-color: var(--color-ktn-sage);
            opacity: 0.3;
        }
        .crosshair-tl::before { top: 0; left: -10px; width: 20px; height: 1px; }
        .crosshair-tl::after { top: -10px; left: 0; width: 1px; height: 20px; }
        
        .crosshair-tr::before { top: 0; right: -10px; width: 20px; height: 1px; }
        .crosshair-tr::after { top: -10px; right: 0; width: 1px; height: 20px; }
        
        .crosshair-bl::before { bottom: 0; left: -10px; width: 20px; height: 1px; }
        .crosshair-bl::after { bottom: -10px; left: 0; width: 1px; height: 20px; }
        
        .crosshair-br::before { bottom: 0; right: -10px; width: 20px; height: 1px; }
        .crosshair-br::after { bottom: -10px; right: 0; width: 1px; height: 20px; }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-12px); }
            100% { transform: translateY(0px); }
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
    </style>
</body>
</html>
