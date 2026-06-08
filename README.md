# Kembali ke Titik Nol

Sistem Manajemen Reuni Alumni Teknik Geodesi UGM Angkatan 1996.

Tema kegiatan: **Kembali ke Titik Nol** - Reuni Geodesi 96 untuk Ngalibrasi 30 Taon Paseduluran.

## Stack

- PHP 8.3
- Laravel 13
- Laravel Fortify
- Livewire 4
- Flux UI 2
- Tailwind CSS 4
- Pest 4
- MySQL/MariaDB

## Modul Saat Ini

- Portal publik landing page.
- Galeri publik untuk dokumentasi dengan `visibility = public`.
- Login berbasis nomor WhatsApp.
- Role sederhana: `superadmin`, `administrator`, `bendahara`, `alumni`.
- Seed alumni dari `specification/contacts.json`.
- Profil alumni self-service.
- Direktori alumni privat.
- Peta/persebaran alumni dasar.
- Timeline lokasi alumni.
- RSVP alumni dan monitoring admin.
- Pembayaran, donasi, dashboard bendahara, dan export CSV.
- Rooming/penginapan, room assignment, export CSV, dan cetak rooming list.
- Dokumentasi foto/video internal, tagging alumni, dan admin monitoring.
- News/pengumuman.
- Audit log dasar.
- WhatsApp import/analytics dasar berbasis agregat, tanpa menampilkan raw chat.
- Reporting/export CSV untuk alumni, RSVP, pembayaran, donasi, dan rooming.
- Backup database dan readiness check go-live.

## Sumber Data dan Assets

- `specification/System Specification.md` - pedoman sistem.
- `specification/contacts.json` - data awal alumni untuk seeding.
- `specification/wag_alumni_tgd_96.txt` - export WhatsApp group untuk analytics.
- `specification/sticker-kembali-ke-titik-nol.jpg` dan `specification/stickers.jpg` - referensi visual/warna.
- `specification/stitch_landing_page_publik_kembali_ke_titik_nol/` - referensi UI landing page.

## Setup Lokal

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Atur koneksi database di `.env`, lalu jalankan:

```bash
php artisan migrate --seed
php artisan storage:link
npm run build
```

Jalankan server lokal:

```bash
php artisan serve
```

Untuk development frontend:

```bash
npm run dev
```

## Akun Superadmin Awal

Seeder membaca konfigurasi berikut:

```env
KTN_SUPERADMIN_NAME="Superadmin KTN"
KTN_SUPERADMIN_EMAIL=
KTN_SUPERADMIN_WHATSAPP=620000000001
KTN_SUPERADMIN_PASSWORD=tgd0001
```

Nilai default ada di [`config/kembali-ke-titik-nol.php`](config/kembali-ke-titik-nol.php).

## Seed Alumni

`DatabaseSeeder` menjalankan:

- `RoleSeeder`
- `LocationSeeder`
- `SuperadminSeeder`
- `AlumniContactSeeder`

`AlumniContactSeeder` menggunakan `specification/contacts.json`. Password default alumni dari seed mengikuti pola:

```text
tgd + 4 digit terakhir nomor WhatsApp
```

## WhatsApp Analytics

Import WhatsApp menggunakan file export `.txt` tanpa media. Sistem hanya menyimpan dan menampilkan statistik agregat:

- active member
- silent reader
- link poster
- image poster
- nocturnal chatter
- work time chatter
- weekend warrior
- emoji champion
- top topic
- busiest year/month/hour
- word cloud

Sistem tidak menampilkan raw chat, kutipan pesan individu, atau riwayat chat per pengguna.

File acuan saat ini:

```text
specification/wag_alumni_tgd_96.txt
```

## Testing dan Quality Check

Jalankan test:

```bash
php artisan test --compact
```

Format PHP:

```bash
vendor/bin/pint --dirty --format agent
```

Build frontend:

```bash
npm run build
```

Readiness check go-live:

```bash
php artisan readiness:check
```

Backup database:

```bash
php artisan backup:database
```

Status terakhir yang pernah diverifikasi:

```text
193 tests, full suite passing
```

Panduan penggunaan ringkas tersedia di [`USER_GUIDE.md`](USER_GUIDE.md).

## Catatan Implementasi

- Dokumentasi foto/video diimplementasikan sebagai `media_items` dan `media_item_tags`, bukan tabel terpisah `photos/videos`. Secara fungsi tetap mencakup tipe media, visibility, metadata, uploader, dan tagging.
- RSVP saat ini disimpan sebagai `alumni.rsvp_status`, sesuai catatan spesifikasi yang memperbolehkan denormalized summary.
- Upload foto sudah menyimpan metadata ukuran/dimensi, tetapi resize/compression aktual masih dapat ditingkatkan.
- News sudah memiliki admin management dan halaman publik.
- Donatur publik di landing page sudah berasal dari data `donations`, tanpa menampilkan nominal.

## Roadmap Terdekat

Rekomendasi urutan berikutnya:

1. UAT bersama panitia menggunakan [`USER_GUIDE.md`](USER_GUIDE.md).
2. Review data alumni awal dan data RSVP/payment/donation aktual.
3. Finalisasi konfigurasi production: domain, SSL, database, storage, scheduler, dan backup retention.
4. Tingkatkan resize/compression foto bila diperlukan untuk efisiensi storage.
5. Siapkan deployment production.
