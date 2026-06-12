<?php

use App\Models\AuditLog;
use App\Models\News;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Manajemen Berita')] class extends Component {
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = 'all';

    public ?int $editing_id = null;

    public string $title = '';

    public string $slug = '';

    public ?string $excerpt = null;

    public string $content = '';

    public string $form_status = 'draft';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedTitle(): void
    {
        if ($this->editing_id === null || blank($this->slug)) {
            $this->slug = Str::slug($this->title);
        }
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function summary(): array
    {
        return [
            'draft' => News::query()->where('status', 'draft')->count(),
            'published' => News::query()->where('status', 'published')->count(),
            'archived' => News::query()->where('status', 'archived')->count(),
        ];
    }

    #[Computed]
    public function newsItems(): LengthAwarePaginator
    {
        $search = trim($this->search);

        return News::query()
            ->with('author')
            ->when(in_array($this->status, ['draft', 'published', 'archived'], true), function ($query): void {
                $query->where('status', $this->status);
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%")
                        ->orWhereHas('author', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(15);
    }

    public function edit(News $news): void
    {
        $this->editing_id = $news->id;
        $this->title = $news->title;
        $this->slug = $news->slug;
        $this->excerpt = $news->excerpt;
        $this->content = $news->content;
        $this->form_status = $news->status;
        $this->resetErrorBag();
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['required', 'string', 'max:220', Rule::unique(News::class, 'slug')->ignore($this->editing_id)],
            'excerpt' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'form_status' => ['required', Rule::in(['draft', 'published', 'archived'])],
        ]);

        $news = $this->editing_id
            ? News::query()->findOrFail($this->editing_id)
            : new News(['author_id' => Auth::id()]);

        $oldValues = $news->exists ? $news->only(['title', 'slug', 'excerpt', 'content', 'status', 'published_at']) : null;
        $status = $validated['form_status'];
        $publishedAt = $status === 'published'
            ? ($news->published_at ?? now())
            : null;

        $news->fill([
            'author_id' => $news->author_id ?: Auth::id(),
            'title' => $validated['title'],
            'slug' => Str::slug($validated['slug']),
            'excerpt' => $validated['excerpt'],
            'content' => $validated['content'],
            'status' => $status,
            'published_at' => $publishedAt,
        ]);
        $news->save();

        AuditLog::record(
            action: $oldValues === null ? 'news.created' : 'news.updated',
            entity: $news,
            oldValues: $oldValues,
            newValues: $news->only(['title', 'slug', 'excerpt', 'status', 'published_at']),
        );

        $this->resetForm();
        unset($this->summary, $this->newsItems);

        Flux::toast(variant: 'success', text: __('Berita disimpan.'));
    }

    public function publish(News $news): void
    {
        $this->changeStatus($news, 'published');
    }

    public function archive(News $news): void
    {
        $this->changeStatus($news, 'archived');
    }

    public function delete(News $news): void
    {
        $oldValues = $news->only(['title', 'slug', 'status', 'published_at']);
        $news->delete();

        AuditLog::record(
            action: 'news.deleted',
            entity: $news,
            oldValues: $oldValues,
        );

        unset($this->summary, $this->newsItems);
        Flux::toast(variant: 'success', text: __('Berita dihapus.'));
    }

    public function statusLabel(string $status): string
    {
        return match ($status) {
            'published' => __('Published'),
            'archived' => __('Archived'),
            default => __('Draft'),
        };
    }

    public function statusColor(string $status): string
    {
        return match ($status) {
            'published' => 'green',
            'archived' => 'zinc',
            default => 'amber',
        };
    }

    private function changeStatus(News $news, string $status): void
    {
        $oldValues = $news->only(['status', 'published_at']);
        $news->forceFill([
            'status' => $status,
            'published_at' => $status === 'published' ? ($news->published_at ?? now()) : null,
        ])->save();

        AuditLog::record(
            action: 'news.status_changed',
            entity: $news,
            oldValues: $oldValues,
            newValues: $news->only(['status', 'published_at']),
        );

        unset($this->summary, $this->newsItems);
        Flux::toast(variant: 'success', text: __('Status berita diperbarui.'));
    }

    private function resetForm(): void
    {
        $this->reset(['editing_id', 'title', 'slug', 'excerpt', 'content']);
        $this->form_status = 'draft';
        $this->resetErrorBag();
    }
}; ?>

<section class="w-full space-y-6 p-6 lg:p-8">
    <div class="space-y-2">
        <flux:heading size="xl">{{ __('Manajemen Berita') }}</flux:heading>
        <flux:text class="max-w-3xl">
            {{ __('Kelola berita dan pengumuman reuni dengan status draft, published, atau archived.') }}
        </flux:text>
    </div>

    <div class="grid gap-3 sm:grid-cols-[minmax(14rem,1fr)_12rem]">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :label="__('Cari')" :placeholder="__('Judul, isi, penulis')" />
        <flux:select wire:model.live="status" :label="__('Status')">
            <flux:select.option value="all">{{ __('Semua') }}</flux:select.option>
            <flux:select.option value="draft">{{ __('Draft') }}</flux:select.option>
            <flux:select.option value="published">{{ __('Published') }}</flux:select.option>
            <flux:select.option value="archived">{{ __('Archived') }}</flux:select.option>
        </flux:select>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <flux:card>
            <flux:text>{{ __('Draft') }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->summary['draft'] }}</div>
        </flux:card>
        <flux:card>
            <flux:text>{{ __('Published') }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->summary['published'] }}</div>
        </flux:card>
        <flux:card>
            <flux:text>{{ __('Archived') }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->summary['archived'] }}</div>
        </flux:card>
    </div>

    <div class="grid gap-6 xl:grid-cols-[26rem_1fr]">
        <form wire:submit="save" class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="space-y-5">
                <div>
                    <flux:heading size="lg">{{ $editing_id ? __('Edit Berita') : __('Tambah Berita') }}</flux:heading>
                    <flux:text class="mt-2">{{ __('Isi pengumuman singkat dan publish saat siap dibaca alumni.') }}</flux:text>
                </div>

                <flux:input wire:model.live="title" :label="__('Judul')" />
                <flux:input wire:model="slug" :label="__('Slug')" />
                <flux:textarea wire:model="excerpt" :label="__('Ringkasan')" rows="3" />
                <flux:textarea wire:model="content" :label="__('Konten')" rows="8" />

                <flux:select wire:model="form_status" :label="__('Status')">
                    <flux:select.option value="draft">{{ __('Draft') }}</flux:select.option>
                    <flux:select.option value="published">{{ __('Published') }}</flux:select.option>
                    <flux:select.option value="archived">{{ __('Archived') }}</flux:select.option>
                </flux:select>

                <div class="flex gap-2">
                    <flux:button type="submit" variant="primary" icon="check" class="flex-1" wire:loading.attr="disabled">
                        {{ __('Simpan') }}
                    </flux:button>
                    @if ($editing_id)
                        <flux:button type="button" variant="ghost" wire:click="cancelEdit">
                            {{ __('Batal') }}
                        </flux:button>
                    @endif
                </div>
            </div>
        </form>

        <flux:table :paginate="$this->newsItems" pagination:scroll-to="body">
            <flux:table.columns>
                <flux:table.column>{{ __('Judul') }}</flux:table.column>
                <flux:table.column>{{ __('Penulis') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column>{{ __('Publikasi') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Aksi') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->newsItems as $news)
                    <flux:table.row :key="$news->id">
                        <flux:table.cell variant="strong">
                            <div class="grid gap-1">
                                <span>{{ $news->title }}</span>
                                <span class="text-xs font-normal text-zinc-500 dark:text-zinc-400">{{ $news->excerpt ?: $news->slug }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>{{ $news->author?->name ?: '-' }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge color="{{ $this->statusColor($news->status) }}">{{ $this->statusLabel($news->status) }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>{{ $news->published_at?->translatedFormat('d F Y H:i') ?: '-' }}</flux:table.cell>
                        <flux:table.cell align="end">
                            <div class="flex justify-end gap-2">
                                <flux:button size="sm" variant="ghost" icon="pencil" wire:click="edit({{ $news->id }})">{{ __('Edit') }}</flux:button>
                                @if ($news->status !== 'published')
                                    <flux:button size="sm" variant="ghost" icon="megaphone" wire:click="publish({{ $news->id }})">{{ __('Publish') }}</flux:button>
                                @endif
                                @if ($news->status !== 'archived')
                                    <flux:button size="sm" variant="ghost" icon="archive-box" wire:click="archive({{ $news->id }})">{{ __('Archive') }}</flux:button>
                                @endif
                                <flux:button size="sm" variant="danger" icon="trash" wire:click="delete({{ $news->id }})" wire:confirm="{{ __('Hapus berita ini?') }}">{{ __('Hapus') }}</flux:button>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5">
                            <div class="py-10 text-center">
                                <flux:heading size="lg">{{ __('Belum ada berita') }}</flux:heading>
                                <flux:text>{{ __('Berita dan pengumuman yang dibuat panitia akan tampil di sini.') }}</flux:text>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</section>
