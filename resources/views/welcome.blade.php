<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @php
            $metaTitle = 'Reuni 30 Tahun Geodesi 96 - Kembali ke Titik Nol';
            $metaDescription = 'Ngalibrasi 30 Taon Paseduluran. Reuni alumni Teknik Geodesi UGM angkatan 96 untuk pulang, bertemu, mengenang, dan kembali ke titik nol bersama.';
            $metaUrl = 'https://geodesiugm96.web.id';
            $metaImage = 'https://geodesiugm96.web.id/images/brand/sticker-kembali-ke-titik-nol-full.png';
            $siteName = 'Kembali ke Titik Nol';
        @endphp

        <title>{{ $metaTitle }}</title>
        <meta name="description" content="{{ $metaDescription }}">
        <link rel="canonical" href="{{ $metaUrl }}">

        <meta property="og:locale" content="id_ID">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="{{ $siteName }}">
        <meta property="og:title" content="{{ $metaTitle }}">
        <meta property="og:description" content="{{ $metaDescription }}">
        <meta property="og:url" content="{{ $metaUrl }}">
        <meta property="og:image" content="{{ $metaImage }}">
        <meta property="og:image:secure_url" content="{{ $metaImage }}">
        <meta property="og:image:type" content="image/png">
        <meta property="og:image:width" content="1254">
        <meta property="og:image:height" content="1254">
        <meta property="og:image:alt" content="Artwork Kembali ke Titik Nol Reuni 30 Tahun Geodesi 96">

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $metaTitle }}">
        <meta name="twitter:description" content="{{ $metaDescription }}">
        <meta name="twitter:image" content="{{ $metaImage }}">
        <meta name="twitter:image:alt" content="Artwork Kembali ke Titik Nol Reuni 30 Tahun Geodesi 96">

        <meta name="version" content="0.9.b">

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
        <div class="min-h-screen overflow-hidden">
            <x-public-header active="about" />

            <main id="home" class="pt-[73px]">
                <section class="relative overflow-hidden bg-ktn-topo px-4 py-16 sm:px-6 sm:py-20 lg:px-8">
                    <div class="hero-kontur absolute inset-y-0 -left-10 -right-10 opacity-35 mix-blend-multiply"></div>
                    <div class="absolute inset-0 bg-ktn-surface/45"></div>
                    <div class="absolute left-6 top-28 hidden rotate-[-8deg] rounded-lg border border-ktn-sage/30 bg-white/70 px-4 py-3 font-mono text-xs font-semibold uppercase tracking-[0.18em] text-ktn-sage shadow-sm lg:block">
                        X 0.000<br>Y 0.000
                    </div>
                    <div class="absolute right-8 top-24 hidden rotate-3 rounded-lg bg-ktn-sage/20 px-5 py-4 font-display text-2xl font-extrabold text-ktn-sage lg:block">
                        96
                    </div>

                    <div class="relative mx-auto flex max-w-7xl flex-col items-center text-center">
                        <img
                            src="{{ asset('images/brand/sticker-kembali-ke-titik-nol.png') }}"
                            alt="Logo Kembali ke Titik Nol Reuni Geodesi 96"
                            class="sticker-shadow mb-8 size-48 rounded-sm object-contain sm:size-56 lg:size-64"
                        >

                        <p class="mb-3 font-mono text-xs font-semibold uppercase tracking-[0.24em] text-ktn-forest">Reuni 30 Tahun Geodesi UGM</p>
                        <h1 class="max-w-4xl font-display text-4xl font-extrabold leading-tight tracking-tight text-ktn-forest sm:text-5xl lg:text-6xl">Kembali ke Titik Nol</h1>
                        <p class="mt-6 max-w-2xl text-base leading-8 text-ktn-muted sm:text-lg">
                            Ngalibrasi 30 Taon Paseduluran. Menghubungkan kenangan, menyatukan langkah, kembali ke kampus tercinta.
                        </p>

                        <div class="mt-10 grid w-full max-w-3xl grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4" data-countdown-target="2026-08-23T12:00:00+07:00" aria-label="Hitung mundur menuju 23 Agustus 2026 pukul 12.00 GMT+7">
                            @foreach ([['days', 'Hari'], ['hours', 'Jam'], ['minutes', 'Menit'], ['seconds', 'Detik']] as [$unit, $label])
                                <div class="rounded-xl border border-ktn-sage/20 bg-white p-5 shadow-sm">
                                    <div class="font-display text-3xl font-extrabold text-ktn-forest sm:text-4xl tabular-nums" data-countdown-unit="{{ $unit }}">0</div>
                                    <div class="mt-2 font-mono text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-ktn-muted">{{ $label }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>

                <section id="tentang" class="scroll-mt-24 bg-white px-4 py-16 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-7xl">
                        <div class="text-center">
                            <span class="inline-flex rounded-full bg-ktn-sage/15 px-4 py-1.5 font-mono text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-ktn-forest">30 Tahun Paseduluran</span>
                            <h2 class="mt-6 font-display text-3xl font-bold tracking-tight text-ktn-forest sm:text-4xl">Tiga Dekade, Satu Paseduluran Tanpa Batas</h2>
                        </div>

                        <div class="mt-12 grid gap-5 md:grid-cols-3">
                            <article class="rounded-xl border border-ktn-sage/15 bg-white p-7 transition hover:border-ktn-sage/35 hover:shadow-lg hover:shadow-ktn-forest/10">
                                <div class="mb-5 grid size-11 place-items-center rounded-lg bg-ktn-gold/15 text-ktn-gold">
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 7v5l3 2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M4.93 4.93A10 10 0 1 1 3 12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M3 4v5h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                                <h3 class="font-display text-xl font-bold text-ktn-forest">Jejak Masa Lalu</h3>
                                <p class="mt-3 leading-7 text-ktn-muted">Mengingat kembali langkah awal di Teknik Geodesi UGM tahun 1996, fondasi dari segala pencapaian hari ini.</p>
                            </article>

                            <article class="rounded-xl border border-ktn-sage/15 bg-white p-7 transition hover:border-ktn-sage/35 hover:shadow-lg hover:shadow-ktn-forest/10">
                                <div class="mb-5 grid size-11 place-items-center rounded-lg bg-ktn-gold/15 text-ktn-gold">
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M8 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM16 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM3.5 20a4.5 4.5 0 0 1 9 0M12 20a4.5 4.5 0 0 1 9 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                </div>
                                <h3 class="font-display text-xl font-bold text-ktn-forest">Ikatan Kuat</h3>
                                <p class="mt-3 leading-7 text-ktn-muted">Lebih dari rekan sejawat, Geodesi 96 adalah keluarga yang saling mendukung dalam rentang waktu 30 tahun.</p>
                            </article>

                            <article class="rounded-xl border border-ktn-sage/15 bg-white p-7 transition hover:border-ktn-sage/35 hover:shadow-lg hover:shadow-ktn-forest/10">
                                <div class="mb-5 grid size-11 place-items-center rounded-lg bg-ktn-gold/15 text-ktn-gold">
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 21s7-5.2 7-12a7 7 0 1 0-14 0c0 6.8 7 12 7 12Z" stroke="currentColor" stroke-width="1.8"/><path d="M12 12.2a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="1.8"/></svg>
                                </div>
                                <h3 class="font-display text-xl font-bold text-ktn-forest">Titik Temu</h3>
                                <p class="mt-3 leading-7 text-ktn-muted">Reuni ini menjadi Bench Mark untuk mengkalibrasi rasa dan memperkuat silaturahmi lintas koordinat kehidupan.</p>
                            </article>
                        </div>
                    </div>
                </section>

                <section class="overflow-hidden bg-ktn-topo" aria-label="Video Kembali ke Titik Nol">
                    <div class="relative mx-auto max-w-7xl">
                        <video
                            class="aspect-video w-full object-cover motion-reduce:hidden"
                            autoplay
                            muted
                            loop
                            playsinline
                            preload="metadata"
                            poster="{{ asset('videos/titiknol-movie-poster.webp') }}"
                            disablepictureinpicture
                            disableremoteplayback
                            data-landing-video
                            aria-hidden="true"
                            tabindex="-1"
                        >
                            <source src="{{ asset('videos/titiknol.mp4') }}" type="video/mp4">
                        </video>

                        <img
                            src="{{ asset('videos/titiknol-movie-poster.webp') }}"
                            alt="Kembali ke Titik Nol Geodesi 96"
                            class="hidden aspect-video w-full object-cover motion-reduce:block"
                        >

                        <div class="topo-grid pointer-events-none absolute inset-0 opacity-10 motion-reduce:hidden"></div>
                    </div>
                </section>

                <section class="bg-ktn-topo px-4 py-16 sm:px-6 lg:px-8">
                    <div class="mx-auto grid max-w-7xl items-center gap-10 lg:grid-cols-[1fr_1.05fr]">
                        <div class="relative overflow-hidden rounded-2xl bg-ktn-forest p-8 text-white">
                            <div class="topo-grid absolute inset-0 opacity-20"></div>
                            <img
                                src="{{ asset('images/brand/stickers.jpg') }}"
                                alt="Sprite sticker Kembali ke Titik Nol Geodesi 96"
                                class="relative aspect-square w-full rounded-xl object-cover opacity-90"
                            >
                            <p class="relative mt-5 font-mono text-xs font-semibold uppercase tracking-[0.18em] text-ktn-sage-light">Koordinat pulang, BM paseduluran, dan tanda 96.</p>
                        </div>

                        <div>
                            <p class="font-mono text-xs font-semibold uppercase tracking-[0.22em] text-ktn-forest">Filosofi</p>
                            <h2 class="mt-4 font-display text-3xl font-bold tracking-tight text-ktn-forest sm:text-4xl">Filosofi “Kembali ke Titik Nol”</h2>
                            <p class="mt-6 text-lg leading-8 text-ktn-muted">
                                Dalam ilmu Geodesi, Titik Nol atau Bench Mark adalah acuan untuk pengukuran. Setelah 30 tahun berkelana di berbagai koordinat kehidupan, reuni ini menjadi momen kembali ke titik awal persahabatan.
                            </p>
                            <div class="mt-7 grid gap-4">
                                @foreach ([
                                    ['Ngalibrasi Roso', 'Menyelaraskan kembali hati dan pikiran.'],
                                    ['Ngeplot Kenangan', 'Memetakan memori indah masa perkuliahan.'],
                                    ['Ngukur Paseduluran', 'Mempererat tali persaudaraan selamanya.'],
                                ] as [$title, $body])
                                    <div class="flex gap-4">
                                        <span class="mt-1 grid size-6 shrink-0 place-items-center rounded-full border border-ktn-forest text-ktn-forest">
                                            <svg class="size-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.25 7.31a1 1 0 0 1-1.421.002L3.29 9.219a1 1 0 1 1 1.42-1.408l4.04 4.079 6.836-6.894a1 1 0 0 1 1.414-.006Z" clip-rule="evenodd"/></svg>
                                        </span>
                                        <p class="leading-7 text-ktn-muted"><strong class="text-ktn-ink">{{ $title }}:</strong> {{ $body }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>

                <section id="rundown" class="scroll-mt-24 bg-white px-4 py-16 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-7xl">
                        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                            <div>
                                <p class="font-mono text-xs font-semibold uppercase tracking-[0.22em] text-ktn-forest">Agenda</p>
                                <h2 class="mt-3 font-display text-3xl font-bold text-ktn-forest sm:text-4xl">Rangkaian Acara</h2>
                                <p class="mt-3 text-ktn-muted">Dua hari untuk kembali saling menyapa dan pulang ke almamater.</p>
                            </div>
                            <a href="#kontak" class="font-mono text-xs font-semibold uppercase tracking-[0.18em] text-ktn-forest">Kontak Panitia</a>
                        </div>

                        @php
                            $eventScheduleItems = \App\Models\EventScheduleItem::query()
                                ->orderByRaw("case event_day when 'day_one' then 1 else 2 end")
                                ->orderBy('start_time')
                                ->orderBy('id')
                                ->get();

                            $eventDays = [
                                ['day_one', '01', 'Minggu, 23 Agustus', 'Penginapan Joglo / Kampung Wisata Tembi'],
                                ['day_two', '02', 'Senin, 24 Agustus', 'Departemen Teknik Geodesi UGM'],
                            ];
                        @endphp

                        <div class="mt-10 grid gap-5 lg:grid-cols-2">
                            @foreach ($eventDays as [$eventDay, $number, $date, $place])
                                <article class="rounded-xl border border-ktn-sage/20 bg-ktn-surface p-7">
                                    <div class="flex items-start gap-4">
                                        <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-ktn-forest font-mono text-xs font-bold text-white">{{ $number }}</span>
                                        <div>
                                            <h3 class="font-display text-xl font-bold text-ktn-forest">{{ $date }}</h3>
                                            <p class="mt-1 font-mono text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-ktn-muted">{{ $place }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-7 grid gap-4">
                                        @forelse ($eventScheduleItems->where('event_day', $eventDay) as $item)
                                            <div class="grid grid-cols-[4rem_1fr] gap-4">
                                                <span class="font-mono text-xs font-semibold text-ktn-muted">{{ $item->displayTime() }}</span>
                                                <span class="font-medium text-ktn-ink">{{ $item->activity }}</span>
                                            </div>
                                        @empty
                                            <p class="text-sm text-ktn-muted">{{ __('Rundown belum tersedia.') }}</p>
                                        @endforelse
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </section>

                <section id="galeri" class="scroll-mt-24 bg-ktn-topo px-4 py-16 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-7xl">
                        @php
                            $publicMediaItems = \App\Models\MediaItem::query()
                                ->with('uploader')
                                ->where('visibility', 'public')
                                ->latest()
                                ->limit(3)
                                ->get();
                            $publicPhotoCount = \App\Models\MediaItem::query()->where('visibility', 'public')->where('type', 'photo')->count();
                            $publicVideoCount = \App\Models\MediaItem::query()->where('visibility', 'public')->where('type', 'video')->count();
                        @endphp

                        <div class="flex flex-col justify-between gap-5 text-center lg:flex-row lg:items-end lg:text-left">
                            <div>
                                <p class="font-mono text-xs font-semibold uppercase tracking-[0.22em] text-ktn-forest">Arsip</p>
                                <h2 class="mt-3 font-display text-3xl font-bold text-ktn-forest sm:text-4xl">Galeri Nostalgia</h2>
                                <p class="mt-3 max-w-2xl leading-7 text-ktn-muted">Dokumentasi yang telah disetujui sebagai publik untuk memperlihatkan momen reuni dan kenangan Geodesi 96.</p>
                            </div>
                            <a href="{{ route('public.gallery') }}" class="inline-flex items-center justify-center rounded-lg bg-ktn-forest px-5 py-3 text-sm font-bold text-white transition hover:bg-ktn-forest-strong">
                                Lihat Galeri Publik
                            </a>
                        </div>

                        <div class="mt-10 grid gap-4 lg:grid-cols-[18rem_1fr]">
                            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                                <div class="rounded-xl bg-ktn-forest p-6 text-white">
                                    <div class="font-display text-4xl font-extrabold">{{ $publicPhotoCount }}</div>
                                    <div class="mt-2 font-mono text-xs font-semibold uppercase tracking-[0.18em] text-ktn-sage-light">Foto Publik</div>
                                </div>
                                <div class="rounded-xl border border-ktn-sage/20 bg-white p-6">
                                    <div class="font-display text-4xl font-extrabold text-ktn-forest">{{ $publicVideoCount }}</div>
                                    <div class="mt-2 font-mono text-xs font-semibold uppercase tracking-[0.18em] text-ktn-muted">Video Publik</div>
                                </div>
                            </div>

                            <div class="grid gap-4 md:grid-cols-3">
                                @forelse ($publicMediaItems as $mediaItem)
                                    <article class="overflow-hidden rounded-xl border border-ktn-sage/20 bg-white shadow-sm">
                                        <div class="aspect-video bg-ktn-forest/10">
                                            @if ($mediaItem->isPhoto() && $mediaItem->displayUrl())
                                                <img src="{{ $mediaItem->displayUrl() }}" alt="{{ $mediaItem->title ?: 'Foto dokumentasi' }}" class="size-full object-cover">
                                            @else
                                                <div class="grid size-full place-items-center bg-ktn-forest text-center text-white">
                                                    <span class="font-mono text-xs font-semibold uppercase tracking-[0.18em]">Video</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="space-y-2 p-5">
                                            <h3 class="font-display text-lg font-bold text-ktn-forest">{{ $mediaItem->title ?: 'Dokumentasi Publik' }}</h3>
                                            <p class="text-sm text-ktn-muted">{{ $mediaItem->uploader?->full_name }}</p>
                                        </div>
                                    </article>
                                @empty
                                    <div class="rounded-xl border border-ktn-sage/20 bg-white p-8 text-center md:col-span-3">
                                        <h3 class="font-display text-xl font-bold text-ktn-forest">Belum ada dokumentasi publik</h3>
                                        <p class="mt-2 text-ktn-muted">Foto dan video publik akan tampil setelah dikurasi panitia.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </section>

                <section id="berita" class="scroll-mt-24 bg-white px-4 py-16 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-7xl">
                        @php
                            $latestNewsItems = \App\Models\News::query()
                                ->with('author')
                                ->where('status', 'published')
                                ->latest('published_at')
                                ->limit(3)
                                ->get();
                        @endphp

                        <div class="flex flex-col justify-between gap-5 text-center lg:flex-row lg:items-end lg:text-left">
                            <div>
                                <p class="font-mono text-xs font-semibold uppercase tracking-[0.22em] text-ktn-forest">Publikasi</p>
                                <h2 class="mt-3 font-display text-3xl font-bold text-ktn-forest sm:text-4xl">Berita dan Pengumuman</h2>
                                <p class="mt-3 max-w-2xl leading-7 text-ktn-muted">Informasi resmi panitia untuk persiapan, pelaksanaan, dan pasca kegiatan reuni.</p>
                            </div>
                            <a href="{{ route('news.index') }}" class="inline-flex items-center justify-center rounded-lg border border-ktn-forest px-5 py-3 text-sm font-bold text-ktn-forest transition hover:bg-ktn-forest hover:text-white">
                                Lihat Semua Berita
                            </a>
                        </div>

                        <div class="mt-10 grid gap-4 md:grid-cols-3">
                            @forelse ($latestNewsItems as $news)
                                <article class="rounded-xl border border-ktn-sage/20 bg-ktn-surface p-6">
                                    <p class="font-mono text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-ktn-muted">
                                        {{ $news->published_at?->translatedFormat('d F Y') }}
                                    </p>
                                    <h3 class="mt-3 font-display text-xl font-bold text-ktn-forest">{{ $news->title }}</h3>
                                    <p class="mt-3 leading-7 text-ktn-muted">{{ $news->excerpt ?: str($news->content)->limit(130) }}</p>
                                    <a href="{{ route('news.show', $news->slug) }}" class="mt-5 inline-flex font-mono text-xs font-semibold uppercase tracking-[0.18em] text-ktn-forest">
                                        Baca Berita
                                    </a>
                                </article>
                            @empty
                                <div class="rounded-xl border border-ktn-sage/20 bg-ktn-surface p-8 text-center md:col-span-3">
                                    <h3 class="font-display text-xl font-bold text-ktn-forest">Belum ada berita publik</h3>
                                    <p class="mt-2 text-ktn-muted">Pengumuman resmi akan tampil setelah dipublikasikan panitia.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>

                <section id="donatur" class="scroll-mt-24 bg-white px-4 pb-16 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-7xl">

                        @php
                            $publicDonations = \App\Models\Donation::query()
                                ->with('alumni')
                                ->where('publication_status', 'show_name')
                                ->latest()
                                ->limit(16)
                                ->get();
                            $anonymousDonorCount = \App\Models\Donation::query()
                                ->where('publication_status', 'anonymous')
                                ->count();
                        @endphp

                        <div class="flex items-center gap-3">
                            <span class="text-ktn-gold">
                                <svg class="size-6" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 21s-7-4.35-7-10a4 4 0 0 1 7-2.65A4 4 0 0 1 19 11c0 5.65-7 10-7 10Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <h2 class="font-display text-2xl font-bold text-ktn-forest sm:text-3xl">Terima Kasih, Donatur</h2>
                        </div>
                        <p class="mt-4 max-w-2xl leading-7 text-ktn-muted">Apresiasi setinggi-tingginya kepada rekan-rekan yang telah memberikan kontribusi untuk kelancaran acara ini.</p>

                        <div class="mt-10 grid gap-x-10 gap-y-3 border-t border-ktn-sage/20 pt-8 text-sm text-ktn-ink sm:grid-cols-2 lg:grid-cols-4">
                            @forelse ($publicDonations as $donation)
                                <span>{{ $donation->alumni?->full_name }}</span>
                            @empty
                                <span class="text-ktn-muted sm:col-span-2 lg:col-span-4">Daftar donatur publik akan tampil setelah donasi tercatat.</span>
                            @endforelse
                        </div>

                        @if ($anonymousDonorCount > 0)
                            <p class="mt-5 text-sm text-ktn-muted">{{ $anonymousDonorCount }} donatur memilih ditampilkan sebagai anonim.</p>
                        @endif

                        <div class="mt-10 flex flex-col justify-between gap-5 rounded-xl bg-ktn-forest p-6 text-white sm:flex-row sm:items-center">
                            <div>
                                <h3 class="font-display text-xl font-bold">Ingin Berkontribusi?</h3>
                                <p class="mt-1 text-sm text-ktn-sage-light">Salurkan donasi Anda untuk mensukseskan Reuni 30 Tahun.</p>
                            </div>
                            <a href="#kontak" class="inline-flex items-center justify-center rounded-lg bg-ktn-gold px-5 py-3 text-sm font-bold text-ktn-forest transition hover:bg-ktn-gold-light">Donasi Sekarang</a>
                        </div>
                    </div>
                </section>
            </main>

            <footer id="kontak" class="scroll-mt-24 bg-ktn-forest px-4 py-12 text-white sm:px-6 lg:px-8">
                <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[1.2fr_0.8fr_1fr]">
                    <div class="space-y-4">
                        <a href="#home" class="inline-flex items-center gap-3">
                            <img src="{{ asset('images/icon/favicon96.png') }}" alt="Logo Geodesi 96" class="size-10 rounded-lg border border-white/20 bg-white object-contain p-1">
                            <span class="font-display text-xl font-extrabold">Geodesi 96</span>
                        </a>
                        <p class="max-w-sm text-sm leading-7 text-ktn-sage-light">Ngalibrasi 30 Taon Paseduluran - Alumni Teknik Geodesi UGM 1996.</p>
                        <p class="font-mono text-md font-semibold uppercase tracking-[0.18em] text-ktn-sage-light">23-24 Agustus 2026</p>
                        <p class="text-xs font-mono text-ktn-sage-light mt-6">v.0.9.b</p>
                    </div>

                    <div>
                        <h3 class="font-mono text-xs font-semibold uppercase tracking-[0.2em] text-ktn-sage-light">Navigasi</h3>
                        <div class="mt-4 grid gap-3 text-sm font-semibold">
                            <a href="#tentang" class="text-white transition hover:text-ktn-gold-light">Tentang</a>
                            <a href="#rundown" class="text-white transition hover:text-ktn-gold-light">Rundown</a>
                            <a href="#galeri" class="text-white transition hover:text-ktn-gold-light">Galeri</a>
                            <a href="#berita" class="text-white transition hover:text-ktn-gold-light">Berita</a>
                            <a href="#donatur" class="text-white transition hover:text-ktn-gold-light">Donatur</a>
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

            <a
                href="#home"
                class="fixed bottom-5 right-5 z-50 inline-flex items-center gap-3 rounded-full border border-ktn-sage/30 bg-ktn-forest px-4 py-3 text-sm font-bold text-white shadow-xl shadow-ktn-forest/20 transition hover:-translate-y-0.5 hover:bg-ktn-forest-strong focus:outline-none focus:ring-2 focus:ring-ktn-gold focus:ring-offset-2 focus:ring-offset-ktn-surface"
                aria-label="Kembali ke bagian atas halaman"
            >
                <span class="hidden sm:inline">Kembali ke Titik Nol</span>
                <svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 19V5M6 11l6-6 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </a>
        </div>

        @livewireScripts
        @fluxScripts
    </body>
</html>
