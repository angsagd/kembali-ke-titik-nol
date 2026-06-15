@props([
    'label' => __('Konten'),
    'name' => 'content',
    'rows' => 12,
    'editorId' => 'rich-text-editor',
    'imageModel' => null,
])

<flux:field>
    <flux:label>{{ $label }}</flux:label>

    <div
        class="overflow-hidden rounded-lg border border-zinc-200 bg-white focus-within:border-ktn-forest focus-within:ring-1 focus-within:ring-ktn-forest dark:border-zinc-700 dark:bg-zinc-900"
        data-rich-text-editor
        data-rich-text-editor-id="{{ $editorId }}"
    >
        <div class="flex flex-wrap items-center gap-1 border-b border-zinc-200 bg-zinc-50 p-2 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:button type="button" size="sm" variant="ghost" data-rich-text-action="heading" tooltip="{{ __('Judul bagian') }}" aria-label="{{ __('Judul bagian') }}">
                H2
            </flux:button>
            <flux:button type="button" size="sm" variant="ghost" data-rich-text-action="bold" tooltip="{{ __('Tebal') }}" aria-label="{{ __('Tebal') }}">
                <strong>B</strong>
            </flux:button>
            <flux:button type="button" size="sm" variant="ghost" data-rich-text-action="italic" tooltip="{{ __('Miring') }}" aria-label="{{ __('Miring') }}">
                <em>I</em>
            </flux:button>

            <span class="mx-1 h-5 w-px bg-zinc-200 dark:bg-zinc-700" aria-hidden="true"></span>

            <flux:button type="button" size="sm" variant="ghost" icon="list-bullet" data-rich-text-action="bullet-list" tooltip="{{ __('Daftar berpoin') }}" aria-label="{{ __('Daftar berpoin') }}" />
            <flux:button type="button" size="sm" variant="ghost" icon="numbered-list" data-rich-text-action="numbered-list" tooltip="{{ __('Daftar bernomor') }}" aria-label="{{ __('Daftar bernomor') }}" />
            <flux:button type="button" size="sm" variant="ghost" icon="chat-bubble-bottom-center-text" data-rich-text-action="quote" tooltip="{{ __('Kutipan') }}" aria-label="{{ __('Kutipan') }}" />
            <flux:button type="button" size="sm" variant="ghost" icon="link" data-rich-text-action="link" tooltip="{{ __('Tautan') }}" aria-label="{{ __('Tautan') }}" />

            @if ($imageModel)
                <flux:button type="button" size="sm" variant="ghost" icon="photo" data-rich-text-action="image" tooltip="{{ __('Unggah gambar') }}" aria-label="{{ __('Unggah gambar') }}" />
                <input
                    type="file"
                    class="sr-only"
                    accept="image/jpeg,image/png,image/webp"
                    wire:model="{{ $imageModel }}"
                    data-rich-text-image-input
                >
            @endif
        </div>

        <textarea
            rows="{{ $rows }}"
            data-rich-text-input
            {{ $attributes->class([
                'block w-full resize-y border-0 bg-transparent px-3 py-3 text-sm leading-6 text-zinc-900 outline-none placeholder:text-zinc-400 focus:ring-0 dark:text-white',
            ]) }}
        ></textarea>
    </div>

    <flux:description>
        {{ $imageModel
            ? __('Gunakan toolbar untuk memformat konten dan mengunggah gambar JPEG, PNG, atau WebP maksimal 5 MB.')
            : __('Gunakan toolbar untuk mengatur judul, penekanan, daftar, kutipan, dan tautan.') }}
    </flux:description>
    <flux:error :name="$name" />
    @if ($imageModel)
        <flux:error :name="$imageModel" />
        <flux:text wire:loading wire:target="{{ $imageModel }}" class="text-sm text-ktn-forest">
            {{ __('Mengunggah gambar...') }}
        </flux:text>
    @endif
</flux:field>
