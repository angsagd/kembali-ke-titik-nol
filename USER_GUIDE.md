# Panduan Penggunaan Singkat

Panduan ini dipakai untuk UAT dan operasional awal Sistem Manajemen Reuni Alumni Teknik Geodesi UGM Angkatan 1996.

## Alumni

1. Buka halaman login.
2. Masukkan nomor WhatsApp dan password.
3. Buka menu Profil Saya untuk melengkapi data diri, domisili, pekerjaan, cerita, kenangan, dan pesan untuk teman.
4. Buka menu RSVP untuk memilih Hadir atau Tidak Hadir.
5. Buka menu Status Pembayaran untuk melihat status pembayaran kontribusi reuni. Nominal donasi tidak ditampilkan untuk alumni biasa.
6. Buka menu Kamar Saya untuk melihat informasi penginapan jika sudah ditempatkan oleh panitia.
7. Buka menu Dokumentasi untuk mengunggah foto atau menambahkan tautan video.
8. Buka Direktori Alumni, Buku Kenangan, Peta Alumni, Timeline, dan WhatsApp Analytics untuk menjelajahi arsip internal alumni.

## Administrator

1. Login dengan akun role administrator.
2. Buka Dashboard untuk melihat ringkasan operasional, RSVP, dokumentasi, berita, rooming, dan export laporan.
3. Buka Manajemen Alumni untuk melihat dan memperbarui data alumni.
4. Buka Monitoring RSVP untuk memantau status kehadiran.
5. Buka Rooming untuk membuat kamar dan menempatkan alumni.
6. Buka Dokumentasi Admin untuk memantau dokumentasi alumni dan mengatur visibility.
7. Buka News untuk membuat draft dan mempublikasikan pengumuman.
8. Buka WhatsApp Import untuk mengunggah dan memproses file export WhatsApp `.txt`.

## Bendahara

1. Login dengan akun role bendahara.
2. Buka Dashboard Bendahara untuk melihat ringkasan pembayaran, donasi, dan total dana.
3. Buka Pembayaran & Donasi untuk memilih alumni, memperbarui status pembayaran, dan mencatat donasi.
4. Gunakan export pembayaran dan donasi untuk kebutuhan laporan internal.

## Superadmin

1. Login dengan akun role superadmin.
2. Superadmin memiliki akses administrator, bendahara, audit log, dan dashboard sistem.
3. Gunakan Audit Log untuk memeriksa aktivitas penting.
4. Gunakan command berikut untuk mempromosikan user berdasarkan nomor WhatsApp:

```bash
php artisan user:promote-role 628xxxxxxxxxx administrator
```

Role target yang tersedia: `superadmin`, `administrator`, `bendahara`, `alumni`.

## Backup dan Readiness

Jalankan backup database manual:

```bash
php artisan backup:database
```

Backup terjadwal harian pukul 02:00 melalui Laravel Scheduler. Pastikan cron production menjalankan:

```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

Jalankan readiness check otomatis sebelum go-live:

```bash
php artisan readiness:check
```

Item manual yang tetap perlu dicek panitia sebelum go-live:

- Domain aktif.
- SSL aktif.
- Database production siap.
- Storage production siap.
- Data alumni awal sudah sesuai.
- UAT selesai dan disetujui panitia.
