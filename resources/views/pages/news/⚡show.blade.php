<?php

use App\Models\News;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Detail Berita')] class extends Component {
    public News $news;

    public function mount(News $news): void
    {
        abort_unless($news->isPublished(), 404);

        $this->news = $news->load('author');
    }
}; ?>

<section class="w-full p-6 lg:p-8">
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

        <div class="space-y-4 rounded-lg border border-zinc-200 bg-white p-6 leading-7 text-zinc-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200">
            {!! nl2br(e($news->content)) !!}
        </div>
    </article>
</section>
