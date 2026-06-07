<?php

use App\Models\News;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::public')]
#[Title('Berita')] class extends Component {
    use WithPagination;

    #[Computed]
    public function newsItems(): LengthAwarePaginator
    {
        return News::query()
            ->with('author')
            ->where('status', 'published')
            ->latest('published_at')
            ->paginate(12);
    }
}; ?>

<main class="min-h-screen bg-ktn-surface">
    <header class="border-b border-ktn-sage/20 bg-white">
        <nav class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <img src="{{ asset('images/icon/favicon96.png') }}" alt="Logo Geodesi 96" class="size-9 rounded-lg border border-ktn-forest/20 bg-white object-contain p-1">
                <span class="font-display text-lg font-extrabold tracking-tight text-ktn-forest">Geodesi 96</span>
            </a>
            <div class="flex items-center gap-3">
                <a href="{{ route('public.gallery') }}" class="hidden font-mono text-xs font-semibold uppercase tracking-[0.18em] text-ktn-muted transition hover:text-ktn-forest sm:inline">Galeri</a>
                <a href="{{ route('home') }}#donatur" class="hidden font-mono text-xs font-semibold uppercase tracking-[0.18em] text-ktn-muted transition hover:text-ktn-forest sm:inline">Donatur</a>
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center rounded-lg bg-ktn-forest px-4 py-2.5 text-sm font-bold text-white transition hover:bg-ktn-forest-strong">Landing</a>
            </div>
        </nav>
    </header>

    <section class="mx-auto w-full max-w-7xl space-y-8 px-4 py-12 sm:px-6 lg:px-8">
        <div class="space-y-3 text-center sm:text-left">
            <p class="font-mono text-xs font-semibold uppercase tracking-[0.22em] text-ktn-forest">{{ __('Publikasi') }}</p>
            <flux:heading size="xl">{{ __('Berita dan Pengumuman') }}</flux:heading>
            <flux:text class="mx-auto max-w-3xl sm:mx-0">
                {{ __('Informasi resmi persiapan, pelaksanaan, dan pasca kegiatan reuni.') }}
            </flux:text>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($this->newsItems as $news)
                <article class="rounded-lg border border-ktn-sage/20 bg-white p-5 shadow-sm" wire:key="news-{{ $news->id }}">
                    <div class="space-y-3">
                        <flux:badge color="green">{{ __('Published') }}</flux:badge>
                        <div class="space-y-1">
                            <flux:heading size="lg">{{ $news->title }}</flux:heading>
                            <flux:text>{{ $news->published_at?->translatedFormat('d F Y') }} · {{ $news->author?->name }}</flux:text>
                        </div>
                        <flux:text>{{ $news->excerpt ?: str($news->content)->limit(140) }}</flux:text>
                        <flux:button variant="ghost" icon="arrow-right" :href="route('news.show', $news->slug)" wire:navigate>
                            {{ __('Baca') }}
                        </flux:button>
                    </div>
                </article>
            @empty
                <div class="rounded-lg border border-ktn-sage/20 bg-white p-8 text-center md:col-span-2 xl:col-span-3">
                    <flux:heading size="lg">{{ __('Belum ada berita') }}</flux:heading>
                    <flux:text class="mt-2">{{ __('Pengumuman resmi akan tampil setelah dipublikasikan panitia.') }}</flux:text>
                </div>
            @endforelse
        </div>

        {{ $this->newsItems->links() }}
    </section>
</main>
