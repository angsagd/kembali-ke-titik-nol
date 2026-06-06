<?php

use App\Models\News;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Berita')] class extends Component {
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

<section class="w-full space-y-6 p-6 lg:p-8">
    <div class="space-y-2">
        <flux:heading size="xl">{{ __('Berita dan Pengumuman') }}</flux:heading>
        <flux:text class="max-w-3xl">
            {{ __('Informasi resmi persiapan, pelaksanaan, dan pasca kegiatan reuni.') }}
        </flux:text>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($this->newsItems as $news)
            <article class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900" wire:key="news-{{ $news->id }}">
                <div class="space-y-3">
                    <flux:badge color="green">{{ __('Published') }}</flux:badge>
                    <div class="space-y-1">
                        <flux:heading size="lg">{{ $news->title }}</flux:heading>
                        <flux:text>{{ $news->published_at?->translatedFormat('d F Y') }} · {{ $news->author?->name }}</flux:text>
                    </div>
                    <flux:text>{{ $news->excerpt ?: str($news->content)->limit(140) }}</flux:text>
                    <flux:button variant="ghost" icon="arrow-right" :href="route('news.show', $news)" wire:navigate>
                        {{ __('Baca') }}
                    </flux:button>
                </div>
            </article>
        @empty
            <div class="rounded-lg border border-zinc-200 bg-white p-8 text-center md:col-span-2 xl:col-span-3 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg">{{ __('Belum ada berita') }}</flux:heading>
                <flux:text class="mt-2">{{ __('Pengumuman resmi akan tampil setelah dipublikasikan panitia.') }}</flux:text>
            </div>
        @endforelse
    </div>

    {{ $this->newsItems->links() }}
</section>
