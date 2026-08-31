<?php

use App\Models\AuditLog;
use App\Models\DocumentationCategory;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Kategori Dokumentasi')] class extends Component {
    public ?int $editingId = null;
    public string $name = '';
    public int|string|null $sortOrder = null;
    public bool $isActive = true;

    #[Computed]
    public function categories(): Collection
    {
        return DocumentationCategory::query()->withCount('mediaItems')->orderByRaw('sort_order is null')->orderBy('sort_order')->orderBy('name')->get();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique(DocumentationCategory::class)->ignore($this->editingId)],
            'sortOrder' => ['nullable', 'integer', 'between:1,65535'],
            'isActive' => ['boolean'],
        ]);

        $category = $this->editingId
            ? DocumentationCategory::query()->findOrFail($this->editingId)
            : new DocumentationCategory;
        $oldValues = $category->exists ? $category->only(['name', 'slug', 'sort_order', 'is_active']) : [];
        $category->fill([
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name'], $category->id),
            'sort_order' => $validated['sortOrder'],
            'is_active' => $validated['isActive'],
        ])->save();

        AuditLog::record(
            action: $oldValues === [] ? 'documentation_category.created' : 'documentation_category.updated',
            entity: $category,
            oldValues: $oldValues,
            newValues: $category->only(['name', 'slug', 'sort_order', 'is_active']),
        );

        $this->resetForm();
        unset($this->categories);
        Flux::toast(variant: 'success', text: __('Kategori dokumentasi disimpan.'));
    }

    public function edit(int $categoryId): void
    {
        $category = DocumentationCategory::query()->findOrFail($categoryId);
        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->sortOrder = $category->sort_order;
        $this->isActive = $category->is_active;
    }

    public function toggleActive(int $categoryId): void
    {
        $category = DocumentationCategory::query()->findOrFail($categoryId);
        $oldValues = ['is_active' => $category->is_active];
        $category->update(['is_active' => ! $category->is_active]);
        AuditLog::record(action: 'documentation_category.status_changed', entity: $category, oldValues: $oldValues, newValues: ['is_active' => $category->is_active]);
        unset($this->categories);
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'sortOrder']);
        $this->isActive = true;
        $this->resetErrorBag();
    }

    private function uniqueSlug(string $name, ?int $ignoreId): string
    {
        $base = Str::slug($name) ?: 'kategori';
        $slug = $base;
        $suffix = 2;

        while (DocumentationCategory::query()->where('slug', $slug)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
};
?>

<section class="w-full space-y-6 p-6 lg:p-8">
    <div><flux:heading size="xl">{{ __('Kategori Dokumentasi') }}</flux:heading><flux:text class="mt-2">{{ __('Kelola master kategori untuk Arsip Dokumentasi Geodesi 96.') }}</flux:text></div>
    <div class="grid gap-6 xl:grid-cols-[22rem_1fr]">
        <form wire:submit="save" class="space-y-5 rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="lg">{{ $editingId ? __('Ubah Kategori') : __('Tambah Kategori') }}</flux:heading>
            <flux:input wire:model="name" :label="__('Nama')" required />
            <flux:input wire:model="sortOrder" :label="__('Urutan')" type="number" min="1" />
            <flux:switch wire:model="isActive" :label="__('Kategori aktif')" />
            <div class="flex gap-2"><flux:button type="submit" variant="primary">{{ __('Simpan') }}</flux:button>@if ($editingId)<flux:button type="button" variant="ghost" wire:click="resetForm">{{ __('Batal') }}</flux:button>@endif</div>
        </form>
        <div class="space-y-3">
            @foreach ($this->categories as $category)
                <flux:card wire:key="documentation-category-{{ $category->id }}" class="flex items-center justify-between gap-4">
                    <div><flux:heading>{{ $category->name }}</flux:heading><flux:text>{{ __('Urutan: :order · :count dokumentasi', ['order' => $category->sort_order ?: '-', 'count' => $category->media_items_count]) }}</flux:text></div>
                    <div class="flex gap-2"><flux:badge color="{{ $category->is_active ? 'green' : 'zinc' }}">{{ $category->is_active ? __('Aktif') : __('Nonaktif') }}</flux:badge><flux:button size="sm" variant="ghost" wire:click="edit({{ $category->id }})">{{ __('Ubah') }}</flux:button><flux:button size="sm" variant="ghost" wire:click="toggleActive({{ $category->id }})">{{ $category->is_active ? __('Nonaktifkan') : __('Aktifkan') }}</flux:button></div>
                </flux:card>
            @endforeach
        </div>
    </div>
</section>
