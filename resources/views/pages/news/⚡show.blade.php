<?php

use App\Models\News;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::public')]
#[Title('Detail Berita')] class extends Component {
    public News $news;

    public function mount(News $news): void
    {
        abort_unless($news->isPublished(), 404);

        $this->news = $news->load('author');
    }
}; ?>

<main class="min-h-screen bg-ktn-surface">
    <header class="border-b border-ktn-sage/20 bg-white">
        <nav class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <img src="{{ asset('images/icon/favicon96.png') }}" alt="Logo Geodesi 96" class="size-9 rounded-lg border border-ktn-forest/20 bg-white object-contain p-1">
                <span class="font-display text-lg font-extrabold tracking-tight text-ktn-forest">Geodesi 96</span>
            </a>
            <a href="{{ route('news.index') }}" class="inline-flex items-center justify-center rounded-lg bg-ktn-forest px-4 py-2.5 text-sm font-bold text-white transition hover:bg-ktn-forest-strong" wire:navigate>
                {{ __('Berita') }}
            </a>
        </nav>
    </header>

    <section class="w-full px-4 py-12 sm:px-6 lg:px-8">
        <article class="mx-auto max-w-3xl space-y-6">
            <flux:button variant="ghost" icon="arrow-left" :href="route('news.index')" wire:navigate>
                {{ __('Kembali') }}
            </flux:button>

            <div class="space-y-3">
                <flux:badge color="green">{{ __('Published') }}</flux:badge>
                <flux:heading size="xl">{{ $news->title }}</flux:heading>
                <flux:text>{{ $news->published_at?->translatedFormat('d F Y H:i') }} · {{ $news->author?->name }}</flux:text>
                @if ($news->excerpt)
                    <flux:text class="text-base">{{ $news->excerpt }}</flux:text>
                @endif
            </div>

            <div class="space-y-4 rounded-lg border border-ktn-sage/20 bg-white p-6 leading-7 text-ktn-ink shadow-sm">
                {!! nl2br(e($news->content)) !!}
            </div>
        </article>
    </section>
</main>
