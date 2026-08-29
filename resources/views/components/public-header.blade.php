@props(['active' => null])

@php
    $navigationItems = [
        ['label' => __('Beranda'), 'href' => route('home'), 'key' => 'home'],
        ['label' => __('Tentang'), 'href' => route('home').'#tentang', 'key' => 'about'],
        ['label' => __('Kabar'), 'href' => route('news.index'), 'key' => 'news'],
        ['label' => __('Dokumentasi'), 'href' => route('public.gallery'), 'key' => 'gallery'],
        ['label' => __('Reuni 30 Tahun'), 'href' => route('public.reunion'), 'key' => 'reunion'],
    ];
@endphp

<header class="fixed inset-x-0 top-0 z-50 border-b border-ktn-sage/20 bg-ktn-surface/85 backdrop-blur-xl" data-public-header>
    <nav class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
        <a href="{{ request()->routeIs('home') ? '#home' : route('home') }}" class="flex items-center gap-3">
            <img src="{{ asset('images/icon/favicon96.png') }}" alt="Logo Geodesi 96" class="size-9 rounded-lg border border-ktn-forest/20 bg-white object-contain p-1">
            <span class="font-display text-lg font-extrabold tracking-tight text-ktn-forest">
                <span class="hidden sm:inline">Portal Alumni Geodesi 96</span>
                <span class="sm:hidden">Portal Alumni</span>
            </span>
        </a>

        <div class="hidden items-center gap-5 lg:flex">
            @foreach ($navigationItems as $item)
                <a
                    href="{{ $item['href'] }}"
                    data-public-header-link
                    data-public-header-key="{{ $item['key'] }}"
                    @class([
                        'font-mono text-xs font-semibold uppercase tracking-[0.22em] transition hover:text-ktn-forest',
                        'text-ktn-forest underline decoration-2 underline-offset-8' => $active === $item['key'],
                        'text-ktn-muted' => $active !== $item['key'],
                    ])
                >
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>

        <div class="hidden lg:block">
            @auth
                <a href="{{ route('dashboard') }}" class="rounded-lg bg-ktn-forest px-5 py-2.5 text-sm font-bold text-white transition hover:bg-ktn-forest-strong focus:outline-none focus:ring-2 focus:ring-ktn-forest focus:ring-offset-2 focus:ring-offset-ktn-surface">
                    {{ __('Dashboard') }}
                </a>
            @else
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="rounded-lg bg-ktn-forest px-5 py-2.5 text-sm font-bold text-white transition hover:bg-ktn-forest-strong focus:outline-none focus:ring-2 focus:ring-ktn-forest focus:ring-offset-2 focus:ring-offset-ktn-surface">
                        {{ __('Login') }}
                    </a>
                @endif
            @endauth
        </div>

        <details class="group relative lg:hidden" data-public-mobile-menu>
            <summary class="flex cursor-pointer list-none items-center gap-2 rounded-lg border border-ktn-sage/40 bg-white/80 px-3 py-2 text-sm font-bold text-ktn-forest transition hover:border-ktn-forest/40 focus:outline-none focus:ring-2 focus:ring-ktn-forest">
                {{ __('Menu') }}
                <svg class="size-4 transition group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                </svg>
            </summary>

            <div class="absolute right-0 top-full mt-3 w-64 rounded-xl border border-ktn-sage/30 bg-white p-2 shadow-xl shadow-ktn-forest/10">
                @foreach ($navigationItems as $item)
                    <a
                        href="{{ $item['href'] }}"
                        data-public-header-link
                        data-public-header-key="{{ $item['key'] }}"
                        @class([
                            'block rounded-lg px-3 py-2.5 text-sm font-semibold transition hover:bg-ktn-mist hover:text-ktn-forest',
                            'bg-ktn-mist text-ktn-forest' => $active === $item['key'],
                            'text-ktn-muted' => $active !== $item['key'],
                        ])
                    >
                        {{ $item['label'] }}
                    </a>
                @endforeach

                <div class="mt-2 border-t border-ktn-sage/20 pt-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="block rounded-lg bg-ktn-forest px-3 py-2.5 text-center text-sm font-bold text-white transition hover:bg-ktn-forest-strong">
                            {{ __('Dashboard') }}
                        </a>
                    @else
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="block rounded-lg bg-ktn-forest px-3 py-2.5 text-center text-sm font-bold text-white transition hover:bg-ktn-forest-strong">
                                {{ __('Login') }}
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </details>
    </nav>
</header>
