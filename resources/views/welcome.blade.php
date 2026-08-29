<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @php
            $metaTitle = 'Portal Alumni Geodesi 96';
            $metaDescription = 'Jejak, cerita, dan perjalanan alumni Teknik Geodesi UGM Angkatan 1996 yang terus terhubung.';
            $metaUrl = 'https://geodesiugm96.web.id';
            $metaImage = 'https://geodesiugm96.web.id/images/icon/favicon512.png';
        @endphp
        <title>{{ $metaTitle }}</title>
        <meta name="description" content="{{ $metaDescription }}">
        <link rel="canonical" href="{{ $metaUrl }}">
        <meta property="og:locale" content="id_ID">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="Portal Alumni Geodesi 96">
        <meta property="og:title" content="{{ $metaTitle }}">
        <meta property="og:description" content="{{ $metaDescription }}">
        <meta property="og:url" content="{{ $metaUrl }}">
        <meta property="og:image" content="{{ $metaImage }}">
        <meta property="og:image:type" content="image/png">
        <meta property="og:image:width" content="512">
        <meta property="og:image:height" content="512">
        <meta property="og:image:alt" content="Logo Portal Alumni Geodesi 96">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $metaTitle }}">
        <meta name="twitter:description" content="{{ $metaDescription }}">
        <meta name="twitter:image" content="{{ $metaImage }}">
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
    <body class="bg-ktn-surface font-sans text-ktn-ink antialiased">
        <div class="min-h-screen overflow-hidden">
            <x-public-header active="home" />
            <main id="home" class="pt-[73px]">
                <section class="relative overflow-hidden bg-ktn-topo px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
                    <div class="hero-kontur absolute inset-0 opacity-25 mix-blend-multiply"></div>
                    <div class="absolute inset-0 bg-gradient-to-r from-ktn-surface via-ktn-surface/90 to-ktn-surface/40"></div>
                    <div class="relative mx-auto grid max-w-7xl items-center gap-12 lg:grid-cols-[1.15fr_0.85fr]">
                        <div>
                            <p class="font-mono text-xs font-semibold uppercase tracking-[0.24em] text-ktn-sage">Portal Alumni Geodesi 96</p>
                            <h1 class="mt-5 max-w-3xl font-display text-4xl font-extrabold leading-tight tracking-tight text-ktn-forest sm:text-5xl lg:text-6xl">Jejak, cerita, dan perjalanan yang terus terhubung</h1>
                            <p class="mt-6 max-w-2xl text-base leading-8 text-ktn-muted sm:text-lg">Ruang bersama alumni Teknik Geodesi UGM Angkatan 1996 untuk saling menemukan, berbagi kabar, dan menjaga kenangan lintas waktu.</p>
                            <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                                @auth
                                    <a href="{{ route('dashboard') }}" class="inline-flex justify-center rounded-lg bg-ktn-forest px-6 py-3.5 text-sm font-bold text-white transition hover:bg-ktn-forest-strong">Buka Dashboard</a>
                                @else
                                    <a href="{{ route('login') }}" class="inline-flex justify-center rounded-lg bg-ktn-forest px-6 py-3.5 text-sm font-bold text-white transition hover:bg-ktn-forest-strong">Masuk sebagai Alumni</a>
                                @endauth
                                <a href="{{ route('public.reunion') }}" class="inline-flex justify-center rounded-lg border border-ktn-forest/25 bg-white/80 px-6 py-3.5 text-sm font-bold text-ktn-forest transition hover:border-ktn-forest">Lihat Arsip Reuni</a>
                            </div>
                        </div>
                        <div class="relative mx-auto w-full max-w-md">
                            <div class="absolute -inset-8 rounded-full bg-ktn-sage/10 blur-2xl"></div>
                            <div class="relative rounded-3xl border border-ktn-sage/20 bg-white/85 p-8 shadow-xl shadow-ktn-forest/10 backdrop-blur">
                                <img src="{{ asset('images/icon/favicon512.png') }}" alt="Logo Geodesi 96" class="mx-auto size-40 object-contain sm:size-48">
                                <div class="mt-7 grid grid-cols-3 gap-2 text-center font-mono text-[0.65rem] font-semibold uppercase tracking-wider text-ktn-sage">
                                    <span class="rounded-lg bg-ktn-topo px-2 py-3">Alumni</span>
                                    <span class="rounded-lg bg-ktn-topo px-2 py-3">Perjalanan</span>
                                    <span class="rounded-lg bg-ktn-topo px-2 py-3">Kenangan</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="tentang" class="scroll-mt-24 px-4 py-20 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-7xl">
                        <p class="font-mono text-xs font-semibold uppercase tracking-[0.22em] text-ktn-sage">Ruang Bersama</p>
                        <h2 class="mt-3 font-display text-3xl font-bold text-ktn-forest sm:text-4xl">Tentang Geodesi 96</h2>
                        <p class="mt-5 max-w-3xl text-base leading-8 text-ktn-muted">Portal ini tumbuh dari paseduluran satu angkatan—menghubungkan kabar hari ini dengan perjalanan dan kenangan yang membentuk kita.</p>
                        <div class="mt-10 grid gap-5 md:grid-cols-3">
                            @foreach ([['Temukan Kembali', 'Terhubung dengan teman seangkatan melalui direktori dan persebaran alumni.'], ['Bagikan Perjalanan', 'Simpan kabar dan tonggak perjalanan alumni sebagai cerita bersama.'], ['Rawat Kenangan', 'Kumpulkan dokumentasi kampus, reuni, serta potret lintas masa.']] as [$title, $copy])
                                <article class="rounded-2xl border border-ktn-sage/20 bg-white p-7 shadow-sm"><h3 class="font-display text-xl font-bold text-ktn-forest">{{ $title }}</h3><p class="mt-3 leading-7 text-ktn-muted">{{ $copy }}</p></article>
                            @endforeach
                        </div>
                    </div>
                </section>

                <section id="kabar" class="scroll-mt-24 bg-white px-4 py-20 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-7xl">
                        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"><div><p class="font-mono text-xs font-semibold uppercase tracking-[0.22em] text-ktn-sage">Kabar Komunitas</p><h2 class="mt-3 font-display text-3xl font-bold text-ktn-forest sm:text-4xl">Kabar Alumni Terbaru</h2></div><a href="{{ route('news.index') }}" class="text-sm font-bold text-ktn-forest underline decoration-ktn-gold decoration-2 underline-offset-4">Lihat semua kabar</a></div>
                        <div class="mt-10 grid gap-5 md:grid-cols-3">
                            @forelse ($latestNewsItems as $newsItem)
                                <article class="flex flex-col rounded-2xl border border-ktn-sage/20 bg-ktn-surface p-7"><p class="font-mono text-xs uppercase tracking-wider text-ktn-sage">{{ $newsItem->published_at?->translatedFormat('d M Y') }}</p><h3 class="mt-3 font-display text-xl font-bold text-ktn-forest">{{ $newsItem->title }}</h3><p class="mt-3 line-clamp-3 leading-7 text-ktn-muted">{{ $newsItem->excerpt }}</p><a href="{{ route('news.show', $newsItem) }}" class="mt-6 text-sm font-bold text-ktn-forest">Baca kabar →</a></article>
                            @empty
                                <div class="rounded-2xl border border-ktn-sage/20 bg-ktn-surface p-8 md:col-span-3"><h3 class="font-display text-xl font-bold text-ktn-forest">Belum ada kabar publik</h3><p class="mt-2 text-ktn-muted">Kabar terbaru komunitas akan hadir di sini.</p></div>
                            @endforelse
                        </div>
                    </div>
                </section>

                <section id="dokumentasi" class="scroll-mt-24 px-4 py-20 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-7xl">
                        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"><div><p class="font-mono text-xs font-semibold uppercase tracking-[0.22em] text-ktn-sage">Arsip Bersama</p><h2 class="mt-3 font-display text-3xl font-bold text-ktn-forest sm:text-4xl">Dokumentasi Pilihan</h2></div><a href="{{ route('public.gallery') }}" class="text-sm font-bold text-ktn-forest underline decoration-ktn-gold decoration-2 underline-offset-4">Lihat Galeri Publik</a></div>
                        <div class="mt-10 grid gap-5 md:grid-cols-3">
                            @forelse ($publicMediaItems as $mediaItem)
                                <article class="overflow-hidden rounded-2xl border border-ktn-sage/20 bg-white shadow-sm"><div class="aspect-[4/3] bg-ktn-topo">@if ($mediaItem->isPhoto() && $mediaItem->displayUrl())<img src="{{ $mediaItem->displayUrl() }}" alt="{{ $mediaItem->title }}" class="size-full object-cover">@else<div class="grid size-full place-items-center font-mono text-xs font-semibold uppercase tracking-wider text-ktn-sage">{{ $mediaItem->type }}</div>@endif</div><div class="p-5"><h3 class="font-display text-lg font-bold text-ktn-forest">{{ $mediaItem->title }}</h3>@if ($mediaItem->year)<p class="mt-2 text-sm text-ktn-muted">{{ $mediaItem->year }}</p>@endif</div></article>
                            @empty
                                <div class="rounded-2xl border border-ktn-sage/20 bg-white p-8 md:col-span-3"><h3 class="font-display text-xl font-bold text-ktn-forest">Dokumentasi sedang dikurasi</h3><p class="mt-2 text-ktn-muted">Potret perjalanan Geodesi 96 akan segera hadir.</p></div>
                            @endforelse
                        </div>
                    </div>
                </section>

                <section id="reuni" class="scroll-mt-24 bg-ktn-forest px-4 py-20 text-white sm:px-6 lg:px-8">
                    <div class="mx-auto grid max-w-7xl items-center gap-10 lg:grid-cols-[0.8fr_1.2fr]">
                        <img src="{{ asset('images/brand/sticker-kembali-ke-titik-nol-full.png') }}" alt="Kembali ke Titik Nol" class="sticker-shadow mx-auto w-full max-w-sm object-contain">
                        <div><p class="font-mono text-xs font-semibold uppercase tracking-[0.22em] text-ktn-gold-light">Satu Bab dalam Perjalanan Kita</p><h2 class="mt-3 font-display text-3xl font-bold sm:text-4xl">Reuni 30 Tahun</h2><h3 class="mt-5 font-display text-2xl font-bold text-ktn-gold-light">Kembali ke Titik Nol</h3><p class="mt-3 font-mono text-sm uppercase tracking-wider text-ktn-sage-light">23–24 Agustus 2026 · Yogyakarta</p><p class="mt-5 max-w-2xl leading-8 text-ktn-sage-light">Simpan cerita, dokumentasi, dan jejak kebersamaan Reuni 30 Tahun dalam ruang khusus yang tetap menjadi bagian dari portal alumni.</p><a href="{{ route('public.reunion') }}" class="mt-7 inline-flex rounded-lg bg-ktn-gold px-6 py-3.5 text-sm font-bold text-ktn-forest transition hover:bg-ktn-gold-light">Buka Halaman Reuni</a>
                            @if ($publicDonations->isNotEmpty() || $anonymousDonorCount > 0)
                                <div class="mt-10 border-t border-white/15 pt-7"><h3 class="font-display text-lg font-bold">Terima Kasih, Donatur</h3><div class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-sm text-ktn-sage-light">@foreach ($publicDonations as $donation)<span>{{ $donation->alumni?->full_name }}</span>@endforeach</div>@if ($anonymousDonorCount > 0)<p class="mt-4 text-sm text-ktn-sage-light">{{ $anonymousDonorCount }} donatur memilih ditampilkan sebagai anonim.</p>@endif</div>
                            @endif
                        </div>
                    </div>
                </section>

                <section class="px-4 py-20 sm:px-6 lg:px-8"><div class="mx-auto max-w-4xl rounded-3xl border border-ktn-sage/20 bg-white p-8 text-center shadow-sm sm:p-12"><p class="font-mono text-xs font-semibold uppercase tracking-[0.22em] text-ktn-sage">Ruang Internal Alumni</p><h2 class="mt-3 font-display text-3xl font-bold text-ktn-forest">Masuk sebagai Alumni</h2><p class="mx-auto mt-4 max-w-2xl leading-7 text-ktn-muted">Perbarui profil, temukan teman seangkatan, ikuti perjalanan alumni, dan akses dokumentasi internal komunitas.</p><a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="mt-7 inline-flex rounded-lg bg-ktn-forest px-7 py-3.5 text-sm font-bold text-white transition hover:bg-ktn-forest-strong">{{ auth()->check() ? 'Buka Dashboard' : 'Login ke Portal' }}</a></div></section>
            </main>

            <footer id="kontak" class="bg-ktn-forest px-4 py-12 text-white sm:px-6 lg:px-8">
                <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-3">
                    <div><div class="flex items-center gap-3"><img src="{{ asset('images/icon/favicon96.png') }}" alt="Logo Geodesi 96" class="size-10 rounded-lg bg-white p-1"><span class="font-display text-xl font-extrabold">Portal Alumni Geodesi 96</span></div><p class="mt-4 max-w-sm text-sm leading-7 text-ktn-sage-light">Jejak, cerita, dan perjalanan yang terus terhubung.</p></div>
                    <div><h3 class="font-mono text-xs font-semibold uppercase tracking-[0.2em] text-ktn-sage-light">Navigasi</h3><div class="mt-4 grid gap-3 text-sm font-semibold"><a href="#tentang">Tentang</a><a href="#kabar">Kabar Alumni</a><a href="#dokumentasi">Dokumentasi</a><a href="#reuni">Reuni 30 Tahun</a></div></div>
                    <div><h3 class="font-mono text-xs font-semibold uppercase tracking-[0.2em] text-ktn-sage-light">Kontak Panitia</h3><p class="mt-4 text-sm leading-7 text-ktn-sage-light">Kanal informasi Reuni 30 Tahun Geodesi 96.</p><div class="mt-5 grid gap-3 text-sm font-bold"><a href="https://www.instagram.com/titiknol.tgd96" target="_blank" rel="noopener noreferrer">Instagram · titiknol.tgd96</a><a href="https://wa.me/6281931720792?text=Halo%20panitia%20reuni%20tgd96" target="_blank" rel="noopener noreferrer">WhatsApp · Asih</a><a href="https://wa.me/6281286134887?text=Halo%20panitia%20reuni%20tgd96" target="_blank" rel="noopener noreferrer">WhatsApp · Iin</a></div></div>
                </div>
                <div class="mx-auto mt-10 max-w-7xl border-t border-white/10 pt-6 font-mono text-xs uppercase tracking-wider text-ktn-sage-light">© 2026 Portal Alumni Geodesi 96</div>
            </footer>
            <a href="#home" class="fixed bottom-5 right-5 z-50 rounded-full bg-ktn-forest p-3 text-white shadow-xl" aria-label="Kembali ke bagian atas halaman"><svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 19V5M6 11l6-6 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg></a>
        </div>
        @livewireScripts
        @fluxScripts
    </body>
</html>
