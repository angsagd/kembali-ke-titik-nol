@props(['active' => null])

@php
    $navigationItems = [
        ['label' => __('Tentang'), 'href' => route('home').'#tentang', 'key' => 'about'],
        ['label' => __('Rundown'), 'href' => route('home').'#rundown', 'key' => 'schedule'],
        ['label' => __('Galeri'), 'href' => route('home').'#galeri', 'key' => 'gallery'],
        ['label' => __('Berita'), 'href' => route('home').'#berita', 'key' => 'news'],
        ['label' => __('Donatur'), 'href' => route('home').'#donatur', 'key' => 'donors'],
    ];
@endphp

<header class="fixed inset-x-0 top-0 z-50 border-b border-ktn-sage/20 bg-ktn-surface/85 backdrop-blur-xl">
    <nav class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-4 py-4 sm:px-6 lg:px-8">
        <a href="{{ request()->routeIs('home') ? '#home' : route('home') }}" class="flex items-center gap-3">
            <img src="{{ asset('images/icon/favicon96.png') }}" alt="Logo Geodesi 96" class="size-9 rounded-lg border border-ktn-forest/20 bg-white object-contain p-1">
            <span class="font-display text-lg font-extrabold tracking-tight text-ktn-forest">Geodesi 96</span>
        </a>

        <div class="hidden items-center gap-8 md:flex">
            @foreach ($navigationItems as $item)
                <a
                    href="{{ $item['href'] }}"
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

        @if (Route::has('login'))
            <a href="{{ route('login') }}" class="rounded-lg bg-ktn-forest px-5 py-2.5 text-sm font-bold text-white transition hover:bg-ktn-forest-strong focus:outline-none focus:ring-2 focus:ring-ktn-forest focus:ring-offset-2 focus:ring-offset-ktn-surface">
                {{ __('Login') }}
            </a>
        @endif
    </nav>
</header>
