<?php

use App\Models\AuditLog;
use App\Models\WhatsappImport;
use App\Services\WhatsappChatAnalyzer;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[Title('WhatsApp Import')] class extends Component {
    use WithFileUploads, WithPagination;

    public ?TemporaryUploadedFile $chat_file = null;

    public ?string $notes = null;

    public function mount(): void
    {
        Gate::authorize('import-whatsapp-analytics');
    }

    public function saveImport(): void
    {
        Gate::authorize('import-whatsapp-analytics');

        $validated = $this->validate([
            'chat_file' => ['required', 'file', 'extensions:txt,zip', 'max:10240'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

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

    public function processImport(WhatsappImport $whatsappImport, WhatsappChatAnalyzer $analyzer): void
    {
        Gate::authorize('import-whatsapp-analytics');

        $whatsappImport->forceFill(['status' => 'processing'])->save();

        try {
            $contents = $this->importContents($whatsappImport);
            $analysis = $analyzer->analyze($contents);

            DB::transaction(function () use ($whatsappImport, $analysis): void {
                $whatsappImport->statistics()->delete();

                foreach ($analysis['statistics'] as $statistic) {
                    $whatsappImport->statistics()->create($statistic);
                }

                $whatsappImport->forceFill([
                    'import_start_date' => $analysis['import_start_date'],
                    'import_end_date' => $analysis['import_end_date'],
                    'total_messages' => $analysis['total_messages'],
                    'total_participants' => $analysis['total_participants'],
                    'status' => 'completed',
                    'processed_at' => now(),
                ])->save();
            });

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
        }

        unset($this->summary, $this->imports);
    }

    private function importContents(WhatsappImport $whatsappImport): string
    {
        if (! $whatsappImport->file_path) {
            return '';
        }

        if ($this->isZipImport($whatsappImport)) {
            return $this->extractLargestTextFileContents($whatsappImport);
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
                    <flux:text class="mt-2">{{ __('Gunakan file .txt atau .zip hasil export WhatsApp tanpa media, maksimum 10 MB.') }}</flux:text>
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
                            <flux:button size="sm" variant="ghost" icon="play" wire:click="processImport({{ $whatsappImport->id }})" wire:loading.attr="disabled">
                                {{ __('Proses') }}
                            </flux:button>
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
</section>
