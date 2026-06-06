<?php

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Audit Log')] class extends Component {
    use WithPagination;

    #[Url]
    public string $user_id = 'all';

    #[Url]
    public string $action = '';

    #[Url]
    public ?string $date_from = null;

    #[Url]
    public ?string $date_to = null;

    public function updatedUserId(): void
    {
        $this->resetPage();
    }

    public function updatedAction(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    /**
     * @return Collection<int, User>
     */
    #[Computed]
    public function users(): Collection
    {
        return User::query()
            ->whereHas('auditLogs')
            ->orderBy('name')
            ->get(['id', 'name', 'whatsapp_number']);
    }

    /**
     * @return array<int, string>
     */
    #[Computed]
    public function actions(): array
    {
        return AuditLog::query()
            ->whereNotNull('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action')
            ->all();
    }

    #[Computed]
    public function auditLogs(): LengthAwarePaginator
    {
        $action = trim($this->action);

        return AuditLog::query()
            ->with('user')
            ->when($this->user_id !== 'all', function ($query): void {
                $query->where('user_id', (int) $this->user_id);
            })
            ->when($action !== '', function ($query) use ($action): void {
                $query->where('action', $action);
            })
            ->when($this->date_from, function ($query): void {
                $query->whereDate('created_at', '>=', $this->date_from);
            })
            ->when($this->date_to, function ($query): void {
                $query->whereDate('created_at', '<=', $this->date_to);
            })
            ->latest()
            ->paginate(20);
    }

    public function jsonSummary(?array $values): string
    {
        if ($values === null || $values === []) {
            return '-';
        }

        return str(json_encode($values, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE))->limit(120)->toString();
    }
}; ?>

<section class="w-full space-y-6 p-6 lg:p-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="space-y-2">
            <flux:heading size="xl">{{ __('Audit Log') }}</flux:heading>
            <flux:text class="max-w-2xl">
                {{ __('Pantau aktivitas penting sistem untuk kebutuhan pelacakan dan kontrol superadmin.') }}
            </flux:text>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 xl:w-[48rem] xl:grid-cols-4">
            <flux:select wire:model.live="user_id" :label="__('User')">
                <flux:select.option value="all">{{ __('Semua') }}</flux:select.option>
                @foreach ($this->users as $user)
                    <flux:select.option value="{{ $user->id }}">{{ $user->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="action" :label="__('Aksi')">
                <flux:select.option value="">{{ __('Semua') }}</flux:select.option>
                @foreach ($this->actions as $actionName)
                    <flux:select.option value="{{ $actionName }}">{{ $actionName }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input wire:model.live="date_from" :label="__('Dari')" type="date" />
            <flux:input wire:model.live="date_to" :label="__('Sampai')" type="date" />
        </div>
    </div>

    <flux:table :paginate="$this->auditLogs" pagination:scroll-to="body">
        <flux:table.columns>
            <flux:table.column>{{ __('Waktu') }}</flux:table.column>
            <flux:table.column>{{ __('User') }}</flux:table.column>
            <flux:table.column>{{ __('Aksi') }}</flux:table.column>
            <flux:table.column>{{ __('Entitas') }}</flux:table.column>
            <flux:table.column>{{ __('Data Baru') }}</flux:table.column>
            <flux:table.column>{{ __('IP') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->auditLogs as $auditLog)
                <flux:table.row :key="$auditLog->id">
                    <flux:table.cell>{{ $auditLog->created_at?->translatedFormat('d F Y H:i') ?: '-' }}</flux:table.cell>
                    <flux:table.cell>{{ $auditLog->user?->name ?: '-' }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge>{{ $auditLog->action }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>{{ collect([$auditLog->entity_type, $auditLog->entity_id])->filter()->join('#') ?: '-' }}</flux:table.cell>
                    <flux:table.cell>{{ $this->jsonSummary($auditLog->new_values) }}</flux:table.cell>
                    <flux:table.cell>{{ $auditLog->ip_address ?: '-' }}</flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6">
                        <div class="py-10 text-center">
                            <flux:heading size="lg">{{ __('Belum ada audit log') }}</flux:heading>
                            <flux:text>{{ __('Aktivitas penting yang dicatat sistem akan tampil di sini.') }}</flux:text>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</section>
