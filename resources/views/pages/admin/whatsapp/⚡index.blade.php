<?php

use App\Models\AuditLog;
use App\Models\WhatsappImport;
use App\Services\WhatsAppAnalyzer\WhatsappImportProcessor;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[Title('WhatsApp Import')] class extends Component {
    use WithFileUploads, WithPagination;

    private const MAX_IMPORT_CONTENT_BYTES = 6 * 1024 * 1024;

    private const PROCESS_MEMORY_LIMIT = '512M';

    public ?TemporaryUploadedFile $chat_file = null;

    public ?string $notes = null;

    public ?int $conclusionImportId = null;

    public string $conclusionText = '';

    public function mount(): void
    {
        Gate::authorize('import-whatsapp-analytics');
    }

    public function saveImport(): void
    {
        Gate::authorize('import-whatsapp-analytics');

        $validated = $this->validate([
            'chat_file' => ['required', 'file', 'extensions:txt,zip', 'max:6144'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($this->chat_file && $this->isOversizedZipUpload($this->chat_file)) {
            throw ValidationException::withMessages([
                'chat_file' => __('File ZIP maksimum 2 MB.'),
            ]);
        }

        $path = $this->chat_file?->store('whatsapp-imports');

        $whatsappImport = WhatsappImport::query()->create([
            'uploaded_by' => Auth::id(),
            'file_name' => $this->chat_file?->getClientOriginalName(),
            'file_path' => $path,
            'status' => 'uploaded',
            'notes' => $validated['notes'],
        ]);

        AuditLog::record(
            action: 'whatsapp_import.uploaded',
            entity: $whatsappImport,
            newValues: $whatsappImport->only(['file_name', 'status']),
        );

        $this->reset(['chat_file', 'notes']);
        unset($this->imports);

        Flux::toast(variant: 'success', text: __('File WhatsApp disimpan. Klik proses untuk membuat analytics.'));
    }

    public function processImport(WhatsappImport $whatsappImport, WhatsappImportProcessor $processor): void
    {
        Gate::authorize('import-whatsapp-analytics');

        $whatsappImport->forceFill(['status' => 'processing'])->save();

        $previousMemoryLimit = ini_get('memory_limit');
        ini_set('memory_limit', self::PROCESS_MEMORY_LIMIT);

        try {
            $contents = $this->importContents($whatsappImport);
            $processor->process($whatsappImport, $contents);

            AuditLog::record(
                action: 'whatsapp_import.processed',
                entity: $whatsappImport,
                newValues: $whatsappImport->only(['status', 'total_messages', 'total_participants', 'processed_at']),
            );

            Flux::toast(variant: 'success', text: __('WhatsApp Analytics selesai diproses.'));
        } catch (Throwable $exception) {
            $whatsappImport->forceFill([
                'status' => 'failed',
                'notes' => trim(($whatsappImport->notes ? $whatsappImport->notes."\n" : '').$exception->getMessage()),
            ])->save();

            Flux::toast(variant: 'danger', text: __('Gagal memproses file WhatsApp.'));
        } finally {
            if (is_string($previousMemoryLimit)) {
                ini_set('memory_limit', $previousMemoryLimit);
            }
        }

        unset($this->summary, $this->imports);
    }

    private function isOversizedZipUpload(TemporaryUploadedFile $file): bool
    {
        return Str::lower($file->getClientOriginalExtension()) === 'zip'
            && $file->getSize() > 2048 * 1024;
    }

    private function importContents(WhatsappImport $whatsappImport): string
    {
        if (! $whatsappImport->file_path) {
            return '';
        }

        if ($this->isZipImport($whatsappImport)) {
            return $this->extractLargestTextFileContents($whatsappImport);
        }

        $size = Storage::size($whatsappImport->file_path);

        if (is_int($size) && $size > self::MAX_IMPORT_CONTENT_BYTES) {
            throw new RuntimeException(__('Ukuran file chat melebihi batas 6 MB.'));
        }

        return Storage::get($whatsappImport->file_path);
    }

    private function isZipImport(WhatsappImport $whatsappImport): bool
    {
        $fileName = Str::lower((string) $whatsappImport->file_name);
        $filePath = Str::lower((string) $whatsappImport->file_path);

        return str_ends_with($fileName, '.zip') || str_ends_with($filePath, '.zip');
    }

    private function extractLargestTextFileContents(WhatsappImport $whatsappImport): string
    {
        $zipPath = Storage::path($whatsappImport->file_path);
        $archive = new ZipArchive();
        $opened = $archive->open($zipPath);

        if ($opened !== true) {
            throw new RuntimeException(__('File ZIP tidak dapat dibuka.'));
        }

        $largestTextIndex = null;
        $largestTextSize = -1;

        for ($index = 0; $index < $archive->numFiles; $index++) {
            $fileStat = $archive->statIndex($index);

            if (! is_array($fileStat)) {
                continue;
            }

            $entryName = (string) ($fileStat['name'] ?? '');
            $entrySize = (int) ($fileStat['size'] ?? 0);

            if (! $this->isTextEntry($entryName) || str_ends_with($entryName, '/')) {
                continue;
            }

            if ($entrySize > $largestTextSize) {
                $largestTextSize = $entrySize;
                $largestTextIndex = $index;
            }
        }

        if ($largestTextIndex === null) {
            $archive->close();

            throw new RuntimeException(__('ZIP harus berisi minimal satu file .txt.'));
        }

        if ($largestTextSize > self::MAX_IMPORT_CONTENT_BYTES) {
            $archive->close();

            throw new RuntimeException(__('Ukuran file txt di dalam ZIP melebihi batas 6 MB.'));
        }

        $contents = $archive->getFromIndex($largestTextIndex);
        $archive->close();

        if (! is_string($contents)) {
            throw new RuntimeException(__('File txt di dalam ZIP tidak dapat dibaca.'));
        }

        return $contents;
    }

    private function isTextEntry(string $entryName): bool
    {
        return Str::lower(pathinfo($entryName, PATHINFO_EXTENSION)) === 'txt';
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function summary(): array
    {
        return [
            'uploaded' => WhatsappImport::query()->where('status', 'uploaded')->count(),
            'completed' => WhatsappImport::query()->where('status', 'completed')->count(),
            'failed' => WhatsappImport::query()->where('status', 'failed')->count(),
            'messages' => WhatsappImport::query()->where('status', 'completed')->sum('total_messages'),
        ];
    }

    #[Computed]
    public function imports(): LengthAwarePaginator
    {
        return WhatsappImport::query()
            ->with('uploader')
            ->latest()
            ->paginate(15);
    }

    public function statusLabel(string $status): string
    {
        return match ($status) {
            'processing' => __('Processing'),
            'completed' => __('Completed'),
            'failed' => __('Failed'),
            default => __('Uploaded'),
        };
    }

    public function statusColor(string $status): string
    {
        return match ($status) {
            'completed' => 'green',
            'failed' => 'red',
            'processing' => 'amber',
            default => 'zinc',
        };
    }

    public function openConclusionModal(WhatsappImport $whatsappImport): void
    {
        Gate::authorize('import-whatsapp-analytics');
        abort_unless($whatsappImport->status === 'completed', 422);

        $this->conclusionImportId = $whatsappImport->id;
        $this->conclusionText = $whatsappImport->conclusion ?? '';

        Flux::modal('conclusion-modal')->show();
    }

    public function saveConclusion(): void
    {
        Gate::authorize('import-whatsapp-analytics');
        abort_if($this->conclusionImportId === null, 404);

        $whatsappImport = WhatsappImport::findOrFail($this->conclusionImportId);
        abort_unless($whatsappImport->status === 'completed', 422);

        $validated = $this->validate([
            'conclusionText' => ['nullable', 'string', 'max:50000'],
        ]);

        $whatsappImport->forceFill([
            'conclusion' => filled($validated['conclusionText']) ? $validated['conclusionText'] : null,
        ])->save();

        AuditLog::record(
            action: 'whatsapp_import.conclusion_updated',
            entity: $whatsappImport,
            newValues: ['has_conclusion' => filled($validated['conclusionText'])],
        );

        Flux::modal('conclusion-modal')->close();
        Flux::toast(variant: 'success', text: __('Kesimpulan berhasil disimpan.'));

        unset($this->imports);
    }
}; ?>

<section class="w-full space-y-6 p-6 lg:p-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="space-y-2">
            <flux:heading size="xl">{{ __('WhatsApp Import') }}</flux:heading>
            <flux:text class="max-w-3xl">
                {{ __('Unggah export chat WhatsApp untuk menghasilkan statistik nostalgia. Raw chat tidak ditampilkan di sistem.') }}
            </flux:text>
        </div>

        <flux:button variant="ghost" :href="route('whatsapp.analytics')" wire:navigate>
            {{ __('Lihat Analytics') }}
        </flux:button>
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        <flux:card>
            <flux:text>{{ __('Uploaded') }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->summary['uploaded'] }}</div>
        </flux:card>
        <flux:card>
            <flux:text>{{ __('Completed') }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->summary['completed'] }}</div>
        </flux:card>
        <flux:card>
            <flux:text>{{ __('Failed') }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->summary['failed'] }}</div>
        </flux:card>
        <flux:card>
            <flux:text>{{ __('Pesan Terolah') }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->summary['messages'] }}</div>
        </flux:card>
    </div>

    <div class="grid gap-6 xl:grid-cols-[25rem_1fr]">
        <form
            wire:submit="saveImport"
            class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
            x-data="{ uploading: false, progress: 0 }"
            x-on:livewire-upload-start="uploading = true"
            x-on:livewire-upload-finish="uploading = false"
            x-on:livewire-upload-cancel="uploading = false"
            x-on:livewire-upload-error="uploading = false"
            x-on:livewire-upload-progress="progress = $event.detail.progress"
        >
            <div class="space-y-5">
                <div>
                    <flux:heading size="lg">{{ __('Upload Export Chat') }}</flux:heading>
                    <flux:text class="mt-2">{{ __('Gunakan file .txt atau .zip hasil export WhatsApp tanpa media. Maksimum 6 MB untuk .txt dan 2 MB untuk .zip.') }}</flux:text>
                </div>

                <flux:input wire:model="chat_file" :label="__('File chat')" type="file" accept=".txt,.zip,text/plain,application/zip" />
                <div x-cloak x-show="uploading" class="space-y-2">
                    <div class="flex justify-between gap-3 text-sm text-zinc-600 dark:text-zinc-300">
                        <span>{{ __('Mengunggah file...') }}</span>
                        <span x-text="`${progress}%`" class="tabular-nums"></span>
                    </div>
                    <progress max="100" x-bind:value="progress" class="h-2 w-full accent-emerald-700"></progress>
                </div>
                <flux:textarea wire:model="notes" :label="__('Catatan')" rows="3" />

                <flux:button
                    type="submit"
                    variant="primary"
                    icon="arrow-up-tray"
                    class="w-full"
                    x-bind:disabled="uploading"
                    wire:loading.attr="disabled"
                    wire:target="saveImport"
                >
                    {{ __('Simpan Import') }}
                </flux:button>
            </div>
        </form>

        <flux:table :paginate="$this->imports" pagination:scroll-to="body">
            <flux:table.columns>
                <flux:table.column>{{ __('File') }}</flux:table.column>
                <flux:table.column>{{ __('Uploader') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column>{{ __('Pesan') }}</flux:table.column>
                <flux:table.column>{{ __('Periode') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Aksi') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->imports as $whatsappImport)
                    <flux:table.row :key="$whatsappImport->id">
                        <flux:table.cell variant="strong">
                            <div class="grid gap-1">
                                <span>{{ $whatsappImport->file_name ?: '-' }}</span>
                                <span class="text-xs font-normal text-zinc-500 dark:text-zinc-400">{{ $whatsappImport->created_at?->translatedFormat('d F Y H:i') }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>{{ $whatsappImport->uploader?->name ?: '-' }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge color="{{ $this->statusColor($whatsappImport->status) }}">{{ $this->statusLabel($whatsappImport->status) }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>{{ $whatsappImport->total_messages }}</flux:table.cell>
                        <flux:table.cell>
                            {{ collect([$whatsappImport->import_start_date?->format('Y-m-d'), $whatsappImport->import_end_date?->format('Y-m-d')])->filter()->join(' - ') ?: '-' }}
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <div class="flex items-center justify-end gap-2">
                                <flux:button size="sm" variant="ghost" icon="play" wire:click="processImport({{ $whatsappImport->id }})" wire:loading.attr="disabled">
                                    {{ __('Proses') }}
                                </flux:button>
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="pencil-square"
                                    wire:click="openConclusionModal({{ $whatsappImport->id }})"
                                    :disabled="$whatsappImport->status !== 'completed'"
                                    :title="$whatsappImport->status !== 'completed' ? __('Proses import terlebih dahulu') : __('Edit Kesimpulan')"
                                >
                                    {{ __('Kesimpulan') }}
                                </flux:button>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6">
                            <div class="py-10 text-center">
                                <flux:heading size="lg">{{ __('Belum ada import') }}</flux:heading>
                                <flux:text>{{ __('File export WhatsApp yang diunggah panitia akan tampil di sini.') }}</flux:text>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <flux:modal name="conclusion-modal" class="w-full max-w-3xl">
        <div class="space-y-5">
            <div>
                <flux:heading size="lg">{{ __('Kesimpulan Analisis') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Tulis kesimpulan dalam format Markdown. Teks ini akan ditampilkan di tab Kesimpulan halaman analytics.') }}</flux:text>
            </div>

            <div class="grid gap-5 lg:grid-cols-2">
                <div class="space-y-1.5">
                    <flux:label>{{ __('Editor Markdown') }}</flux:label>
                    <flux:textarea
                        wire:model="conclusionText"
                        rows="18"
                        placeholder="## Kesimpulan&#10;&#10;Tuliskan analisis dan kesimpulan Anda di sini..."
                        class="font-mono text-sm"
                    />
                    @error('conclusionText')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </div>

                <div class="space-y-1.5">
                    <flux:label>{{ __('Preview') }}</flux:label>
                    <div class="ktn-prose h-[432px] overflow-y-auto rounded-lg border border-zinc-200 px-4 py-3 dark:border-zinc-700">
                        @if (filled($conclusionText))
                            @php
                                $previewConverter = new \League\CommonMark\GithubFlavoredMarkdownConverter([
                                    'html_input' => 'strip',
                                    'allow_unsafe_links' => false,
                                    'max_nesting_level' => 10,
                                ]);
                            @endphp
                            {!! $previewConverter->convert($conclusionText) !!}
                        @else
                            <p class="text-zinc-400 dark:text-zinc-500">{{ __('Preview akan muncul saat Anda mulai mengetik...') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Batal') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" wire:click="saveConclusion" wire:loading.attr="disabled" wire:target="saveConclusion">
                    {{ __('Simpan Kesimpulan') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</section>
