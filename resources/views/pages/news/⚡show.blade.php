<?php

use App\Models\News;
use Illuminate\Support\Str;
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
    <x-public-header active="news" />

    <section class="w-full px-4 pb-12 pt-24 sm:px-6 lg:px-8">
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

            <div class="space-y-4 rounded-lg border border-ktn-sage/20 bg-white p-6 leading-7 text-ktn-ink shadow-sm [&_a]:font-medium [&_a]:text-ktn-forest [&_a]:underline [&_a]:underline-offset-4 [&_blockquote]:border-l-4 [&_blockquote]:border-ktn-sage [&_blockquote]:pl-4 [&_blockquote]:italic [&_h1]:text-2xl [&_h1]:font-bold [&_h2]:text-xl [&_h2]:font-bold [&_h3]:text-lg [&_h3]:font-semibold [&_img]:max-h-[70vh] [&_img]:w-full [&_img]:rounded-lg [&_img]:object-contain [&_li]:ml-5 [&_ol]:list-decimal [&_p]:leading-7 [&_strong]:font-bold [&_ul]:list-disc">
                {!! Str::markdown($news->content, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
            </div>
        </article>
    </section>
</main>
