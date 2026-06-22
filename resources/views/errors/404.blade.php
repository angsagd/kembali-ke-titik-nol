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
<body class="bg-ktn-surface text-ktn-ink font-sans antialiased">
    <div class="min-h-screen">
        <x-public-header />

        <main id="home" class="relative overflow-hidden bg-ktn-topo px-4 pb-16 pt-[105px] sm:px-6 sm:pb-20 lg:px-8">
            <div class="hero-kontur absolute inset-y-0 -left-10 -right-10 opacity-30 mix-blend-multiply"></div>
            <div class="absolute inset-0 bg-ktn-surface/55"></div>

            <section class="relative mx-auto max-w-5xl rounded-2xl border border-ktn-sage/20 bg-white/90 p-6 shadow-xl shadow-ktn-forest/10 backdrop-blur-sm sm:p-8 lg:p-10">
                <div class="grid items-center gap-8 lg:grid-cols-[0.9fr_1.1fr]">
                    <div class="relative mx-auto w-full max-w-xs">
                        <div class="absolute inset-0 rounded-full bg-ktn-gold/10 blur-2xl"></div>
                        <div class="relative overflow-hidden rounded-2xl border border-ktn-sage/20 bg-ktn-surface p-4">
                            <img
                                src="{{ asset('images/errors/404.png') }}"
                                alt="Ilustrasi 404 Geodesi 96"
                                class="w-full object-contain"
                            >
                        </div>
                    </div>

                    <div class="text-center lg:text-left">
                        <p class="font-mono text-xs font-semibold uppercase tracking-[0.22em] text-ktn-sage">ERR_NOT_FOUND</p>
                        <h1 class="mt-3 font-display text-4xl font-extrabold leading-tight tracking-tight text-ktn-forest sm:text-5xl">
                            404: Koordinat Tidak Ditemukan
                        </h1>
                        <p class="mt-5 text-base leading-8 text-ktn-muted sm:text-lg">
                            Sepertinya Anda telah melangkah keluar dari batas peta. Titik nol yang Anda cari tidak ada di koordinat ini.
                            Silakan kembali ke beranda atau ke halaman sebelumnya.
                        </p>

                        <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center lg:justify-start">
                            <a href="{{ route('home') }}" class="inline-flex items-center justify-center rounded-lg bg-ktn-forest px-6 py-3 text-sm font-bold text-white transition hover:bg-ktn-forest-strong">
                                Kembali ke Titik Nol
                            </a>
                            <button onclick="window.history.back()" class="inline-flex items-center justify-center rounded-lg border border-ktn-forest px-6 py-3 text-sm font-bold text-ktn-forest transition hover:bg-ktn-forest hover:text-white">
                                Peta Sebelumnya
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer id="kontak" class="scroll-mt-24 bg-ktn-forest px-4 py-12 text-white sm:px-6 lg:px-8">
            <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[1.2fr_0.8fr_1fr]">
                <div class="space-y-4">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                        <img src="{{ asset('images/icon/favicon96.png') }}" alt="Logo Geodesi 96" class="size-10 rounded-lg border border-white/20 bg-white object-contain p-1">
                        <span class="font-display text-xl font-extrabold">Geodesi 96</span>
                    </a>
                    <p class="max-w-sm text-sm leading-7 text-ktn-sage-light">Ngalibrasi 30 Taon Paseduluran - Alumni Teknik Geodesi UGM 1996.</p>
                    <p class="font-mono text-md font-semibold uppercase tracking-[0.18em] text-ktn-sage-light">23-24 Agustus 2026</p>
                    <p class="mt-6 font-mono text-xs text-ktn-sage-light">v.0.9.b</p>
                </div>

                <div>
                    <h3 class="font-mono text-xs font-semibold uppercase tracking-[0.2em] text-ktn-sage-light">Navigasi</h3>
                    <div class="mt-4 grid gap-3 text-sm font-semibold">
                        <a href="{{ route('home') }}#tentang" class="text-white transition hover:text-ktn-gold-light">Tentang</a>
                        <a href="{{ route('home') }}#rundown" class="text-white transition hover:text-ktn-gold-light">Rundown</a>
                        <a href="{{ route('home') }}#galeri" class="text-white transition hover:text-ktn-gold-light">Galeri</a>
                        <a href="{{ route('home') }}#berita" class="text-white transition hover:text-ktn-gold-light">Berita</a>
                        <a href="{{ route('home') }}#donatur" class="text-white transition hover:text-ktn-gold-light">Donatur</a>
                    </div>
                </div>

                <div>
                    <h3 class="font-mono text-xs font-semibold uppercase tracking-[0.2em] text-ktn-sage-light">Kontak Panitia</h3>
                    <p class="mt-4 text-sm leading-7 text-ktn-sage-light">Ikuti kabar terbaru reuni dan hubungi panitia untuk informasi teknis kegiatan.</p>

                    <div class="mt-5 grid gap-3">
                        <a
                            href="https://www.instagram.com/titiknol.tgd96"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-3 text-sm font-bold text-white transition hover:text-ktn-gold-light"
                            aria-label="Instagram titiknol.tgd96"
                        >
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <rect x="4" y="4" width="16" height="16" rx="4.5" stroke="currentColor" stroke-width="1.8" />
                                <circle cx="12" cy="12" r="3.5" stroke="currentColor" stroke-width="1.8" />
                                <circle cx="16.8" cy="7.2" r="1" fill="currentColor" />
                            </svg>
                            <span>titiknol.tgd96</span>
                        </a>

                        <a
                            href="https://wa.me/6281931720792?text=Halo%20panitia%20reuni%20tgd96"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-3 text-sm font-bold text-white transition hover:text-ktn-gold-light"
                            aria-label="WhatsApp Asih panitia reuni TGD96"
                        >
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M5.3 18.7 6.4 15A7.4 7.4 0 1 1 9 17.6l-3.7 1.1Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M9.4 8.7c.2-.5.4-.5.7-.5h.5c.2 0 .4.1.5.4l.6 1.4c.1.3 0 .5-.2.7l-.4.4c.7 1.2 1.6 2.1 2.8 2.8l.4-.4c.2-.2.4-.3.7-.2l1.4.6c.3.1.4.3.4.6v.5c0 .3 0 .5-.5.7-.5.2-1 .3-1.5.3-2.8 0-6.2-3.4-6.2-6.2 0-.5.1-1 .3-1.5Z" fill="currentColor" />
                            </svg>
                            <span>Asih</span>
                        </a>

                        <a
                            href="https://wa.me/6281286134887?text=Halo%20panitia%20reuni%20tgd96"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-3 text-sm font-bold text-white transition hover:text-ktn-gold-light"
                            aria-label="WhatsApp Iin panitia reuni TGD96"
                        >
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M5.3 18.7 6.4 15A7.4 7.4 0 1 1 9 17.6l-3.7 1.1Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M9.4 8.7c.2-.5.4-.5.7-.5h.5c.2 0 .4.1.5.4l.6 1.4c.1.3 0 .5-.2.7l-.4.4c.7 1.2 1.6 2.1 2.8 2.8l.4-.4c.2-.2.4-.3.7-.2l1.4.6c.3.1.4.3.4.6v.5c0 .3 0 .5-.5.7-.5.2-1 .3-1.5.3-2.8 0-6.2-3.4-6.2-6.2 0-.5.1-1 .3-1.5Z" fill="currentColor" />
                            </svg>
                            <span>Iin</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="mx-auto mt-10 flex max-w-7xl flex-col gap-3 border-t border-white/10 pt-6 font-mono text-xs font-semibold uppercase tracking-[0.16em] text-ktn-sage-light sm:flex-row sm:items-center sm:justify-between">
                <span>© 2026 Geodesi 96 · Kembali ke Titik Nol</span>
                <span>Reuni 30 Tahun Teknik Geodesi UGM 1996</span>
            </div>
        </footer>
    </div>
</body>
</html>
