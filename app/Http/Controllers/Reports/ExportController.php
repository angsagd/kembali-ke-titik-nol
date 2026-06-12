<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Alumni;
use App\Models\Donation;
use App\Models\Payment;
use App\Models\Room;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function alumni(): StreamedResponse
    {
        return $this->csv('alumni-export.csv', [
            'Nama',
            'NIM',
            'Nama Panggilan',
            'WhatsApp',
            'Email',
            'Kota',
            'Negara',
            'Perusahaan',
            'Jabatan',
            'Status Alumni',
            'Status RSVP',
            'Status Pembayaran',
            'Status Donasi',
            'Kamar',
            'Profil Lengkap',
            'Terakhir Diperbarui',
        ], function (): void {
            Alumni::query()
                ->with(['user', 'currentCity', 'currentCountry', 'payment', 'donation', 'roomAssignment.room'])
                ->orderBy('full_name')
                ->lazy()
                ->each(function (Alumni $alumni): void {
                    $this->writeCsvRow([
                        $alumni->full_name,
                        $alumni->student_number,
                        $alumni->nickname,
                        $alumni->user?->whatsapp_number,
                        $alumni->email,
                        $alumni->currentCity?->name,
                        $alumni->currentCountry?->name,
                        $alumni->company,
                        $alumni->job_title,
                        $this->alumniStatusLabel($alumni->alumni_status),
                        $this->rsvpStatusLabel($alumni->rsvp_status),
                        $this->paymentStatusLabel($alumni->payment?->status),
                        $this->donationStatusLabel($alumni->donation !== null),
                        $alumni->roomAssignment?->room?->room_name,
                        $alumni->is_profile_completed ? 'Ya' : 'Tidak',
                        $alumni->updated_at?->toDateTimeString(),
                    ]);
                });
        });
    }

    public function rsvp(): StreamedResponse
    {
        return $this->csv('rsvp-export.csv', [
            'Nama',
            'NIM',
            'WhatsApp',
            'Status RSVP',
            'Kehadiran',
            'Jumlah Keluarga Tambahan',
            'Total Peserta',
            'Kaos Alumni',
            'Kaos Keluarga',
            'Terakhir Diperbarui',
        ], function (): void {
            Alumni::query()
                ->with(['user', 'rsvpGuests'])
                ->orderBy('full_name')
                ->lazy()
                ->each(function (Alumni $alumni): void {
                    $isAttending = $alumni->rsvp_status === 'attending';
                    $familyMembersCount = $isAttending && $alumni->rsvp_party_type === 'family'
                        ? $alumni->family_members_count
                        : 0;
                    $familyShirts = $alumni->rsvpGuests
                        ->map(fn ($guest): string => sprintf(
                            'Keluarga %d: %s / %s',
                            $guest->sequence,
                            $this->shirtTypeLabel($guest->shirt_type),
                            $guest->shirt_size,
                        ))
                        ->join('; ');

                    $this->writeCsvRow([
                        $alumni->full_name,
                        $alumni->student_number,
                        $alumni->user?->whatsapp_number,
                        $this->rsvpStatusLabel($alumni->rsvp_status),
                        $isAttending ? $this->partyTypeLabel($alumni->rsvp_party_type) : null,
                        $familyMembersCount,
                        $isAttending ? 1 + $familyMembersCount : 0,
                        filled($alumni->shirt_type) && filled($alumni->shirt_size)
                            ? $this->shirtTypeLabel($alumni->shirt_type).' / '.$alumni->shirt_size
                            : null,
                        $familyShirts,
                        $alumni->updated_at?->toDateTimeString(),
                    ]);
                });
        });
    }

    public function payments(): StreamedResponse
    {
        return $this->csv('payment-export.csv', [
            'Nama',
            'NIM',
            'WhatsApp',
            'Nominal',
            'Status Pembayaran',
            'Tanggal Pembayaran',
            'Diverifikasi Oleh',
            'Tanggal Verifikasi',
            'Catatan',
        ], function (): void {
            Payment::query()
                ->with(['alumni.user', 'verifier'])
                ->orderByDesc('updated_at')
                ->lazy()
                ->each(function (Payment $payment): void {
                    $this->writeCsvRow([
                        $payment->alumni?->full_name,
                        $payment->alumni?->student_number,
                        $payment->alumni?->user?->whatsapp_number,
                        $payment->amount,
                        $this->paymentStatusLabel($payment->status),
                        $payment->payment_date?->toDateString(),
                        $payment->verifier?->name,
                        $payment->verified_at?->toDateTimeString(),
                        $payment->notes,
                    ]);
                });
        });
    }

    public function donations(): StreamedResponse
    {
        return $this->csv('donation-export.csv', [
            'Nama',
            'NIM',
            'WhatsApp',
            'Nominal',
            'Status Publikasi',
            'Dikelola Oleh',
            'Tanggal Donasi',
            'Catatan',
        ], function (): void {
            Donation::query()
                ->with(['alumni.user', 'manager'])
                ->latest()
                ->lazy()
                ->each(function (Donation $donation): void {
                    $this->writeCsvRow([
                        $donation->alumni?->full_name,
                        $donation->alumni?->student_number,
                        $donation->alumni?->user?->whatsapp_number,
                        $donation->amount,
                        $this->donationPublicationLabel($donation->publication_status),
                        $donation->manager?->name,
                        $donation->created_at?->toDateTimeString(),
                        $donation->notes,
                    ]);
                });
        });
    }

    public function rooming(): StreamedResponse
    {
        return $this->csv('rooming-list-export.csv', [
            'Nama Kamar',
            'Tipe Kamar',
            'Kapasitas',
            'Jumlah Penghuni',
            'Penghuni',
            'Catatan Lokasi',
            'Catatan Kamar',
        ], function (): void {
            Room::query()
                ->with(['assignments.alumni'])
                ->withCount('assignments')
                ->orderBy('room_name')
                ->lazy()
                ->each(function (Room $room): void {
                    $occupants = $room->assignments
                        ->sortBy(fn ($assignment): string => $assignment->alumni?->full_name ?? '')
                        ->map(fn ($assignment): string => $assignment->alumni?->full_name ?? '-')
                        ->join('; ');

                    $this->writeCsvRow([
                        $room->room_name,
                        $room->room_type,
                        $room->capacity,
                        $room->assignments_count,
                        $occupants,
                        $room->location_notes,
                        $room->notes,
                    ]);
                });
        });
    }

    public function roomingPrint(): View
    {
        return view('reports.rooming-print', [
            'rooms' => Room::query()
                ->with(['assignments.alumni'])
                ->withCount('assignments')
                ->orderBy('room_name')
                ->get(),
        ]);
    }

    /**
     * @param  array<int, string>  $headers
     */
    private function csv(string $filename, array $headers, callable $writer): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $writer): void {
            $this->writeCsvRow($headers);
            $writer();
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @param  array<int, mixed>  $row
     */
    private function writeCsvRow(array $row): void
    {
        $handle = fopen('php://output', 'w');

        if ($handle === false) {
            return;
        }

        fputcsv($handle, $row);
        fclose($handle);
    }

    private function rsvpStatusLabel(?string $status): string
    {
        return match ($status) {
            'attending' => 'Hadir',
            'not_attending' => 'Tidak Hadir',
            default => 'Belum Merespon',
        };
    }

    private function partyTypeLabel(?string $partyType): string
    {
        return $partyType === 'family'
            ? 'Bersama keluarga'
            : 'Sendiri';
    }

    private function shirtTypeLabel(?string $shirtType): string
    {
        return match ($shirtType) {
            'child' => 'Anak',
            'male' => 'Pria',
            'female' => 'Wanita',
            default => '-',
        };
    }

    private function alumniStatusLabel(?string $status): string
    {
        return $status === 'deceased'
            ? 'Meninggal'
            : 'Aktif';
    }

    private function paymentStatusLabel(?string $status): string
    {
        return match ($status) {
            'paid' => 'Lunas',
            'pending_verification' => 'Menunggu Verifikasi',
            default => 'Belum Bayar',
        };
    }

    private function donationStatusLabel(bool $hasDonation): string
    {
        return $hasDonation ? 'Ada Donasi' : 'Belum Ada';
    }

    private function donationPublicationLabel(?string $status): string
    {
        return $status === 'anonymous'
            ? 'Anonim'
            : 'Publik';
    }
}
