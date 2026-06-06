# Spesifikasi Sistem Manajemen Reuni Alumni Teknik Geodesi UGM Angkatan 1996

## BAB 1 - SCOPE & REQUIREMENT DEFINITION

### 1.1 Nama Sistem

**Sistem Manajemen Reuni Alumni Teknik Geodesi UGM Angkatan 1996**

Tema kegiatan:

**Kembali ke Titik Nol**
*Reuni Geodesi 96 untuk Ngalibrasi 30 Taon Paseduluran*

### 1.2 Latar Belakang

Tahun 2026 menandai 30 tahun perjalanan Alumni Teknik Geodesi Universitas Gadjah Mada Angkatan 1996 sejak pertama kali memasuki dunia perkuliahan. Untuk memperingati momen tersebut, diselenggarakan kegiatan reuni bertema **Kembali ke Titik Nol**, yaitu sebuah perjalanan simbolik untuk kembali pada titik awal persahabatan, mengenang perjalanan hidup masing-masing alumni, memperkuat kembali hubungan antar alumni, serta memberikan kontribusi kepada almamater dan generasi penerus.

Saat ini informasi alumni, dokumentasi, pembayaran, komunikasi, dan data kegiatan tersebar dalam berbagai media seperti grup WhatsApp, spreadsheet, media sosial, serta arsip pribadi masing-masing alumni. Kondisi tersebut menyebabkan data sulit dikelola secara terpusat dan berpotensi hilang seiring berjalannya waktu.

Untuk mendukung pelaksanaan reuni sekaligus menjaga keberlanjutan hubungan antar alumni setelah acara selesai, diperlukan sebuah sistem informasi berbasis web yang berfungsi sebagai pusat data alumni, media komunikasi informasi resmi, sarana dokumentasi digital, arsip sejarah angkatan, serta wadah untuk menyimpan perjalanan hidup Alumni Teknik Geodesi UGM Angkatan 1996.

Sistem ini dirancang tidak hanya untuk mendukung pelaksanaan acara reuni tanggal 23–24 Agustus 2026, tetapi juga sebagai arsip digital permanen yang dapat terus digunakan oleh alumni setelah kegiatan reuni berakhir.

### 1.3 Tujuan Sistem

Sistem dibangun untuk mencapai tujuan berikut:

1. Menyediakan basis data alumni yang terpusat dan mudah dikelola.
2. Mendukung proses registrasi dan konfirmasi kehadiran peserta reuni.
3. Mendukung proses pencatatan pembayaran kontribusi reuni.
4. Mendukung proses pencatatan dan publikasi donasi alumni.
5. Membantu pengelolaan penginapan dan pembagian kamar peserta.
6. Menjadi media dokumentasi foto dan video kegiatan reuni.
7. Menjadi buku kenangan digital Alumni Teknik Geodesi UGM Angkatan 1996.
8. Menyajikan peta persebaran alumni berdasarkan kota dan negara domisili.
9. Menampilkan timeline perjalanan hidup alumni berdasarkan riwayat lokasi tempat tinggal.
10. Menyajikan analisis aktivitas grup WhatsApp alumni sebagai media nostalgia dan hiburan.
11. Menjadi arsip digital jangka panjang yang dapat terus dimanfaatkan oleh alumni.

### 1.4 Ruang Lingkup Sistem

Sistem yang dibangun mencakup:

#### A. Portal Informasi Publik

Portal publik yang dapat diakses oleh siapa saja tanpa login.

#### B. Portal Alumni

Portal privat yang hanya dapat diakses oleh alumni yang memiliki akun.

#### C. Portal Administrasi

Portal khusus yang digunakan oleh panitia dan administrator untuk mengelola seluruh data reuni.

#### D. Arsip Digital Alumni

Portal yang tetap aktif setelah acara selesai sebagai pusat dokumentasi dan informasi Alumni Teknik Geodesi UGM Angkatan 1996.

### 1.5 Batasan Sistem

Batasan sistem adalah sebagai berikut:

1. Sistem hanya digunakan untuk Alumni Teknik Geodesi UGM Angkatan 1996.
2. Sistem hanya mendukung satu kegiatan reuni utama yaitu Reuni 30 Tahun Tahun 2026.
3. Sistem tidak dirancang sebagai sistem multi-event.
4. Sistem tidak mendukung pendaftaran alumni secara mandiri.
5. Data awal alumni diinputkan oleh administrator.
6. Login alumni menggunakan nomor WhatsApp dan password.
7. Verifikasi pembayaran dilakukan secara manual oleh bendahara.
8. Sistem tidak menggunakan payment gateway.
9. Sistem tidak menampilkan isi percakapan WhatsApp secara langsung.
10. Sistem hanya menampilkan hasil analisis statistik dari data percakapan grup.
11. Sistem tidak digunakan untuk pasangan, keluarga, mahasiswa, atau peserta di luar Alumni Geodesi 96.
12. Video tidak disimpan di server, melainkan menggunakan tautan eksternal seperti YouTube atau Google Drive.
13. Sistem tidak menyimpan foto resolusi asli (HD) setelah proses upload.

### 1.6 Informasi Kegiatan Reuni

Nama kegiatan:

**Kembali ke Titik Nol**
*Reuni Geodesi 96 untuk Ngalibrasi 30 Taon Paseduluran*

Tanggal kegiatan:

**23–24 Agustus 2026**

Lokasi kegiatan:

#### Hari Pertama

Penginapan Joglo / Kampung Wisata Tembi

#### Hari Kedua

Departemen Teknik Geodesi Universitas Gadjah Mada

#### Gala Dinner

Restoran dengan pemandangan matahari terbenam (sunset view) yang akan ditetapkan kemudian.

### 1.7 Jenis Pengguna Sistem

Sistem memiliki empat jenis pengguna utama:

1. Superadmin
2. Administrator
3. Bendahara
4. Alumni

Rincian hak akses masing-masing pengguna akan dijelaskan pada bab berikutnya.

### 1.8 Portal Informasi Publik

Portal publik dapat diakses oleh siapa saja tanpa login.

Fitur yang tersedia:

#### Informasi Acara

* Tema reuni
* Filosofi Kembali ke Titik Nol
* Informasi kegiatan
* Informasi lokasi

#### Rundown Acara

* Rundown hari pertama
* Rundown hari kedua
* Rundown gala dinner

#### Berita dan Pengumuman

* Berita persiapan reuni
* Berita pelaksanaan reuni
* Berita pasca reuni

#### Galeri Publik

* Foto yang ditandai sebagai publik
* Video yang ditandai sebagai publik

#### Donatur

* Daftar nama donatur
* Ucapan terima kasih kepada donatur

#### Kontak Panitia

* Informasi kontak resmi panitia

### 1.9 Portal Alumni

Portal alumni hanya dapat diakses setelah login.

Fitur yang tersedia:

#### Dashboard

* Status RSVP
* Status pembayaran
* Informasi kamar
* Informasi terbaru

#### Profil Alumni

* Data pribadi
* Foto masa kuliah
* Foto saat ini
* Cerita singkat
* Kenangan lucu atau tak terlupakan
* Pesan untuk rekan alumni

#### Direktori Alumni

* Daftar alumni
* Pencarian alumni
* Profil alumni

#### RSVP

Status RSVP terdiri dari:

* Belum Merespon
* Hadir
* Tidak Hadir

#### Dokumentasi

* Upload foto
* Upload video melalui tautan eksternal
* Galeri internal
* Galeri publik

#### Buku Kenangan Digital

* Profil alumni
* Cerita alumni
* Kenangan alumni
* Pesan alumni

#### Peta Alumni

* Persebaran alumni berdasarkan kota dan negara
* Statistik persebaran alumni

#### Timeline Alumni

* Riwayat perpindahan kota
* Riwayat perpindahan negara
* Visualisasi perjalanan hidup alumni

#### WhatsApp Analytics

* Top 5 anggota paling aktif
* Top 5 silent reader
* Top 5 link poster
* Top 5 image poster
* Top 5 nocturnal chatter
* Top 5 work time chatter
* Top 5 weekend warrior
* Top 5 emoji champion
* Top 10 topik paling sering muncul
* Tahun paling ramai
* Bulan paling ramai
* Jam paling ramai
* Nostalgia word cloud

### 1.10 Data Alumni

Data alumni terdiri atas:

#### Data Identitas

* Nama lengkap
* Nomor Induk Mahasiswa (NIM) masa kuliah
* Nama panggilan waktu kuliah
* Nomor WhatsApp
* Email

#### Data Domisili

* Kota saat ini
* Negara saat ini

#### Data Pekerjaan

* Instansi atau perusahaan
* Profesi atau jabatan

#### Data Kehadiran

* Status RSVP

#### Data Khusus

* Catatan khusus
* Kebutuhan khusus
* Informasi kesehatan yang relevan

#### Data Buku Kenangan

* Cerita singkat
* Kenangan lucu atau tak terlupakan
* Pesan untuk rekan alumni

#### Foto Profil

* Foto masa kuliah (1 foto utama)
* Foto saat ini (1 foto utama)

#### Data Timeline Lokasi

Setiap alumni dapat memiliki banyak riwayat lokasi yang terdiri atas:

* Bulan
* Tahun
* Kota
* Negara
* Koordinat lintang
* Koordinat bujur

### 1.11 Status Alumni

Setiap alumni memiliki status:

* Aktif
* Meninggal

#### Alumni Aktif

Dapat login dan menggunakan sistem.

#### Alumni Meninggal

Tetap ditampilkan dalam:

* Direktori alumni
* Buku kenangan digital
* Peta alumni
* Timeline alumni
* Dokumentasi alumni

Profil alumni yang telah meninggal akan ditampilkan sebagai halaman memorial.

Foto-foto yang ditandai dengan alumni tersebut tetap dapat ditampilkan pada halaman profil memorial.

### 1.12 Dokumentasi Foto dan Video

#### Foto

Foto diunggah langsung ke server.

Setiap foto memiliki:

* Pemilik/Uploader
* Tanggal upload
* Bulan (opsional)
* Tahun (wajib)
* Status publik atau internal
* Alumni yang ditandai

Foto akan:

* Dikompresi otomatis
* Di-resize otomatis
* Tidak menyimpan file resolusi asli

#### Video

Video disimpan dalam bentuk tautan eksternal.

Platform yang didukung:

* YouTube
* Google Drive

Setiap video memiliki:

* Judul
* Deskripsi
* Pemilik/Uploader
* Tahun
* Status publik atau internal
* Alumni yang ditandai

### 1.13 Galeri Alumni

Setiap alumni memiliki dua jenis galeri:

#### Uploaded

Berisi foto dan video yang diunggah oleh alumni tersebut.

#### Tagged

Berisi foto dan video yang diunggah oleh alumni lain namun menandai alumni tersebut.

### 1.14 Donasi

Setiap alumni dapat memiliki satu data donasi.

Donasi dapat:

* Ditambahkan
* Diubah
* Dikelola oleh bendahara

Informasi yang ditampilkan kepada alumni:

* Nama donatur

Nominal donasi tidak ditampilkan.

Tidak terdapat leaderboard atau peringkat donatur.

### 1.15 Pembayaran Reuni

Pembayaran kontribusi reuni:

* Dilakukan di luar sistem
* Dicatat dalam sistem
* Diverifikasi secara manual oleh bendahara

Setiap alumni hanya memiliki satu pembayaran utama untuk kegiatan reuni.

### 1.16 Penginapan dan Rooming

Seluruh peserta yang hadir diasumsikan menginap.

Data kamar dan pembagian kamar diinput oleh administrator atau tim tamu.

Penentuan teman sekamar dilakukan di luar sistem berdasarkan pertimbangan panitia.

Sistem hanya berfungsi sebagai media pencatatan dan publikasi rooming list.

### 1.17 Peta Alumni

Sistem menyediakan visualisasi persebaran alumni berdasarkan:

* Kota
* Negara

Koordinat lokasi diperoleh melalui:

1. Geocoding otomatis berdasarkan kota dan negara.
2. Input manual oleh administrator apabila diperlukan.

Koordinat dapat diperbaiki secara manual untuk meningkatkan akurasi.

### 1.18 WhatsApp Analytics

Sistem menyediakan fitur analisis grup WhatsApp alumni berdasarkan file ekspor percakapan sejak tahun 2016.

Analisis hanya menampilkan hasil statistik dan tidak menampilkan isi percakapan asli.

Anggota dengan status meninggal tidak diikutsertakan dalam peringkat statistik individu.

Namun kontribusi historis mereka tetap dihitung dalam statistik grup secara keseluruhan.

### 1.19 Fungsi Sistem Pasca Reuni

Setelah acara selesai, sistem tetap digunakan sebagai:

1. Direktori alumni.
2. Buku kenangan digital.
3. Arsip dokumentasi reuni.
4. Peta persebaran alumni.
5. Timeline perjalanan alumni.
6. Arsip sejarah angkatan.
7. Portal informasi Alumni Teknik Geodesi UGM Angkatan 1996.

### 1.20 Future Enhancement

Fitur berikut tidak termasuk dalam fase awal pengembangan:

1. Payment gateway.
2. OTP login.
3. Mobile application Android.
4. Mobile application iOS.
5. Single Sign-On.
6. Chat internal alumni.
7. Video conference.
8. Multi-event reunion management.
9. AI recommendation system.
10. Analisis WhatsApp berbasis AI lanjutan.
11. Integrasi LinkedIn.
12. Integrasi timeline lokasi otomatis dari layanan pihak ketiga.

## BAB 2 - USER ROLE, HAK AKSES, DAN MODUL SISTEM

### 2.1 Pendahuluan

Sistem Manajemen Reuni Alumni Teknik Geodesi UGM Angkatan 1996 menggunakan mekanisme Role-Based Access Control (RBAC) untuk mengatur akses pengguna terhadap fitur dan data sistem.

Setiap pengguna diberikan hak akses sesuai dengan tugas dan tanggung jawabnya dalam kegiatan reuni. Mekanisme ini bertujuan untuk menjaga keamanan data, mencegah perubahan data yang tidak berwenang, serta memastikan setiap proses bisnis berjalan sesuai dengan fungsi masing-masing pengguna.

### 2.2 Struktur Role Sistem

Sistem memiliki empat role utama:

#### 2.2.1 Superadmin

Superadmin merupakan pengelola teknis sistem yang memiliki akses penuh terhadap seluruh modul dan konfigurasi sistem.

Tanggung jawab utama:

* Mengelola akun pengguna.
* Mengelola role dan permission.
* Mengelola konfigurasi sistem.
* Mengelola data master.
* Melakukan audit aktivitas pengguna.
* Memberikan bantuan teknis kepada panitia.

Jumlah pengguna:

* 1–2 orang.

#### 2.2.2 Administrator

Administrator merupakan panitia pelaksana reuni.

Role ini mencakup:

* Ketua
* Wakil Ketua
* Tim Acara
* Tim Tamu

Administrator bertanggung jawab terhadap operasional kegiatan reuni dan pengelolaan data peserta.

#### 2.2.3 Bendahara

Bendahara bertanggung jawab terhadap seluruh proses pembayaran dan donasi.

Bendahara memiliki akses khusus terhadap modul keuangan yang tidak dapat diakses oleh alumni maupun administrator umum.

#### 2.2.4 Alumni

Alumni merupakan peserta reuni yang memiliki akun pribadi.

Setiap alumni dapat:

* Melengkapi profil.
* Melakukan RSVP.
* Mengakses direktori alumni.
* Mengunggah dokumentasi.
* Mengakses buku kenangan digital.
* Mengakses peta alumni.
* Mengakses WhatsApp Analytics.

### 2.3 Matriks Hak Akses

| Fitur                       | Superadmin | Administrator | Bendahara | Alumni |
| --------------------------- | ---------- | ------------- | --------- | ------ |
| Login                       | ✔          | ✔             | ✔         | ✔      |
| Ubah Profil Sendiri         | ✔          | ✔             | ✔         | ✔      |
| Ubah Password Sendiri       | ✔          | ✔             | ✔         | ✔      |
| Ubah Nomor WhatsApp Sendiri | ✔          | ✔             | ✔         | ✔      |
| Lihat Direktori Alumni      | ✔          | ✔             | ✔         | ✔      |
| Lihat Profil Alumni Lain    | ✔          | ✔             | ✔         | ✔      |
| Kelola Data Alumni          | ✔          | ✔             | ✖         | ✖      |
| Kelola Status Alumni        | ✔          | ✔             | ✖         | ✖      |
| Import Data Alumni          | ✔          | ✔             | ✖         | ✖      |
| Kelola RSVP                 | ✔          | ✔             | ✖         | ✖      |
| Kelola Rooming              | ✔          | ✔             | ✖         | ✖      |
| Kelola Berita               | ✔          | ✔             | ✖         | ✖      |
| Upload Dokumentasi          | ✔          | ✔             | ✔         | ✔      |
| Edit Dokumentasi Sendiri    | ✔          | ✔             | ✔         | ✔      |
| Hapus Dokumentasi Sendiri   | ✔          | ✔             | ✔         | ✔      |
| Kelola Seluruh Dokumentasi  | ✔          | ✔             | ✖         | ✖      |
| Verifikasi Pembayaran       | ✔          | ✖             | ✔         | ✖      |
| Kelola Donasi               | ✔          | ✖             | ✔         | ✖      |
| Lihat Statistik Keuangan    | ✔          | ✔             | ✔         | ✖      |
| Import WhatsApp Analytics   | ✔          | ✔             | ✖         | ✖      |
| Kelola User                 | ✔          | ✖             | ✖         | ✖      |
| Kelola Role dan Permission  | ✔          | ✖             | ✖         | ✖      |
| Kelola Konfigurasi Sistem   | ✔          | ✖             | ✖         | ✖      |
| Audit Log                   | ✔          | ✖             | ✖         | ✖      |

### 2.4 Struktur Modul Sistem

Secara umum sistem terdiri atas tiga area utama:

#### Public Website

Area yang dapat diakses oleh publik tanpa login.

#### Alumni Portal

Area yang hanya dapat diakses oleh alumni yang telah login.

#### Administration Portal

Area yang hanya dapat diakses oleh panitia dan pengelola sistem.

### 2.5 Modul Public Website

#### Informasi Acara

Menampilkan:

* Tema reuni
* Filosofi kegiatan
* Informasi kegiatan
* Lokasi kegiatan
* Informasi panitia

#### Rundown Acara

Menampilkan:

* Rundown hari pertama
* Rundown hari kedua
* Gala dinner

#### Berita dan Pengumuman

Menampilkan:

* Berita persiapan reuni
* Pengumuman panitia
* Berita pelaksanaan kegiatan
* Berita pasca kegiatan

#### Galeri Publik

Menampilkan:

* Foto publik
* Video publik

#### Donatur

Menampilkan:

* Daftar nama donatur
* Donatur anonim
* Ucapan terima kasih

Nominal donasi tidak ditampilkan.

#### Kontak Panitia

Menampilkan:

* Kontak resmi panitia reuni

### 2.6 Modul Alumni Portal

#### Dashboard Alumni

Menampilkan informasi ringkas:

* Status RSVP
* Status pembayaran
* Informasi kamar
* Informasi terbaru
* Statistik singkat reuni

#### Profil Alumni

Digunakan untuk mengelola data pribadi alumni.

Fitur:

* Ubah profil
* Ubah password
* Ubah nomor WhatsApp
* Kelola cerita alumni
* Kelola kenangan alumni
* Kelola pesan alumni

#### Direktori Alumni

Digunakan untuk:

* Melihat seluruh alumni
* Mencari alumni
* Membuka profil alumni

#### RSVP

Digunakan untuk konfirmasi kehadiran.

Status RSVP:

* Belum Merespon
* Hadir
* Tidak Hadir

#### Dokumentasi

Digunakan untuk:

* Upload foto
* Upload video
* Edit dokumentasi sendiri
* Soft delete dokumentasi sendiri
* Menambahkan tag alumni

#### Buku Kenangan Digital

Menampilkan:

* Profil alumni
* Cerita alumni
* Kenangan alumni
* Pesan alumni

#### Peta Alumni

Menampilkan:

* Persebaran alumni berdasarkan kota
* Persebaran alumni berdasarkan negara
* Statistik persebaran alumni

#### Timeline Alumni

Menampilkan:

* Riwayat lokasi alumni
* Perjalanan hidup alumni berdasarkan waktu

#### WhatsApp Analytics

Menampilkan:

* Statistik grup
* Statistik anggota
* Statistik topik
* Word cloud nostalgia

### 2.7 Modul Administrasi

Digunakan oleh Administrator dan Superadmin.

#### Manajemen Alumni

Fitur:

* Tambah alumni
* Ubah data alumni
* Import data alumni
* Reset password alumni
* Aktivasi akun
* Nonaktifkan akun
* Kelola status hidup alumni

#### Manajemen RSVP

Fitur:

* Monitoring RSVP
* Rekap peserta
* Statistik kehadiran
* Export data RSVP

#### Manajemen Penginapan

Fitur:

* Kelola kamar
* Kelola penghuni kamar
* Cetak rooming list

#### Manajemen Berita

Fitur:

* Tambah berita
* Ubah berita
* Hapus berita
* Publikasi berita

#### Manajemen Dokumentasi

Fitur:

* Kelola foto
* Kelola video
* Kelola tag alumni
* Ubah status publik/internal

#### Manajemen WhatsApp Analytics

Fitur:

* Upload file chat
* Generate statistik
* Regenerate statistik

### 2.8 Modul Bendahara

Digunakan oleh Bendahara dan Superadmin.

#### Manajemen Pembayaran

Digunakan untuk mengelola pembayaran kontribusi reuni.

Status pembayaran:

* Belum Bayar
* Menunggu Verifikasi
* Lunas

Fitur:

* Input pembayaran
* Verifikasi pembayaran
* Ubah status pembayaran
* Catatan pembayaran

#### Manajemen Donasi

Digunakan untuk mengelola donasi alumni.

Fitur:

* Tambah donasi
* Ubah donasi
* Hapus donasi
* Atur status publik donor

Pilihan publikasi donor:

* Tampilkan nama saya
* Donatur anonim

Nominal donasi tidak ditampilkan kepada alumni.

#### Dashboard Keuangan

Menampilkan:

* Statistik pembayaran
* Statistik donasi
* Pembayaran belum diverifikasi
* Ringkasan keuangan

### 2.9 Modul Memorial Alumni

Modul khusus untuk alumni dengan status meninggal.

Profil memorial tetap dapat diakses oleh seluruh alumni.

Informasi yang ditampilkan:

* Nama alumni
* Foto masa kuliah
* Foto saat ini yang tersedia
* Cerita alumni
* Kenangan alumni
* Pesan alumni
* Dokumentasi terkait
* Timeline perjalanan hidup

### 2.10 Dashboard Berdasarkan Role

#### Dashboard Superadmin

Menampilkan:

* Total alumni
* Statistik RSVP
* Statistik pembayaran
* Statistik donasi
* Statistik dokumentasi
* Statistik WhatsApp Analytics

#### Dashboard Administrator

Menampilkan:

* Statistik alumni
* Statistik RSVP
* Statistik kamar
* Statistik dokumentasi
* Pengumuman terbaru

#### Dashboard Bendahara

Menampilkan:

* Statistik pembayaran
* Statistik donasi
* Pembayaran menunggu verifikasi
* Ringkasan keuangan

#### Dashboard Alumni

Menampilkan:

* Status RSVP pribadi
* Status pembayaran pribadi
* Informasi kamar
* Dokumentasi terbaru
* Berita terbaru
* Statistik singkat reuni

### 2.11 Kepemilikan Dokumentasi

Setiap foto dan video memiliki informasi kepemilikan (ownership).

Informasi yang disimpan:

* Uploader
* Tanggal upload
* Tanggal perubahan terakhir

Uploader dapat:

* Mengubah dokumentasinya sendiri
* Melakukan soft delete dokumentasinya sendiri

Administrator dan Superadmin dapat:

* Mengelola seluruh dokumentasi
* Melakukan restore dokumentasi yang dihapus
* Melakukan penghapusan permanen apabila diperlukan

### 2.12 Tagging Alumni

Foto dan video mendukung mekanisme tagging alumni.

Karakteristik:

* Satu foto dapat memiliki banyak alumni.
* Satu video dapat memiliki banyak alumni.
* Satu alumni dapat ditandai pada banyak foto.
* Satu alumni dapat ditandai pada banyak video.

Tagging digunakan untuk membangun:

* Galeri Uploaded
* Galeri Tagged
* Dokumentasi pada Profil Alumni
* Dokumentasi pada Memorial Alumni

## BAB 3 - FUNCTIONAL MODULES & USE CASE SPECIFICATION

### 3.1 Pendahuluan

Bab ini menjelaskan fungsi-fungsi yang harus tersedia pada Sistem Manajemen Reuni Alumni Teknik Geodesi UGM Angkatan 1996.

Setiap fungsi dijabarkan dalam bentuk use case yang menggambarkan interaksi antara pengguna dan sistem.

Tujuan utama bab ini adalah memastikan seluruh kebutuhan fungsional telah teridentifikasi sebelum dilakukan perancangan basis data, antarmuka, dan implementasi sistem.

### 3.2 Aktor Sistem

Sistem memiliki empat aktor utama:

#### Superadmin

Pengelola teknis sistem.

#### Administrator

Panitia pelaksana reuni.

#### Bendahara

Pengelola pembayaran dan donasi.

#### Alumni

Peserta reuni.

### 3.3 Modul Autentikasi

#### UC-AUTH-01 Login

##### Aktor

* Superadmin
* Administrator
* Bendahara
* Alumni

##### Deskripsi

Pengguna masuk ke sistem menggunakan nomor WhatsApp dan password.

##### Prasyarat

* Akun telah dibuat.

##### Alur Utama

1. Pengguna membuka halaman login.
2. Pengguna memasukkan nomor WhatsApp.
3. Pengguna memasukkan password.
4. Sistem melakukan validasi.
5. Sistem mengarahkan pengguna ke dashboard sesuai role.

##### Hasil

Pengguna berhasil masuk ke sistem.

#### UC-AUTH-02 Logout

##### Aktor

Semua pengguna.

##### Deskripsi

Keluar dari sistem.

#### UC-AUTH-03 Ubah Password

##### Aktor

Semua pengguna.

##### Deskripsi

Mengubah password akun.

#### UC-AUTH-04 Ubah Nomor WhatsApp

##### Aktor

Semua pengguna.

##### Deskripsi

Mengubah nomor WhatsApp yang digunakan untuk login.

##### Catatan

Perubahan dilakukan setelah pengguna berhasil login menggunakan nomor lama.

### 3.4 Modul Profil Alumni

#### UC-PROF-01 Melihat Profil Sendiri

##### Aktor

Alumni

##### Deskripsi

Melihat seluruh data profil pribadi.

#### UC-PROF-02 Mengubah Profil Sendiri

##### Aktor

Alumni

##### Deskripsi

Memperbarui data profil pribadi.

##### Data yang dapat diubah

* Nama panggilan
* Email
* Kota
* Negara
* Instansi
* Jabatan
* Cerita alumni
* Kenangan alumni
* Pesan alumni
* Catatan khusus

#### UC-PROF-03 Mengubah Foto Masa Kuliah

##### Aktor

Alumni

##### Deskripsi

Mengunggah atau mengganti foto masa kuliah.

#### UC-PROF-04 Mengubah Foto Saat Ini

##### Aktor

Alumni

##### Deskripsi

Mengunggah atau mengganti foto saat ini.

#### UC-PROF-05 Melihat Profil Alumni Lain

##### Aktor

Alumni

##### Deskripsi

Melihat profil alumni lain yang terdaftar.

#### UC-PROF-06 Melihat Profil Memorial

##### Aktor

Semua alumni.

##### Deskripsi

Melihat profil alumni yang telah meninggal.

### 3.5 Modul Direktori Alumni

#### UC-DIR-01 Menampilkan Direktori Alumni

##### Aktor

Alumni

##### Deskripsi

Menampilkan seluruh alumni yang terdaftar.

#### UC-DIR-02 Mencari Alumni

##### Aktor

Alumni

##### Deskripsi

Melakukan pencarian alumni berdasarkan:

* Nama
* Nama panggilan
* Kota
* Negara
* Perusahaan

### 3.6 Modul RSVP

#### UC-RSVP-01 Mengisi RSVP

##### Aktor

Alumni

##### Deskripsi

Mengisi status kehadiran reuni.

##### Pilihan Status

* Hadir
* Tidak Hadir

#### UC-RSVP-02 Mengubah RSVP

##### Aktor

Alumni

##### Deskripsi

Mengubah status RSVP sebelum batas waktu yang ditentukan.

#### UC-RSVP-03 Monitoring RSVP

##### Aktor

Administrator

##### Deskripsi

Melihat rekap kehadiran alumni.

#### UC-RSVP-04 Export RSVP

##### Aktor

Administrator

##### Deskripsi

Menghasilkan laporan RSVP dalam format Excel atau PDF.

### 3.7 Modul Pembayaran

#### UC-PAY-01 Mencatat Pembayaran

##### Aktor

Bendahara

##### Deskripsi

Mencatat pembayaran alumni.

#### UC-PAY-02 Verifikasi Pembayaran

##### Aktor

Bendahara

##### Deskripsi

Memverifikasi pembayaran yang telah diterima.

##### Status

* Belum Bayar
* Menunggu Verifikasi
* Lunas

#### UC-PAY-03 Melihat Status Pembayaran

##### Aktor

Alumni

##### Deskripsi

Melihat status pembayaran pribadi.

#### UC-PAY-04 Melihat Rekap Pembayaran

##### Aktor

Bendahara
Administrator

##### Deskripsi

Melihat statistik pembayaran peserta.

### 3.8 Modul Donasi

#### UC-DON-01 Menambah Donasi

##### Aktor

Bendahara

##### Deskripsi

Menambahkan data donasi alumni.

#### UC-DON-02 Mengubah Donasi

##### Aktor

Bendahara

##### Deskripsi

Mengubah data donasi.

#### UC-DON-03 Menentukan Status Publikasi Donatur

##### Aktor

Bendahara

##### Pilihan

* Tampilkan Nama Saya
* Donatur Anonim

#### UC-DON-04 Melihat Daftar Donatur

##### Aktor

Publik
Alumni

##### Deskripsi

Melihat daftar donatur yang dipublikasikan.

### 3.9 Modul Rooming

#### UC-ROOM-01 Membuat Data Kamar

##### Aktor

Administrator

##### Deskripsi

Membuat daftar kamar penginapan.

#### UC-ROOM-02 Menentukan Penghuni Kamar

##### Aktor

Administrator

##### Deskripsi

Menentukan penghuni masing-masing kamar.

#### UC-ROOM-03 Melihat Informasi Kamar

##### Aktor

Alumni

##### Deskripsi

Melihat kamar yang ditempati selama reuni.

#### UC-ROOM-04 Cetak Rooming List

##### Aktor

Administrator

##### Deskripsi

Mencetak daftar kamar dan penghuni.

### 3.10 Modul Dokumentasi

#### UC-DOC-01 Upload Foto

##### Aktor

Alumni

##### Deskripsi

Mengunggah foto ke sistem.

#### UC-DOC-02 Upload Video

##### Aktor

Alumni

##### Deskripsi

Menambahkan video melalui tautan eksternal.

##### Platform

* YouTube
* Google Drive

#### UC-DOC-03 Menandai Alumni

##### Aktor

Alumni

##### Deskripsi

Menambahkan tag alumni pada foto atau video.

#### UC-DOC-04 Edit Dokumentasi

##### Aktor

Uploader

##### Deskripsi

Mengubah data dokumentasi miliknya sendiri.

#### UC-DOC-05 Hapus Dokumentasi

##### Aktor

Uploader

##### Deskripsi

Melakukan soft delete dokumentasi miliknya sendiri.

#### UC-DOC-06 Restore Dokumentasi

##### Aktor

Administrator
Superadmin

##### Deskripsi

Mengembalikan dokumentasi yang telah dihapus.

#### UC-DOC-07 Mengubah Status Publik/Internal

##### Aktor

Administrator

##### Deskripsi

Mengubah visibilitas dokumentasi.

### 3.11 Modul Buku Kenangan Digital

#### UC-MEM-01 Melihat Buku Kenangan

##### Aktor

Alumni

##### Deskripsi

Menjelajahi seluruh profil alumni.

#### UC-MEM-02 Membaca Cerita Alumni

##### Aktor

Alumni

##### Deskripsi

Membaca cerita dan perjalanan alumni.

#### UC-MEM-03 Membaca Kenangan Alumni

##### Aktor

Alumni

##### Deskripsi

Membaca kenangan dan pesan alumni.

### 3.12 Modul Peta Alumni

#### UC-MAP-01 Melihat Peta Persebaran Alumni

##### Aktor

Alumni

##### Deskripsi

Menampilkan persebaran alumni berdasarkan lokasi saat ini.

#### UC-MAP-02 Melihat Detail Alumni pada Peta

##### Aktor

Alumni

##### Deskripsi

Menampilkan daftar alumni pada lokasi tertentu.

#### UC-MAP-03 Melihat Statistik Persebaran

##### Aktor

Alumni

##### Deskripsi

Menampilkan statistik berdasarkan:

* Kota
* Negara

### 3.13 Modul Timeline Alumni

#### UC-TIME-01 Menambah Riwayat Lokasi

##### Aktor

Alumni

##### Deskripsi

Menambahkan lokasi tempat tinggal pada periode tertentu.

#### UC-TIME-02 Mengubah Riwayat Lokasi

##### Aktor

Alumni

##### Deskripsi

Mengubah data timeline lokasi.

#### UC-TIME-03 Melihat Timeline Alumni

##### Aktor

Alumni

##### Deskripsi

Menampilkan perjalanan hidup alumni berdasarkan waktu dan lokasi.

### 3.14 Modul WhatsApp Analytics

#### UC-WA-01 Upload File Chat

##### Aktor

Administrator

##### Deskripsi

Mengunggah file ekspor WhatsApp.

#### UC-WA-02 Generate Analytics

##### Aktor

Administrator

##### Deskripsi

Menghasilkan statistik grup alumni.

#### UC-WA-03 Melihat Statistik Anggota

##### Aktor

Alumni

##### Deskripsi

Menampilkan:

* Top Active Member
* Silent Reader
* Link Poster
* Image Poster
* Nocturnal Chatter
* Work Time Chatter
* Weekend Warrior
* Emoji Champion

#### UC-WA-04 Melihat Statistik Grup

##### Aktor

Alumni

##### Deskripsi

Menampilkan:

* Tahun paling ramai
* Bulan paling ramai
* Jam paling ramai

#### UC-WA-05 Melihat Topik Populer

##### Aktor

Alumni

##### Deskripsi

Menampilkan 10 topik yang paling sering dibahas.

#### UC-WA-06 Melihat Nostalgia Word Cloud

##### Aktor

Alumni

##### Deskripsi

Menampilkan visualisasi kata yang paling sering digunakan.

### 3.15 Modul Administrasi Sistem

#### UC-ADM-01 Kelola User

##### Aktor

Superadmin

##### Deskripsi

Mengelola akun pengguna.

#### UC-ADM-02 Kelola Role

##### Aktor

Superadmin

##### Deskripsi

Mengelola role dan permission.

#### UC-ADM-03 Audit Log

##### Aktor

Superadmin

##### Deskripsi

Melihat aktivitas pengguna dalam sistem.

#### UC-ADM-04 Konfigurasi Sistem

##### Aktor

Superadmin

##### Deskripsi

Mengelola konfigurasi website.

### 3.16 Ringkasan Modul

Total modul utama:

1. Autentikasi
2. Profil Alumni
3. Direktori Alumni
4. RSVP
5. Pembayaran
6. Donasi
7. Rooming
8. Dokumentasi
9. Buku Kenangan Digital
10. Peta Alumni
11. Timeline Alumni
12. WhatsApp Analytics
13. Administrasi Sistem

Total use case yang teridentifikasi pada fase analisis saat ini: 53 use case.

## BAB 4 - INFORMATION ARCHITECTURE & MENU STRUCTURE

### 4.1 Pendahuluan

Information Architecture (IA) mendefinisikan struktur informasi dan navigasi yang digunakan dalam Sistem Manajemen Reuni Alumni Teknik Geodesi UGM Angkatan 1996.

Tujuan utama penyusunan Information Architecture adalah:

1. Menyusun struktur informasi yang mudah dipahami pengguna.
2. Menentukan hubungan antar halaman.
3. Menentukan menu dan sub menu sistem.
4. Menjadi dasar desain UI/UX.
5. Menjadi dasar implementasi routing Laravel.
6. Menjadi dasar pengelompokan modul pada sistem.

### 4.2 Arsitektur Sistem Secara Umum

Sistem dibagi menjadi tiga area utama:

#### Area Publik

Dapat diakses tanpa login.

Tujuan:

* Informasi kegiatan reuni
* Dokumentasi publik
* Berita dan pengumuman

#### Area Alumni

Dapat diakses setelah login.

Tujuan:

* Mengelola profil alumni
* Mengakses direktori alumni
* Mengakses dokumentasi internal
* Mengakses buku kenangan digital
* Mengakses peta alumni
* Mengakses WhatsApp Analytics

#### Area Administrasi

Dapat diakses oleh Administrator, Bendahara, dan Superadmin.

Tujuan:

* Mengelola seluruh data sistem
* Mengelola operasional reuni
* Mengelola keuangan
* Mengelola dokumentasi

### 4.3 Sitemap Sistem

#### Public Website

Home
├── Tentang Reuni
├── Rundown Acara
├── Lokasi Acara
├── Berita
├── Galeri Publik
├── Donatur
├── Kontak Panitia
└── Login

#### Alumni Portal

Dashboard
├── Profil Saya
├── Direktori Alumni
├── Buku Kenangan
├── Dokumentasi
├── Peta Alumni
├── Timeline Alumni
├── WhatsApp Analytics
├── Informasi Kamar
├── Status Pembayaran
├── RSVP
└── Pengaturan Akun

#### Administration Portal

Dashboard Admin
├── Manajemen Alumni
├── RSVP
├── Rooming
├── Dokumentasi
├── Berita
├── Donasi
├── Pembayaran
├── WhatsApp Analytics
├── User Management
├── Audit Log
└── Konfigurasi Sistem

### 4.4 Struktur Menu Public Website

#### Home

Landing page utama sistem.

Konten:

* Hero Banner
* Countdown Reuni
* Informasi singkat reuni
* Statistik alumni
* Berita terbaru
* Dokumentasi pilihan
* Tombol login

#### Tentang Reuni

Konten:

* Filosofi Kembali ke Titik Nol
* Makna logo
* Tema reuni
* Sejarah singkat Geodesi 96

#### Rundown Acara

Konten:

* Hari Pertama
* Hari Kedua
* Gala Dinner

#### Lokasi Acara

Konten:

* Kampung Wisata Tembi
* Departemen Teknik Geodesi UGM
* Lokasi Gala Dinner

Dilengkapi:

* Peta
* Petunjuk arah
* Informasi lokasi

#### Berita

Konten:

* Daftar berita
* Detail berita

#### Galeri Publik

Konten:

* Foto publik
* Video publik

#### Donatur

Konten:

* Daftar donatur
* Donatur anonim

#### Kontak Panitia

Konten:

* Informasi panitia
* Nomor kontak

### 4.5 Struktur Menu Dashboard Alumni

#### Dashboard

Halaman pertama setelah login.

Widget:

* Status RSVP
* Status pembayaran
* Informasi kamar
* Berita terbaru
* Dokumentasi terbaru

### 4.6 Struktur Menu Profil Saya

Profil Saya
├── Informasi Dasar
├── Foto Masa Kuliah
├── Foto Saat Ini
├── Cerita Alumni
├── Kenangan Alumni
├── Pesan Alumni
├── Timeline Lokasi
└── Pengaturan Akun

#### Informasi Dasar

Data:

* Nama
* Panggilan
* WhatsApp
* Email
* Kota
* Negara
* Instansi
* Jabatan

#### Timeline Lokasi

Data:

* Bulan
* Tahun
* Kota
* Negara
* Koordinat

Fungsi:

* Tambah
* Ubah
* Hapus

#### Pengaturan Akun

Fungsi:

* Ganti password
* Ganti nomor WhatsApp

### 4.7 Struktur Menu Direktori Alumni

Direktori Alumni
├── Semua Alumni
├── Alumni Aktif
├── Alumni Memorial
├── Berdasarkan Kota
├── Berdasarkan Negara
└── Pencarian Alumni

#### Detail Profil Alumni

Menampilkan:

* Informasi alumni
* Cerita alumni
* Kenangan alumni
* Pesan alumni
* Uploaded Gallery
* Tagged Gallery
* Timeline Alumni

### 4.8 Struktur Menu Buku Kenangan Digital

Buku Kenangan
├── Seluruh Alumni
├── Cerita Alumni
├── Kenangan Alumni
├── Pesan Alumni
└── Memorial Alumni

### 4.9 Struktur Menu Dokumentasi

Dokumentasi
├── Semua Dokumentasi
├── Foto
├── Video
├── Uploaded
├── Tagged
├── Dokumentasi Publik
└── Dokumentasi Internal

#### Detail Dokumentasi

Menampilkan:

* Foto atau video
* Uploader
* Tanggal upload
* Alumni yang ditandai
* Tahun dokumentasi
* Deskripsi

### 4.10 Struktur Menu Peta Alumni

Peta Alumni
├── Peta Persebaran
├── Statistik Kota
├── Statistik Negara
└── Detail Lokasi

#### Detail Lokasi

Menampilkan:

* Kota
* Negara
* Daftar alumni
* Jumlah alumni

### 4.11 Struktur Menu Timeline Alumni

Timeline Alumni
├── Timeline Saya
├── Timeline Alumni Lain
└── Timeline Memorial

#### Visualisasi Timeline

Menampilkan:

* Tahun
* Kota
* Negara
* Perpindahan lokasi

### 4.12 Struktur Menu WhatsApp Analytics

WhatsApp Analytics
├── Hall of Fame
├── Statistik Grup
├── Topik Populer
├── Word Cloud
└── Insight Historis

#### Hall of Fame

Kategori:

* Top Active Member
* Silent Reader
* Link Poster
* Image Poster
* Nocturnal Chatter
* Work Time Chatter
* Weekend Warrior
* Emoji Champion

#### Statistik Grup

Konten:

* Tahun paling ramai
* Bulan paling ramai
* Hari paling ramai
* Jam paling ramai

#### Topik Populer

Konten:

* Top 10 topik diskusi

#### Word Cloud

Konten:

* Visualisasi kata populer

### 4.13 Struktur Menu Informasi Kamar

Informasi Kamar
├── Data Kamar
├── Teman Sekamar
└── Informasi Penginapan

### 4.14 Struktur Menu Pembayaran

Pembayaran
├── Status Pembayaran
└── Informasi Pembayaran

### 4.15 Struktur Menu RSVP

RSVP
├── Status Kehadiran
└── Riwayat Perubahan

### 4.16 Struktur Menu Administrasi

Dashboard Admin
├── Alumni
├── RSVP
├── Rooming
├── Dokumentasi
├── Berita
├── WhatsApp Analytics
└── Laporan

### 4.17 Struktur Menu Bendahara

Dashboard Bendahara
├── Pembayaran
├── Donasi
├── Statistik Pembayaran
├── Statistik Donasi
└── Laporan Keuangan

### 4.18 Struktur Menu Superadmin

Dashboard Superadmin
├── User Management
├── Role & Permission
├── Konfigurasi Sistem
├── Audit Log
├── Backup Data
└── Monitoring Sistem

### 4.19 Navigasi Utama Alumni

Menu utama yang muncul pada navbar alumni:

1. Dashboard
2. Direktori Alumni
3. Buku Kenangan
4. Dokumentasi
5. Peta Alumni
6. WhatsApp Analytics
7. RSVP
8. Pembayaran
9. Kamar
10. Profil Saya

Menu ini dirancang sebagai navigasi utama yang akan paling sering digunakan oleh alumni.

### 4.20 Prinsip Desain Informasi

Sistem dirancang menggunakan prinsip:

1. Mobile First.
2. Maksimal tiga klik menuju informasi utama.
3. Alumni sebagai pusat navigasi.
4. Dokumentasi sebagai aset utama pasca reuni.
5. Arsip digital jangka panjang.
6. Konsistensi navigasi antara desktop dan mobile.
7. Pencarian tersedia pada seluruh data alumni dan dokumentasi.

## BAB 5 - DATA ARCHITECTURE & ENTITY RELATIONSHIP DESIGN

### 5.1 Pendahuluan

Bab ini menjelaskan arsitektur data dan hubungan antar entitas yang digunakan dalam Sistem Manajemen Reuni Alumni Teknik Geodesi UGM Angkatan 1996.

Perancangan data dilakukan untuk mendukung seluruh kebutuhan sistem yang telah didefinisikan pada bab-bab sebelumnya, mulai dari pengelolaan data alumni, dokumentasi, pembayaran, donasi, rooming, peta alumni, timeline alumni, hingga analisis grup WhatsApp.

Tujuan utama perancangan arsitektur data adalah:

1. Menjamin konsistensi dan integritas data.
2. Mengurangi redundansi data.
3. Mendukung kebutuhan operasional reuni.
4. Mendukung kebutuhan arsip digital jangka panjang.
5. Mendukung pengembangan sistem di masa depan tanpa perubahan struktur yang signifikan.

Sistem menggunakan pendekatan Relational Database Management System (RDBMS).

### 5.2 Prinsip Perancangan Data

Perancangan basis data menggunakan prinsip berikut:

#### Single Source of Truth

Setiap data utama hanya disimpan pada satu lokasi utama.

Contoh:

* Data alumni disimpan pada entitas Alumni.
* Data pembayaran disimpan pada entitas Pembayaran.
* Data donasi disimpan pada entitas Donasi.

#### Referential Integrity

Hubungan antar entitas dijaga menggunakan foreign key sehingga tidak terjadi data yatim (orphan record).

#### Auditability

Perubahan data penting dapat ditelusuri melalui pencatatan aktivitas pengguna.

#### Soft Delete

Beberapa entitas menggunakan mekanisme soft delete agar data tetap dapat dipulihkan apabila diperlukan.

Contoh:

* Foto
* Video
* Berita

#### Historical Preservation

Data sejarah tidak dihapus walaupun alumni telah meninggal atau kegiatan reuni telah selesai.

### 5.3 Arsitektur Data Tingkat Tinggi

Secara konseptual sistem terdiri atas kelompok data berikut:

#### Data Identitas dan Akses

* Role
* User
* Alumni

#### Data Alumni

* Alumni
* Alumni Timeline
* City
* Country

#### Data Operasional Reuni

* RSVP
* Payment
* Donation
* Room
* Room Assignment

#### Data Dokumentasi

* Photo
* Video
* Photo Tag
* Video Tag

#### Data Publikasi

* News

#### Data Analitik

* WhatsApp Import
* WhatsApp Statistics

#### Data Sistem

* Audit Log

### 5.4 Entitas Role

Entitas Role digunakan untuk mendefinisikan hak akses pengguna.

Role yang digunakan pada sistem:

* Superadmin
* Administrator
* Bendahara
* Alumni

Hubungan:

Role → User

Relasi:

One-to-Many

Satu role dapat dimiliki oleh banyak user.

### 5.5 Entitas User

Entitas User digunakan untuk autentikasi dan otorisasi.

Data yang dikelola pada entitas ini berkaitan dengan:

* Nomor WhatsApp untuk login
* Password
* Status akun
* Role pengguna

Hubungan:

Role → User

Relasi:

Many-to-One

Hubungan:

User → Alumni

Relasi:

One-to-One

Catatan:

User hanya berisi informasi yang berkaitan dengan autentikasi dan akses sistem.

### 5.6 Entitas Alumni

Entitas Alumni merupakan entitas inti sistem yang menyimpan seluruh informasi profil alumni.

Data yang dikelola meliputi:

* Identitas alumni
* Informasi pekerjaan
* Informasi domisili
* Informasi buku kenangan
* Informasi memorial
* Foto profil

Hubungan:

User → Alumni

Relasi:

One-to-One

Hubungan:

Alumni → RSVP

Relasi:

One-to-One

Hubungan:

Alumni → Payment

Relasi:

One-to-One

Hubungan:

Alumni → Donation

Relasi:

One-to-One

Hubungan:

Alumni → Timeline

Relasi:

One-to-Many

Hubungan:

Alumni → Photo

Relasi:

One-to-Many

Sebagai uploader.

Hubungan:

Alumni → Video

Relasi:

One-to-Many

Sebagai uploader.

Hubungan:

Alumni → Room Assignment

Relasi:

One-to-One

### 5.7 Pemisahan User dan Alumni

Sistem menggunakan pemisahan antara entitas User dan Alumni.

Tujuan pemisahan:

1. Memisahkan data autentikasi dari data profil.
2. Mempermudah pengelolaan role.
3. Mempermudah penambahan role baru di masa depan.
4. Mengurangi duplikasi data.
5. Mengikuti praktik pengembangan Laravel yang umum digunakan.

Struktur konseptual:

Role
→ User
→ Alumni

Dengan pendekatan ini:

* User menyimpan data login.
* Alumni menyimpan data profil.

### 5.8 Entitas Country

Menyimpan daftar negara yang digunakan oleh sistem.

Digunakan untuk:

* Domisili alumni
* Timeline alumni
* Statistik negara
* Peta alumni

Hubungan:

Country → City

Relasi:

One-to-Many

Hubungan:

Country → Alumni

Relasi:

One-to-Many

### 5.9 Entitas City

Menyimpan daftar kota yang digunakan oleh sistem.

Digunakan untuk:

* Domisili alumni
* Timeline alumni
* Statistik kota
* Peta alumni

Hubungan:

Country → City

Relasi:

Many-to-One

Hubungan:

City → Alumni

Relasi:

One-to-Many

Hubungan:

City → Timeline

Relasi:

One-to-Many

Catatan:

Penggunaan master city dan country bertujuan menjaga konsistensi data lokasi dan meningkatkan akurasi statistik persebaran alumni.

### 5.10 Entitas Alumni Timeline

Menyimpan riwayat lokasi alumni dari waktu ke waktu.

Contoh:

1996 – Yogyakarta

2002 – Jakarta

2011 – Balikpapan

2019 – Denpasar

2026 – Singapura

Hubungan:

Alumni → Timeline

Relasi:

One-to-Many

Hubungan:

City → Timeline

Relasi:

One-to-Many

Hubungan:

Country → Timeline

Relasi:

One-to-Many

### 5.11 Entitas RSVP

Menyimpan status kehadiran alumni.

Status yang digunakan:

* Belum Merespon
* Hadir
* Tidak Hadir

Hubungan:

Alumni → RSVP

Relasi:

One-to-One

### 5.12 Entitas Payment

Menyimpan data pembayaran kontribusi reuni.

Status yang digunakan:

* Belum Bayar
* Menunggu Verifikasi
* Lunas

Hubungan:

Alumni → Payment

Relasi:

One-to-One

Hubungan:

User → Payment

Relasi:

One-to-Many

Sebagai verifikator.

Catatan:

Walaupun saat ini satu alumni hanya memiliki satu pembayaran, struktur data tetap dapat dikembangkan menjadi one-to-many apabila diperlukan di masa mendatang.

### 5.13 Entitas Donation

Menyimpan data donasi alumni.

Hubungan:

Alumni → Donation

Relasi:

One-to-One

Status publikasi donor:

* Tampilkan Nama Saya
* Donatur Anonim

Catatan:

Walaupun saat ini satu alumni hanya memiliki satu donasi, struktur data dapat dikembangkan menjadi one-to-many pada masa mendatang.

### 5.14 Entitas Room

Menyimpan data kamar penginapan.

Hubungan:

Room → Room Assignment

Relasi:

One-to-Many

### 5.15 Entitas Room Assignment

Menghubungkan alumni dengan kamar.

Hubungan:

Room → Room Assignment

Relasi:

One-to-Many

Hubungan:

Alumni → Room Assignment

Relasi:

One-to-One

Catatan:

Satu alumni hanya dapat ditempatkan pada satu kamar selama kegiatan reuni.

### 5.16 Entitas Photo

Menyimpan dokumentasi foto.

Karakteristik:

* Disimpan di server.
* Dikompresi otomatis.
* Di-resize otomatis.
* Tidak menyimpan file resolusi asli.

Hubungan:

Alumni → Photo

Relasi:

One-to-Many

Sebagai uploader.

Hubungan:

Photo → Photo Tag

Relasi:

One-to-Many

Hubungan:

Photo ↔ Alumni

Relasi:

Many-to-Many melalui Photo Tag.

### 5.17 Entitas Video

Menyimpan dokumentasi video.

Video tidak disimpan pada server.

Video direpresentasikan sebagai URL eksternal.

Platform yang didukung:

* YouTube
* Google Drive

Hubungan:

Alumni → Video

Relasi:

One-to-Many

Sebagai uploader.

Hubungan:

Video → Video Tag

Relasi:

One-to-Many

Hubungan:

Video ↔ Alumni

Relasi:

Many-to-Many melalui Video Tag.

### 5.18 Entitas Photo Tag

Merupakan tabel penghubung antara Photo dan Alumni.

Fungsi:

Menyimpan informasi alumni yang muncul pada suatu foto.

Relasi:

Photo ↔ Alumni

Many-to-Many

### 5.19 Entitas Video Tag

Merupakan tabel penghubung antara Video dan Alumni.

Fungsi:

Menyimpan informasi alumni yang muncul pada suatu video.

Relasi:

Video ↔ Alumni

Many-to-Many

### 5.20 Entitas News

Menyimpan artikel, berita, dan pengumuman.

Hubungan:

User → News

Relasi:

One-to-Many

Sebagai penulis.

### 5.21 Entitas WhatsApp Import

Menyimpan informasi file ekspor WhatsApp yang diunggah ke sistem.

Fungsi:

* Menyimpan metadata impor.
* Menjadi sumber data analisis grup.

Hubungan:

User → WhatsApp Import

Relasi:

One-to-Many

Hubungan:

WhatsApp Import → WhatsApp Statistics

Relasi:

One-to-Many

### 5.22 Entitas WhatsApp Statistics

Menyimpan hasil analisis grup WhatsApp.

Kategori statistik meliputi:

* Top Active Member
* Silent Reader
* Link Poster
* Image Poster
* Nocturnal Chatter
* Work Time Chatter
* Weekend Warrior
* Emoji Champion
* Topik Populer
* Statistik Aktivitas
* Word Cloud

Hubungan:

WhatsApp Import → WhatsApp Statistics

Relasi:

One-to-Many

### 5.23 Entitas Audit Log

Menyimpan aktivitas penting pengguna.

Contoh aktivitas:

* Login
* Logout
* Ubah profil
* Ubah password
* Upload dokumentasi
* Hapus dokumentasi
* Verifikasi pembayaran
* Kelola donasi

Hubungan:

User → Audit Log

Relasi:

One-to-Many

### 5.24 Diagram Relasi Tingkat Tinggi

Role
│
└── User
│
├── Alumni
│   ├── RSVP
│   ├── Payment
│   ├── Donation
│   ├── Timeline
│   ├── Room Assignment
│   ├── Photo
│   └── Video
│
├── News
├── Audit Log
└── WhatsApp Import

Country
│
└── City
│
├── Alumni
└── Timeline

Room
│
└── Room Assignment

Photo
│
└── Photo Tag
│
└── Alumni

Video
│
└── Video Tag
│
└── Alumni

WhatsApp Import
│
└── WhatsApp Statistics

### 5.25 Kelompok Data Master

Data yang relatif jarang berubah:

* Role
* Country
* City
* Alumni
* Room

### 5.26 Kelompok Data Transaksional

Data yang berubah selama operasional reuni:

* RSVP
* Payment
* Donation
* Room Assignment
* Photo
* Video
* News

### 5.27 Kelompok Data Historis

Data yang disimpan permanen:

* Alumni Timeline
* Photo
* Video
* WhatsApp Statistics
* Audit Log
* Memorial Alumni

### 5.28 Kelompok Data Arsip Jangka Panjang

Data yang tetap dipertahankan setelah reuni selesai:

* Profil Alumni
* Buku Kenangan Digital
* Dokumentasi
* Timeline Alumni
* Peta Alumni
* Donasi
* Berita Reuni
* Statistik WhatsApp
* Memorial Alumni

## BAB 6 - PHYSICAL DATABASE DESIGN & LARAVEL SCHEMA

### 6.1 Pendahuluan

Bab ini menjelaskan rancangan fisik basis data untuk Sistem Manajemen Reuni Alumni Teknik Geodesi UGM Angkatan 1996.

Rancangan ini disusun berdasarkan arsitektur data pada BAB 5 dan ditujukan sebagai acuan teknis bagi tim developer dalam membuat:

1. Migration Laravel.
2. Model Eloquent.
3. Relasi antar model.
4. Foreign key.
5. Index.
6. Unique constraint.
7. Soft delete.
8. Validasi data.
9. Seeder awal sistem.

Database menggunakan pendekatan relational database dan disarankan menggunakan MySQL atau MariaDB.

### 6.2 Konvensi Penamaan

#### Nama Tabel

Nama tabel menggunakan format plural snake_case.

Contoh:

* users
* roles
* alumni
* alumni_timelines
* payments
* donations
* rooms
* room_assignments
* photos
* videos

#### Nama Kolom

Nama kolom menggunakan snake_case.

Contoh:

* full_name
* nickname
* whatsapp_number
* current_city_id
* current_country_id

#### Primary Key

Setiap tabel menggunakan kolom:

* id

dengan tipe unsigned big integer auto increment.

#### Timestamp

Setiap tabel utama menggunakan:

* created_at
* updated_at

#### Soft Delete

Tabel tertentu menggunakan:

* deleted_at

### 6.3 Daftar Tabel

Database terdiri dari tabel berikut:

1. roles
2. users
3. countries
4. cities
5. alumni
6. alumni_timelines
7. rsvps
8. payments
9. donations
10. rooms
11. room_assignments
12. photos
13. photo_tags
14. videos
15. video_tags
16. news
17. whatsapp_imports
18. whatsapp_statistics
19. audit_logs

### 6.4 Tabel roles

#### Fungsi

Menyimpan daftar role pengguna.

#### Struktur Tabel

| Field       | Type        | Constraint  | Keterangan     |
| ----------- | ----------- | ----------- | -------------- |
| id          | bigInteger  | primary key | ID role        |
| name        | string(50)  | unique      | Nama role      |
| description | string(255) | nullable    | Deskripsi role |
| created_at  | timestamp   | nullable    | Waktu dibuat   |
| updated_at  | timestamp   | nullable    | Waktu diubah   |

#### Data Awal

| name          | description                     |
| ------------- | ------------------------------- |
| superadmin    | Pengelola teknis sistem         |
| administrator | Panitia pelaksana reuni         |
| bendahara     | Pengelola pembayaran dan donasi |
| alumni        | Anggota alumni                  |

### 6.5 Tabel users

#### Fungsi

Menyimpan data akun untuk login dan otorisasi sistem.

#### Struktur Tabel

| Field           | Type        | Constraint   | Keterangan                 |
| --------------- | ----------- | ------------ | -------------------------- |
| id              | bigInteger  | primary key  | ID user                    |
| role_id         | bigInteger  | foreign key  | Relasi ke roles            |
| whatsapp_number | string(30)  | unique       | Nomor WhatsApp untuk login |
| password        | string(255) | not null     | Password terenkripsi       |
| is_active       | boolean     | default true | Status akun aktif          |
| last_login_at   | timestamp   | nullable     | Waktu login terakhir       |
| remember_token  | string(100) | nullable     | Token remember me Laravel  |
| created_at      | timestamp   | nullable     | Waktu dibuat               |
| updated_at      | timestamp   | nullable     | Waktu diubah               |
| deleted_at      | timestamp   | nullable     | Soft delete                |

#### Relasi

* users.role_id → roles.id

#### Catatan

Nomor WhatsApp digunakan sebagai username login. Alumni dapat mengganti nomor WhatsApp setelah login menggunakan nomor lama.

### 6.6 Tabel countries

#### Fungsi

Menyimpan master negara.

#### Struktur Tabel

| Field      | Type          | Constraint  | Keterangan        |
| ---------- | ------------- | ----------- | ----------------- |
| id         | bigInteger    | primary key | ID negara         |
| name       | string(100)   | unique      | Nama negara       |
| code       | string(10)    | nullable    | Kode negara       |
| latitude   | decimal(10,7) | nullable    | Latitude default  |
| longitude  | decimal(10,7) | nullable    | Longitude default |
| created_at | timestamp     | nullable    | Waktu dibuat      |
| updated_at | timestamp     | nullable    | Waktu diubah      |

### 6.7 Tabel cities

#### Fungsi

Menyimpan master kota.

#### Struktur Tabel

| Field      | Type          | Constraint  | Keterangan          |
| ---------- | ------------- | ----------- | ------------------- |
| id         | bigInteger    | primary key | ID kota             |
| country_id | bigInteger    | foreign key | Relasi ke countries |
| name       | string(100)   | index       | Nama kota           |
| latitude   | decimal(10,7) | nullable    | Latitude kota       |
| longitude  | decimal(10,7) | nullable    | Longitude kota      |
| created_at | timestamp     | nullable    | Waktu dibuat        |
| updated_at | timestamp     | nullable    | Waktu diubah        |

#### Relasi

* cities.country_id → countries.id

#### Constraint

Kombinasi country_id dan name sebaiknya unique.

### 6.8 Tabel alumni

#### Fungsi

Menyimpan profil alumni.

#### Struktur Tabel

| Field                | Type        | Constraint            | Keterangan                      |
| -------------------- | ----------- | --------------------- | ------------------------------- |
| id                   | bigInteger  | primary key           | ID alumni                       |
| user_id              | bigInteger  | unique, foreign key   | Relasi ke users                 |
| student_number       | string(50)  | nullable, unique      | NIM masa kuliah                 |
| full_name            | string(150) | not null              | Nama lengkap                    |
| nickname             | string(100) | nullable              | Nama panggilan waktu kuliah     |
| email                | string(150) | nullable              | Email                           |
| current_city_id      | bigInteger  | nullable, foreign key | Kota domisili saat ini          |
| current_country_id   | bigInteger  | nullable, foreign key | Negara domisili saat ini        |
| company              | string(150) | nullable              | Instansi/perusahaan             |
| job_title            | string(150) | nullable              | Profesi/jabatan                 |
| alumni_status        | enum        | default active        | active/deceased                 |
| rsvp_status          | enum        | default pending       | pending/attending/not_attending |
| special_notes        | text        | nullable              | Catatan khusus                  |
| short_story          | text        | nullable              | Cerita singkat                  |
| memorable_story      | text        | nullable              | Kenangan lucu/tak terlupakan    |
| message_to_friends   | text        | nullable              | Pesan untuk alumni              |
| college_photo_path   | string(255) | nullable              | Foto masa kuliah                |
| current_photo_path   | string(255) | nullable              | Foto saat ini                   |
| is_profile_completed | boolean     | default false         | Status kelengkapan profil       |
| created_at           | timestamp   | nullable              | Waktu dibuat                    |
| updated_at           | timestamp   | nullable              | Waktu diubah                    |
| deleted_at           | timestamp   | nullable              | Soft delete                     |

#### Relasi

* alumni.user_id → users.id
* alumni.current_city_id → cities.id
* alumni.current_country_id → countries.id

#### Enum alumni_status

* active
* deceased

#### Enum rsvp_status

* pending
* attending
* not_attending

#### Catatan

Status RSVP juga dapat ditempatkan pada tabel rsvps. Namun untuk kebutuhan akses cepat pada dashboard dan direktori, field rsvp_status dapat disimpan pada alumni sebagai denormalized summary. Apabila ingin lebih strict secara normalisasi, field ini dapat dihapus dan hanya menggunakan tabel rsvps.

### 6.9 Tabel alumni_timelines

#### Fungsi

Menyimpan riwayat lokasi alumni dari waktu ke waktu.

#### Struktur Tabel

| Field           | Type          | Constraint            | Keterangan                 |
| --------------- | ------------- | --------------------- | -------------------------- |
| id              | bigInteger    | primary key           | ID timeline                |
| alumni_id       | bigInteger    | foreign key           | Relasi ke alumni           |
| month           | tinyInteger   | nullable              | Bulan, 1–12                |
| year            | smallInteger  | not null              | Tahun                      |
| city_id         | bigInteger    | nullable, foreign key | Kota                       |
| country_id      | bigInteger    | nullable, foreign key | Negara                     |
| latitude        | decimal(10,7) | nullable              | Latitude manual/geocoding  |
| longitude       | decimal(10,7) | nullable              | Longitude manual/geocoding |
| location_source | enum          | default geocoded      | geocoded/manual            |
| notes           | string(255)   | nullable              | Catatan lokasi             |
| created_at      | timestamp     | nullable              | Waktu dibuat               |
| updated_at      | timestamp     | nullable              | Waktu diubah               |
| deleted_at      | timestamp     | nullable              | Soft delete                |

#### Relasi

* alumni_timelines.alumni_id → alumni.id
* alumni_timelines.city_id → cities.id
* alumni_timelines.country_id → countries.id

#### Enum location_source

* geocoded
* manual

### 6.10 Tabel rsvps

#### Fungsi

Menyimpan data RSVP alumni.

#### Struktur Tabel

| Field       | Type       | Constraint          | Keterangan       |
| ----------- | ---------- | ------------------- | ---------------- |
| id          | bigInteger | primary key         | ID RSVP          |
| alumni_id   | bigInteger | unique, foreign key | Relasi ke alumni |
| status      | enum       | default pending     | Status RSVP      |
| response_at | timestamp  | nullable            | Waktu respon     |
| notes       | text       | nullable            | Catatan RSVP     |
| created_at  | timestamp  | nullable            | Waktu dibuat     |
| updated_at  | timestamp  | nullable            | Waktu diubah     |

#### Relasi

* rsvps.alumni_id → alumni.id

#### Enum status

* pending
* attending
* not_attending

### 6.11 Tabel payments

#### Fungsi

Menyimpan data pembayaran kontribusi reuni.

#### Struktur Tabel

| Field        | Type          | Constraint            | Keterangan                 |
| ------------ | ------------- | --------------------- | -------------------------- |
| id           | bigInteger    | primary key           | ID pembayaran              |
| alumni_id    | bigInteger    | unique, foreign key   | Relasi ke alumni           |
| amount       | decimal(15,2) | nullable              | Nominal pembayaran         |
| status       | enum          | default unpaid        | Status pembayaran          |
| payment_date | date          | nullable              | Tanggal pembayaran         |
| verified_by  | bigInteger    | nullable, foreign key | User bendahara/verifikator |
| verified_at  | timestamp     | nullable              | Waktu verifikasi           |
| notes        | text          | nullable              | Catatan pembayaran         |
| created_at   | timestamp     | nullable              | Waktu dibuat               |
| updated_at   | timestamp     | nullable              | Waktu diubah               |

#### Relasi

* payments.alumni_id → alumni.id
* payments.verified_by → users.id

#### Enum status

* unpaid
* pending_verification
* paid

### 6.12 Tabel donations

#### Fungsi

Menyimpan data donasi alumni.

#### Struktur Tabel

| Field              | Type          | Constraint            | Keterangan             |
| ------------------ | ------------- | --------------------- | ---------------------- |
| id                 | bigInteger    | primary key           | ID donasi              |
| alumni_id          | bigInteger    | unique, foreign key   | Relasi ke alumni       |
| amount             | decimal(15,2) | nullable              | Nominal donasi         |
| publication_status | enum          | default show_name     | Status publikasi donor |
| notes              | text          | nullable              | Catatan donasi         |
| managed_by         | bigInteger    | nullable, foreign key | User bendahara         |
| created_at         | timestamp     | nullable              | Waktu dibuat           |
| updated_at         | timestamp     | nullable              | Waktu diubah           |

#### Relasi

* donations.alumni_id → alumni.id
* donations.managed_by → users.id

#### Enum publication_status

* show_name
* anonymous

#### Catatan

Nominal donasi tidak ditampilkan pada halaman publik maupun halaman alumni, kecuali untuk role Bendahara dan Superadmin.

### 6.13 Tabel rooms

#### Fungsi

Menyimpan daftar kamar penginapan.

#### Struktur Tabel

| Field          | Type        | Constraint  | Keterangan           |
| -------------- | ----------- | ----------- | -------------------- |
| id             | bigInteger  | primary key | ID kamar             |
| room_name      | string(100) | not null    | Nama/nomor kamar     |
| room_type      | string(100) | nullable    | Tipe kamar           |
| capacity       | tinyInteger | default 2   | Kapasitas kamar      |
| location_notes | string(255) | nullable    | Catatan lokasi kamar |
| notes          | text        | nullable    | Catatan tambahan     |
| created_at     | timestamp   | nullable    | Waktu dibuat         |
| updated_at     | timestamp   | nullable    | Waktu diubah         |
| deleted_at     | timestamp   | nullable    | Soft delete          |

### 6.14 Tabel room_assignments

#### Fungsi

Menyimpan penempatan alumni ke dalam kamar.

#### Struktur Tabel

| Field       | Type       | Constraint            | Keterangan         |
| ----------- | ---------- | --------------------- | ------------------ |
| id          | bigInteger | primary key           | ID assignment      |
| room_id     | bigInteger | foreign key           | Relasi ke rooms    |
| alumni_id   | bigInteger | unique, foreign key   | Relasi ke alumni   |
| assigned_by | bigInteger | nullable, foreign key | User panitia       |
| notes       | text       | nullable              | Catatan assignment |
| created_at  | timestamp  | nullable              | Waktu dibuat       |
| updated_at  | timestamp  | nullable              | Waktu diubah       |

#### Relasi

* room_assignments.room_id → rooms.id
* room_assignments.alumni_id → alumni.id
* room_assignments.assigned_by → users.id

### 6.15 Tabel photos

#### Fungsi

Menyimpan data dokumentasi foto.

#### Struktur Tabel

| Field                 | Type         | Constraint       | Keterangan                 |
| --------------------- | ------------ | ---------------- | -------------------------- |
| id                    | bigInteger   | primary key      | ID foto                    |
| uploaded_by_alumni_id | bigInteger   | foreign key      | Alumni uploader            |
| title                 | string(150)  | nullable         | Judul foto                 |
| description           | text         | nullable         | Deskripsi foto             |
| file_path             | string(255)  | not null         | Path file foto             |
| thumbnail_path        | string(255)  | nullable         | Path thumbnail             |
| month                 | tinyInteger  | nullable         | Bulan foto                 |
| year                  | smallInteger | not null         | Tahun foto                 |
| visibility            | enum         | default internal | internal/public            |
| file_size             | integer      | nullable         | Ukuran file hasil kompresi |
| width                 | integer      | nullable         | Lebar foto                 |
| height                | integer      | nullable         | Tinggi foto                |
| created_at            | timestamp    | nullable         | Waktu dibuat               |
| updated_at            | timestamp    | nullable         | Waktu diubah               |
| deleted_at            | timestamp    | nullable         | Soft delete                |

#### Relasi

* photos.uploaded_by_alumni_id → alumni.id

#### Enum visibility

* internal
* public

### 6.16 Tabel photo_tags

#### Fungsi

Menyimpan tag alumni pada foto.

#### Struktur Tabel

| Field               | Type       | Constraint            | Keterangan                |
| ------------------- | ---------- | --------------------- | ------------------------- |
| id                  | bigInteger | primary key           | ID tag                    |
| photo_id            | bigInteger | foreign key           | Relasi ke photos          |
| alumni_id           | bigInteger | foreign key           | Relasi ke alumni          |
| tagged_by_alumni_id | bigInteger | nullable, foreign key | Alumni yang melakukan tag |
| created_at          | timestamp  | nullable              | Waktu dibuat              |
| updated_at          | timestamp  | nullable              | Waktu diubah              |

#### Relasi

* photo_tags.photo_id → photos.id
* photo_tags.alumni_id → alumni.id
* photo_tags.tagged_by_alumni_id → alumni.id

#### Constraint

Kombinasi photo_id dan alumni_id sebaiknya unique.

### 6.17 Tabel videos

#### Fungsi

Menyimpan data dokumentasi video dalam bentuk tautan eksternal.

#### Struktur Tabel

| Field                 | Type         | Constraint       | Keterangan                 |
| --------------------- | ------------ | ---------------- | -------------------------- |
| id                    | bigInteger   | primary key      | ID video                   |
| uploaded_by_alumni_id | bigInteger   | foreign key      | Alumni uploader            |
| title                 | string(150)  | not null         | Judul video                |
| description           | text         | nullable         | Deskripsi video            |
| video_url             | string(500)  | not null         | URL video                  |
| provider              | enum         | nullable         | youtube/google_drive/other |
| month                 | tinyInteger  | nullable         | Bulan video                |
| year                  | smallInteger | not null         | Tahun video                |
| visibility            | enum         | default internal | internal/public            |
| created_at            | timestamp    | nullable         | Waktu dibuat               |
| updated_at            | timestamp    | nullable         | Waktu diubah               |
| deleted_at            | timestamp    | nullable         | Soft delete                |

#### Relasi

* videos.uploaded_by_alumni_id → alumni.id

#### Enum provider

* youtube
* google_drive
* other

#### Enum visibility

* internal
* public

### 6.18 Tabel video_tags

#### Fungsi

Menyimpan tag alumni pada video.

#### Struktur Tabel

| Field               | Type       | Constraint            | Keterangan                |
| ------------------- | ---------- | --------------------- | ------------------------- |
| id                  | bigInteger | primary key           | ID tag                    |
| video_id            | bigInteger | foreign key           | Relasi ke videos          |
| alumni_id           | bigInteger | foreign key           | Relasi ke alumni          |
| tagged_by_alumni_id | bigInteger | nullable, foreign key | Alumni yang melakukan tag |
| created_at          | timestamp  | nullable              | Waktu dibuat              |
| updated_at          | timestamp  | nullable              | Waktu diubah              |

#### Relasi

* video_tags.video_id → videos.id
* video_tags.alumni_id → alumni.id
* video_tags.tagged_by_alumni_id → alumni.id

#### Constraint

Kombinasi video_id dan alumni_id sebaiknya unique.

### 6.19 Tabel news

#### Fungsi

Menyimpan berita dan pengumuman.

#### Struktur Tabel

| Field               | Type        | Constraint    | Keterangan               |
| ------------------- | ----------- | ------------- | ------------------------ |
| id                  | bigInteger  | primary key   | ID berita                |
| author_id           | bigInteger  | foreign key   | User penulis             |
| title               | string(200) | not null      | Judul berita             |
| slug                | string(220) | unique        | Slug URL                 |
| excerpt             | string(255) | nullable      | Ringkasan                |
| content             | longText    | not null      | Isi berita               |
| featured_image_path | string(255) | nullable      | Gambar utama             |
| status              | enum        | default draft | draft/published/archived |
| published_at        | timestamp   | nullable      | Waktu publikasi          |
| created_at          | timestamp   | nullable      | Waktu dibuat             |
| updated_at          | timestamp   | nullable      | Waktu diubah             |
| deleted_at          | timestamp   | nullable      | Soft delete              |

#### Relasi

* news.author_id → users.id

#### Enum status

* draft
* published
* archived

### 6.20 Tabel whatsapp_imports

#### Fungsi

Menyimpan metadata file chat WhatsApp yang diimpor.

#### Struktur Tabel

| Field              | Type        | Constraint       | Keterangan               |
| ------------------ | ----------- | ---------------- | ------------------------ |
| id                 | bigInteger  | primary key      | ID import                |
| uploaded_by        | bigInteger  | foreign key      | User uploader            |
| file_name          | string(255) | nullable         | Nama file                |
| file_path          | string(255) | nullable         | Path file                |
| import_start_date  | date        | nullable         | Tanggal awal chat        |
| import_end_date    | date        | nullable         | Tanggal akhir chat       |
| total_messages     | integer     | default 0        | Total pesan              |
| total_participants | integer     | default 0        | Total peserta terdeteksi |
| status             | enum        | default uploaded | Status proses            |
| processed_at       | timestamp   | nullable         | Waktu selesai proses     |
| notes              | text        | nullable         | Catatan proses           |
| created_at         | timestamp   | nullable         | Waktu dibuat             |
| updated_at         | timestamp   | nullable         | Waktu diubah             |

#### Relasi

* whatsapp_imports.uploaded_by → users.id

#### Enum status

* uploaded
* processing
* completed
* failed

### 6.21 Tabel whatsapp_statistics

#### Fungsi

Menyimpan hasil analisis WhatsApp.

#### Struktur Tabel

| Field              | Type          | Constraint            | Keterangan                 |
| ------------------ | ------------- | --------------------- | -------------------------- |
| id                 | bigInteger    | primary key           | ID statistik               |
| whatsapp_import_id | bigInteger    | foreign key           | Relasi ke whatsapp_imports |
| category           | string(100)   | index                 | Kategori statistik         |
| label              | string(150)   | nullable              | Label statistik            |
| alumni_id          | bigInteger    | nullable, foreign key | Alumni terkait             |
| value              | decimal(15,2) | nullable              | Nilai statistik            |
| rank               | integer       | nullable              | Peringkat                  |
| metadata           | json          | nullable              | Data tambahan              |
| created_at         | timestamp     | nullable              | Waktu dibuat               |
| updated_at         | timestamp     | nullable              | Waktu diubah               |

#### Relasi

* whatsapp_statistics.whatsapp_import_id → whatsapp_imports.id
* whatsapp_statistics.alumni_id → alumni.id

#### Contoh category

* active_member
* silent_reader
* link_poster
* image_poster
* nocturnal_chatter
* work_time_chatter
* weekend_warrior
* emoji_champion
* top_topic
* busiest_year
* busiest_month
* busiest_hour
* word_cloud

### 6.22 Tabel audit_logs

#### Fungsi

Menyimpan aktivitas penting pengguna.

#### Struktur Tabel

| Field       | Type        | Constraint            | Keterangan             |
| ----------- | ----------- | --------------------- | ---------------------- |
| id          | bigInteger  | primary key           | ID log                 |
| user_id     | bigInteger  | nullable, foreign key | User pelaku            |
| action      | string(100) | index                 | Nama aksi              |
| entity_type | string(100) | nullable              | Nama model/entitas     |
| entity_id   | bigInteger  | nullable              | ID entitas             |
| old_values  | json        | nullable              | Data sebelum perubahan |
| new_values  | json        | nullable              | Data setelah perubahan |
| ip_address  | string(45)  | nullable              | IP address             |
| user_agent  | text        | nullable              | Browser/user agent     |
| created_at  | timestamp   | nullable              | Waktu aktivitas        |

#### Relasi

* audit_logs.user_id → users.id

### 6.23 Rekomendasi Index

Index yang disarankan:

#### users

* whatsapp_number
* role_id
* is_active

#### alumni

* user_id
* student_number
* full_name
* nickname
* current_city_id
* current_country_id
* alumni_status
* rsvp_status

#### alumni_timelines

* alumni_id
* city_id
* country_id
* year

#### payments

* alumni_id
* status
* verified_by

#### donations

* alumni_id
* publication_status

#### photos

* uploaded_by_alumni_id
* year
* visibility

#### videos

* uploaded_by_alumni_id
* year
* visibility

#### news

* slug
* status
* published_at

#### whatsapp_statistics

* whatsapp_import_id
* category
* alumni_id
* rank

### 6.24 Rekomendasi Unique Constraint

Unique constraint yang disarankan:

#### roles

* name

#### users

* whatsapp_number

#### countries

* name

#### cities

* country_id + name

#### alumni

* user_id
* student_number

#### rsvps

* alumni_id

#### payments

* alumni_id

#### donations

* alumni_id

#### room_assignments

* alumni_id

#### photo_tags

* photo_id + alumni_id

#### video_tags

* video_id + alumni_id

#### news

* slug

### 6.25 Rekomendasi Soft Delete

Tabel yang menggunakan soft delete:

* users
* alumni
* alumni_timelines
* rooms
* photos
* videos
* news

Catatan:

Tabel payment, donation, RSVP, dan audit log tidak disarankan menggunakan soft delete karena berkaitan dengan data historis dan pelacakan aktivitas.

### 6.26 Rekomendasi Laravel Model

Model Laravel yang disarankan:

* Role
* User
* Country
* City
* Alumni
* AlumniTimeline
* Rsvp
* Payment
* Donation
* Room
* RoomAssignment
* Photo
* PhotoTag
* Video
* VideoTag
* News
* WhatsappImport
* WhatsappStatistic
* AuditLog

### 6.27 Rekomendasi Relasi Eloquent

#### User

* belongsTo Role
* hasOne Alumni
* hasMany News
* hasMany AuditLog

#### Role

* hasMany User

#### Alumni

* belongsTo User
* belongsTo City
* belongsTo Country
* hasMany AlumniTimeline
* hasOne Rsvp
* hasOne Payment
* hasOne Donation
* hasOne RoomAssignment
* hasMany Photo
* hasMany Video
* belongsToMany Photo melalui photo_tags
* belongsToMany Video melalui video_tags

#### Country

* hasMany City
* hasMany Alumni
* hasMany AlumniTimeline

#### City

* belongsTo Country
* hasMany Alumni
* hasMany AlumniTimeline

#### Room

* hasMany RoomAssignment

#### RoomAssignment

* belongsTo Room
* belongsTo Alumni

#### Photo

* belongsTo Alumni sebagai uploader
* belongsToMany Alumni melalui photo_tags

#### Video

* belongsTo Alumni sebagai uploader
* belongsToMany Alumni melalui video_tags

#### WhatsappImport

* belongsTo User
* hasMany WhatsappStatistic

#### WhatsappStatistic

* belongsTo WhatsappImport
* belongsTo Alumni

### 6.28 Catatan Implementasi Laravel

#### Authentication

Laravel authentication default dapat dimodifikasi agar menggunakan whatsapp_number sebagai credential utama.

#### Authorization

Role dapat dikelola dengan:

* Middleware custom
* Policy
* Gate

Untuk sistem kecil, role sederhana pada tabel users sudah cukup. Jika ingin lebih fleksibel, dapat menggunakan package permission.

#### File Storage

Foto disimpan menggunakan Laravel Storage.

Rekomendasi path:

* storage/app/public/photos
* storage/app/public/profile/college
* storage/app/public/profile/current
* storage/app/public/news

File foto perlu diproses dengan resize dan compression sebelum disimpan.

#### Video

Video tidak disimpan sebagai file, hanya URL.

#### Geocoding

Koordinat city dapat diisi melalui:

1. Seeder awal.
2. Geocoding otomatis.
3. Input manual admin.

#### WhatsApp Analytics

File chat WhatsApp dapat diproses melalui:

1. Upload file.
2. Parsing text.
3. Normalisasi nama peserta.
4. Filtering alumni meninggal untuk kategori ranking individu.
5. Penyimpanan hasil ke whatsapp_statistics.

### 6.29 Prioritas Implementasi Database

Urutan pembuatan migration disarankan:

1. roles
2. users
3. countries
4. cities
5. alumni
6. alumni_timelines
7. rsvps
8. payments
9. donations
10. rooms
11. room_assignments
12. photos
13. photo_tags
14. videos
15. video_tags
16. news
17. whatsapp_imports
18. whatsapp_statistics
19. audit_logs

## BAB 7 - APPLICATION FLOW & USER JOURNEY

### 7.1 Pendahuluan

Bab ini menjelaskan alur penggunaan sistem dari sudut pandang pengguna (user journey) dan proses bisnis utama (application flow).

Tujuan utama penyusunan bab ini adalah:

1. Memastikan seluruh proses bisnis telah terdefinisi.
2. Menjadi dasar perancangan UI/UX.
3. Menjadi dasar implementasi routing Laravel.
4. Menjadi dasar penyusunan test scenario.
5. Menjadi dasar penyusunan user manual.

### 7.2 Application Flow Tingkat Tinggi

Secara umum alur sistem dapat digambarkan sebagai berikut:

Persiapan Data Alumni
↓
Pembuatan Akun
↓
Login Alumni
↓
Melengkapi Profil
↓
Mengisi RSVP
↓
Verifikasi Pembayaran
↓
Penentuan Kamar
↓
Pelaksanaan Reuni
↓
Upload Dokumentasi
↓
Buku Kenangan Digital
↓
Arsip Digital Jangka Panjang

### 7.3 User Journey Alumni

#### Tahap 1 – Aktivasi dan Login

##### Kondisi Awal

Administrator telah:

* Menginput data alumni.
* Membuat akun alumni.
* Menentukan password awal.

##### Alur

1. Alumni membuka website reuni.
2. Alumni memilih menu Login.
3. Alumni memasukkan nomor WhatsApp.
4. Alumni memasukkan password.
5. Sistem memvalidasi akun.
6. Sistem menampilkan Dashboard Alumni.

##### Hasil

Alumni berhasil masuk ke sistem.

#### Tahap 2 – Melengkapi Profil

##### Tujuan

Memastikan seluruh data alumni lengkap.

##### Alur

1. Alumni membuka menu Profil Saya.
2. Alumni memeriksa data yang telah diinput panitia.
3. Alumni melengkapi data yang belum tersedia.
4. Alumni mengunggah foto masa kuliah.
5. Alumni mengunggah foto saat ini.
6. Alumni menulis cerita alumni.
7. Alumni menulis kenangan.
8. Alumni menulis pesan untuk rekan alumni.
9. Alumni menyimpan perubahan.

##### Hasil

Profil alumni menjadi lengkap.

#### Tahap 3 – Mengisi Timeline Perjalanan Hidup

##### Tujuan

Membangun peta perjalanan hidup alumni.

##### Alur

1. Alumni membuka menu Timeline Lokasi.
2. Alumni menambahkan lokasi-lokasi penting dalam hidupnya.
3. Alumni menentukan bulan dan tahun.
4. Alumni menentukan kota dan negara.
5. Sistem menyimpan timeline.

##### Hasil

Timeline alumni dapat ditampilkan pada profil dan peta alumni.

#### Tahap 4 – Mengisi RSVP

##### Tujuan

Mengonfirmasi kehadiran pada reuni.

##### Alur

1. Alumni membuka menu RSVP.
2. Alumni memilih status kehadiran.
3. Alumni menyimpan pilihan.
4. Sistem memperbarui status RSVP.

##### Status

* Hadir
* Tidak Hadir

##### Hasil

Panitia memperoleh data kehadiran.

#### Tahap 5 – Menunggu Verifikasi Pembayaran

##### Alur

1. Alumni melakukan pembayaran di luar sistem.
2. Bendahara mencatat pembayaran.
3. Bendahara melakukan verifikasi.
4. Status pembayaran berubah menjadi Lunas.

##### Hasil

Alumni terdaftar sebagai peserta yang telah menyelesaikan kewajiban pembayaran.

#### Tahap 6 – Melihat Informasi Kamar

##### Alur

1. Panitia menentukan pembagian kamar.
2. Alumni membuka menu Informasi Kamar.
3. Sistem menampilkan:

   * Nama kamar
   * Kapasitas kamar
   * Daftar penghuni kamar

##### Hasil

Alumni mengetahui lokasi menginap.

#### Tahap 7 – Mengikuti Reuni

##### Alur

1. Alumni hadir pada lokasi reuni.
2. Alumni mengikuti kegiatan.
3. Alumni mengambil foto dan video kegiatan.
4. Alumni mengumpulkan dokumentasi.

#### Tahap 8 – Mengunggah Dokumentasi

##### Tujuan

Membangun arsip dokumentasi bersama.

##### Alur

1. Alumni membuka menu Dokumentasi.
2. Alumni mengunggah foto.
3. Alumni menambahkan deskripsi.
4. Alumni menentukan tahun dokumentasi.
5. Alumni menambahkan tag alumni lain.
6. Alumni menentukan status:

   * Internal
   * Publik
7. Sistem menyimpan dokumentasi.

##### Hasil

Dokumentasi masuk ke galeri bersama.

#### Tahap 9 – Menjelajahi Dokumentasi

##### Alur

1. Alumni membuka galeri.
2. Alumni melihat foto yang diunggah orang lain.
3. Alumni melihat dokumentasi yang menandai dirinya.
4. Alumni membuka profil alumni lain.
5. Alumni melihat dokumentasi yang terkait.

##### Hasil

Terbentuk arsip digital kolektif.

#### Tahap 10 – Menjelajahi Buku Kenangan Digital

##### Alur

1. Alumni membuka menu Buku Kenangan.
2. Alumni membaca cerita alumni lain.
3. Alumni membaca kenangan alumni lain.
4. Alumni melihat timeline alumni lain.
5. Alumni melihat profil memorial.

##### Hasil

Meningkatkan interaksi dan nostalgia antar alumni.

#### Tahap 11 – Mengakses Peta Alumni

##### Alur

1. Alumni membuka menu Peta Alumni.
2. Sistem menampilkan persebaran alumni.
3. Alumni memilih kota atau negara.
4. Sistem menampilkan daftar alumni pada lokasi tersebut.

##### Hasil

Persebaran alumni dapat dipahami secara visual.

#### Tahap 12 – Mengakses WhatsApp Analytics

##### Alur

1. Alumni membuka menu WhatsApp Analytics.
2. Sistem menampilkan statistik grup.
3. Alumni melihat Hall of Fame.
4. Alumni melihat topik populer.
5. Alumni melihat word cloud nostalgia.

##### Hasil

Meningkatkan keterlibatan alumni melalui analisis historis grup.

### 7.4 User Journey Administrator

#### Persiapan Sebelum Reuni

Administrator:

1. Menginput data alumni.
2. Membuat akun alumni.
3. Memverifikasi kelengkapan data.
4. Mengelola berita.
5. Memantau RSVP.
6. Mengelola kamar.
7. Mengelola dokumentasi.

#### Selama Reuni

Administrator:

1. Memperbarui informasi kegiatan.
2. Mengelola dokumentasi.
3. Mengelola publikasi berita.

#### Setelah Reuni

Administrator:

1. Memastikan dokumentasi lengkap.
2. Menjaga kualitas arsip digital.
3. Mengelola berita pasca reuni.

### 7.5 User Journey Bendahara

#### Sebelum Reuni

1. Memasukkan data pembayaran.
2. Memverifikasi pembayaran.
3. Mengelola data donasi.
4. Menentukan donor anonim atau publik.

#### Selama Reuni

1. Memantau data pembayaran.
2. Memantau donasi.

#### Setelah Reuni

1. Menyelesaikan rekap keuangan.
2. Memastikan seluruh data pembayaran terdokumentasi.

### 7.6 User Journey Superadmin

#### Persiapan Sistem

1. Membuat role.
2. Membuat akun administrator.
3. Membuat akun bendahara.
4. Menyiapkan konfigurasi sistem.

#### Operasional Sistem

1. Memantau kesehatan sistem.
2. Mengelola hak akses.
3. Menangani masalah teknis.
4. Melakukan backup data.

#### Pasca Reuni

1. Menjaga ketersediaan sistem.
2. Menjaga arsip digital.
3. Melakukan maintenance berkala.

### 7.7 Flow Pengelolaan Dokumentasi

Upload Foto
↓
Resize Otomatis
↓
Kompresi Otomatis
↓
Simpan ke Storage
↓
Tag Alumni
↓
Tentukan Visibilitas
↓
Publikasi Dokumentasi

### 7.8 Flow Pengelolaan Video

Input URL Video
↓
Validasi URL
↓
Simpan Metadata
↓
Tag Alumni
↓
Tentukan Visibilitas
↓
Publikasi Video

### 7.9 Flow Pembayaran

Pembayaran di Luar Sistem
↓
Input oleh Bendahara
↓
Status Menunggu Verifikasi
↓
Verifikasi Bendahara
↓
Status Lunas

### 7.10 Flow Donasi

Input Donasi
↓
Tentukan Status Publikasi
↓
Tampilkan Nama Donatur
atau
Tampilkan Donatur Anonim

### 7.11 Flow Room Assignment

Buat Kamar
↓
Tentukan Kapasitas
↓
Tempatkan Alumni
↓
Publikasikan Rooming List

### 7.12 Flow Peta Alumni

Input Kota dan Negara
↓
Geocoding Otomatis
↓
Verifikasi Admin
↓
Simpan Koordinat
↓
Tampilkan Pada Peta

### 7.13 Flow Timeline Alumni

Input Riwayat Lokasi
↓
Validasi Data
↓
Simpan Timeline
↓
Tampilkan Pada Profil
↓
Tampilkan Pada Visualisasi Timeline

### 7.14 Flow WhatsApp Analytics

Upload Chat WhatsApp
↓
Parsing File
↓
Normalisasi Nama Alumni
↓
Proses Statistik
↓
Generate Ranking
↓
Generate Topik
↓
Generate Word Cloud
↓
Publikasi Analytics

### 7.15 Lifecycle Sistem

Fase 1
Persiapan Reuni

Fase 2
Pelaksanaan Reuni

Fase 3
Dokumentasi dan Publikasi

Fase 4
Arsip Digital Permanen

Sistem dirancang untuk tetap aktif dan relevan pada seluruh fase tersebut.

### 7.16 Prinsip User Experience

Sistem dirancang dengan prinsip:

1. Mobile First.
2. Login sederhana menggunakan WhatsApp.
3. Maksimal tiga klik menuju fitur utama.
4. Alumni sebagai pusat navigasi.
5. Dokumentasi sebagai aset utama pasca reuni.
6. Konsistensi tampilan antar perangkat.
7. Kemudahan eksplorasi profil alumni.
8. Kemudahan eksplorasi dokumentasi.
9. Kemudahan eksplorasi nostalgia dan sejarah angkatan.

## BAB 8 - UI/UX SPECIFICATION & WIREFRAME DEFINITION

### 8.1 Pendahuluan

Bab ini mendefinisikan kebutuhan antarmuka pengguna (User Interface) dan pengalaman pengguna (User Experience) untuk Sistem Manajemen Reuni Alumni Teknik Geodesi UGM Angkatan 1996.

Tujuan penyusunan bab ini adalah:

1. Menjadi acuan tim UI/UX Designer.
2. Menjadi acuan tim Frontend Developer.
3. Menjaga konsistensi tampilan sistem.
4. Menentukan komponen yang wajib tersedia pada setiap halaman.
5. Mendukung penggunaan pada perangkat desktop dan mobile.

### 8.2 Prinsip Desain Antarmuka

Sistem menggunakan prinsip:

#### Sederhana

Alumni tidak semuanya berasal dari latar belakang teknologi sehingga antarmuka harus mudah dipahami.

#### Mobile First

Mayoritas pengguna diperkirakan mengakses sistem melalui smartphone.

#### Nostalgia Oriented

Fokus utama sistem adalah membangun kembali memori dan kebersamaan alumni.

Elemen visual dapat memanfaatkan:

* Foto lama
* Arsip kegiatan kuliah
* Dokumentasi reuni
* Timeline perjalanan alumni

#### Community Driven

Profil alumni dan dokumentasi menjadi pusat interaksi.

#### Long-Term Archive

Website tidak hanya digunakan saat reuni tetapi juga sebagai arsip digital jangka panjang.

### 8.3 Layout Global

#### Public Layout

Header
↓
Navigation
↓
Content
↓
Footer

#### Alumni Layout

Top Navigation Bar
↓
Page Content
↓
Footer

Pada desktop tidak diperlukan sidebar permanen.

Menu utama diletakkan pada top navigation agar lebih nyaman di perangkat mobile.

#### Admin Layout

Sidebar
+
Top Navigation
+
Content Area

Karena administrator bekerja dengan data yang lebih kompleks.

### 8.4 Public Website

#### Halaman Home

##### Tujuan

Menjadi pintu masuk utama website reuni.

##### Komponen

###### Hero Section

Menampilkan:

* Logo reuni
* Tema reuni
* Tanggal kegiatan
* Tombol Login

###### Countdown Section

Menampilkan:

* Hari
* Jam
* Menit
* Detik

Menuju tanggal reuni.

Setelah reuni selesai:

Countdown otomatis berubah menjadi:

> "30 Tahun Persahabatan Geodesi 96"

###### Statistik Alumni

Menampilkan:

* Total alumni
* Total hadir
* Total negara
* Total kota

###### Highlight Dokumentasi

Menampilkan:

* Foto pilihan
* Video pilihan

###### Berita Terbaru

Menampilkan 3–5 berita terbaru.

###### Footer

Menampilkan:

* Copyright
* Kontak panitia
* Social media

### 8.5 Halaman Login

#### Layout

Logo Reuni

↓

Form Login

↓

Bantuan Login

#### Komponen

Field:

* Nomor WhatsApp
* Password

Tombol:

* Login

Link:

* Lupa Password

### 8.6 Dashboard Alumni

#### Tujuan

Memberikan ringkasan informasi yang paling penting.

#### Komponen Utama

##### Welcome Card

Menampilkan:

* Nama alumni
* Nama panggilan
* Foto profil

##### Status Card

Menampilkan:

* Status RSVP
* Status pembayaran
* Status kamar

##### Berita Terbaru

Card berita terbaru.

##### Dokumentasi Terbaru

Foto dan video terbaru.

##### Hall of Fame Mini

Menampilkan:

* Active Member
* Nocturnal Chatter
* Emoji Champion

##### Alumni Map Summary

Mini map persebaran alumni.

### 8.7 Profil Saya

#### Tab Informasi Dasar

Field:

* Nama lengkap
* Nama panggilan
* WhatsApp
* Email
* Kota
* Negara
* Instansi
* Jabatan

#### Tab Foto Masa Kuliah

Menampilkan:

* Foto utama

Fitur:

* Upload
* Ganti

#### Tab Foto Saat Ini

Menampilkan:

* Foto utama

Fitur:

* Upload
* Ganti

#### Tab Cerita Alumni

Editor teks.

#### Tab Kenangan Alumni

Editor teks.

#### Tab Pesan Alumni

Editor teks.

#### Tab Timeline Lokasi

Tabel timeline.

Fitur:

* Tambah
* Edit
* Hapus

### 8.8 Direktori Alumni

#### Layout

Search Bar
↓
Filter
↓
Grid Alumni

#### Filter

* Nama
* Kota
* Negara
* Status Alumni

#### Alumni Card

Menampilkan:

* Foto
* Nama
* Nama panggilan
* Kota
* Negara

Tombol:

* Lihat Profil

### 8.9 Detail Profil Alumni

#### Header Profile

Menampilkan:

* Foto masa kuliah
* Foto saat ini
* Nama
* Nama panggilan

#### Informasi Alumni

* Kota
* Negara
* Instansi
* Jabatan

#### Cerita Alumni

#### Kenangan Alumni

#### Pesan Alumni

#### Timeline Alumni

Visual timeline.

#### Galeri Uploaded

Foto dan video yang diunggah oleh alumni.

#### Galeri Tagged

Foto dan video yang menandai alumni.

### 8.10 Memorial Profile

Tampilan serupa dengan profil alumni biasa.

Tambahan:

#### Memorial Banner

Menampilkan:

> In Memoriam

#### Informasi Memorial

* Tahun lahir (jika tersedia)
* Tahun wafat (jika tersedia)

### 8.11 RSVP Page

#### Komponen

Status saat ini.

Pilihan:

○ Hadir

○ Tidak Hadir

Tombol:

* Simpan

### 8.12 Payment Page

#### Komponen

Status pembayaran:

* Belum Bayar
* Menunggu Verifikasi
* Lunas

#### Riwayat Verifikasi

Menampilkan:

* Tanggal verifikasi
* Catatan bendahara

### 8.13 Room Information Page

#### Komponen

Card Kamar

Menampilkan:

* Nama kamar
* Kapasitas
* Penghuni kamar

### 8.14 Documentation Gallery

#### Layout

Search
↓
Filter
↓
Gallery Grid

#### Filter

* Tahun
* Foto
* Video
* Internal
* Publik

#### Gallery Card

Foto thumbnail.

Menampilkan:

* Tahun
* Uploader

#### Detail Gallery

Menampilkan:

* Foto ukuran besar
* Deskripsi
* Uploader
* Alumni yang ditandai

### 8.15 Upload Documentation

#### Form Foto

Field:

* Judul
* Deskripsi
* Foto
* Bulan
* Tahun
* Visibility
* Tag Alumni

#### Form Video

Field:

* Judul
* Deskripsi
* URL
* Bulan
* Tahun
* Visibility
* Tag Alumni

### 8.16 Digital Memory Book

#### Layout

Grid Alumni

↓

Cerita

↓

Kenangan

↓

Pesan

Tujuan:

Menciptakan pengalaman membaca buku kenangan digital.

### 8.17 Alumni Map

#### Komponen

Interactive World Map

#### Marker

Menampilkan:

* Kota
* Negara
* Jumlah alumni

#### Klik Marker

Menampilkan:

* Daftar alumni pada lokasi tersebut

#### Statistik

Menampilkan:

* Top Kota
* Top Negara

### 8.18 Timeline Explorer

#### Layout

Filter Alumni
↓
Timeline Visualization

Menampilkan:

* Tahun
* Kota
* Negara

dalam bentuk garis waktu.

### 8.19 WhatsApp Analytics

#### Dashboard Analytics

Menampilkan:

##### Hall of Fame

* Top Active Member
* Silent Reader
* Link Poster
* Image Poster
* Nocturnal Chatter
* Work Time Chatter
* Weekend Warrior
* Emoji Champion

##### Activity Statistics

* Tahun teramai
* Bulan teramai
* Jam teramai

##### Top Topics

Top 10 topik.

##### Nostalgia Word Cloud

Visualisasi kata populer.

### 8.20 Dashboard Administrator

#### KPI Cards

* Total Alumni
* RSVP Hadir
* RSVP Tidak Hadir
* Dokumentasi
* Kamar

#### Quick Actions

* Tambah Alumni
* Import Alumni
* Kelola Kamar
* Kelola Berita

### 8.21 Dashboard Bendahara

#### KPI Cards

* Lunas
* Menunggu Verifikasi
* Donatur

#### Tabel Pembayaran

Menampilkan:

* Nama alumni
* Status pembayaran
* Verifikasi

#### Tabel Donasi

Menampilkan:

* Nama donor
* Status publikasi

### 8.22 Dashboard Superadmin

#### KPI Cards

* User
* Alumni
* Dokumentasi
* Login Hari Ini

#### Monitoring

* Audit Log
* Storage Usage
* WhatsApp Analytics Jobs

### 8.23 Responsive Design

#### Desktop

* Multi-column layout.
* Statistik ditampilkan penuh.

#### Tablet

* Grid 2 kolom.

#### Mobile

* Single column layout.
* Hamburger menu.
* Prioritas informasi utama.

### 8.24 Design System

#### Warna

Mengikuti identitas visual reuni yang akan ditentukan kemudian.

#### Font

Rekomendasi:

* Inter
* Poppins
* Nunito

#### Komponen UI

Komponen standar yang harus tersedia:

* Card
* Table
* Badge
* Modal
* Drawer
* Toast Notification
* Search Box
* Pagination
* Filter Panel
* Timeline Component
* Map Component
* Gallery Component

### 8.25 Target User Experience

Target pengalaman pengguna:

1. Login kurang dari 30 detik.
2. Mengisi profil kurang dari 5 menit.
3. Mengisi RSVP kurang dari 1 menit.
4. Upload dokumentasi kurang dari 2 menit.
5. Menemukan alumni lain kurang dari 3 klik.
6. Menemukan dokumentasi terkait alumni kurang dari 3 klik.
7. Mengakses informasi penting langsung dari dashboard.

## BAB 9 - SECURITY, PRIVACY, BACKUP & DATA GOVERNANCE

### 9.1 Pendahuluan

Bab ini menjelaskan kebijakan keamanan, privasi, backup, dan tata kelola data yang diterapkan pada Sistem Manajemen Reuni Alumni Teknik Geodesi UGM Angkatan 1996.

Tujuan utama bab ini adalah:

1. Melindungi data pribadi alumni.
2. Menjaga integritas dan ketersediaan data.
3. Menjamin keberlangsungan arsip digital jangka panjang.
4. Menetapkan tanggung jawab pengelolaan data.
5. Menjadi pedoman operasional setelah sistem digunakan.

### 9.2 Prinsip Keamanan Sistem

Sistem dibangun berdasarkan prinsip:

#### Confidentiality

Data hanya dapat diakses oleh pihak yang memiliki hak akses.

#### Integrity

Data tidak dapat diubah oleh pihak yang tidak berwenang.

#### Availability

Data tetap tersedia dan dapat diakses ketika dibutuhkan.

#### Accountability

Seluruh aktivitas penting dapat ditelusuri melalui audit log.

#### Least Privilege

Setiap pengguna hanya memperoleh akses yang diperlukan sesuai perannya.

### 9.3 Autentikasi dan Akses Sistem

#### Login

Sistem menggunakan:

* Nomor WhatsApp
* Password

sebagai mekanisme autentikasi.

#### Password

Password wajib:

* Disimpan dalam bentuk hash.
* Tidak pernah disimpan dalam bentuk plaintext.
* Tidak ditampilkan kepada administrator.

Rekomendasi:

* Laravel Bcrypt
* Laravel Argon2

#### Session Management

Session login:

* Menggunakan session Laravel.
* Berakhir otomatis setelah periode tidak aktif.
* Dapat diakhiri melalui logout.

#### Role Based Access Control

Akses sistem dibatasi berdasarkan role:

* Superadmin
* Administrator
* Bendahara
* Alumni

### 9.4 Kebijakan Data Alumni

#### Kepemilikan Data

Data profil alumni merupakan bagian dari arsip komunitas Alumni Teknik Geodesi UGM Angkatan 1996.

Setiap alumni memiliki hak untuk:

* Melihat profilnya sendiri.
* Memperbarui profilnya sendiri.
* Mengoreksi data yang tidak akurat.

#### Data yang Ditampilkan

Data berikut dapat dilihat oleh seluruh alumni yang telah login:

* Nama lengkap
* Nama panggilan
* Nomor WhatsApp
* Email
* Kota
* Negara
* Perusahaan
* Jabatan
* Cerita alumni
* Kenangan alumni
* Pesan alumni
* Dokumentasi yang terkait

#### Alumni Meninggal

Data alumni yang telah meninggal:

* Tetap dipertahankan.
* Tetap ditampilkan dalam direktori.
* Tetap ditampilkan dalam buku kenangan.
* Tetap ditampilkan pada peta alumni.
* Tetap ditampilkan pada timeline alumni.

Profil alumni yang telah meninggal ditampilkan sebagai halaman memorial.

### 9.5 Kebijakan Dokumentasi

#### Kepemilikan Dokumentasi

Setiap foto dan video memiliki informasi:

* Uploader
* Tanggal upload
* Riwayat perubahan

#### Hak Uploader

Uploader dapat:

* Mengubah dokumentasi miliknya.
* Melakukan soft delete dokumentasi miliknya.

#### Hak Administrator

Administrator dapat:

* Mengelola seluruh dokumentasi.
* Mengubah status publik/internal.
* Melakukan restore dokumentasi yang dihapus.

#### Hak Superadmin

Superadmin dapat:

* Menghapus permanen dokumentasi.
* Memulihkan dokumentasi.
* Melakukan audit dokumentasi.

#### Status Visibilitas

Dokumentasi memiliki dua tingkat visibilitas:

##### Internal

Hanya dapat dilihat oleh alumni yang login.

##### Publik

Dapat ditampilkan pada website publik.

#### Tagging Alumni

Foto dan video dapat menandai alumni lain.

Tagging digunakan untuk:

* Galeri Uploaded
* Galeri Tagged
* Profil Alumni
* Memorial Alumni

### 9.6 Kebijakan WhatsApp Analytics

#### Sumber Data

Data diperoleh dari file ekspor grup WhatsApp Alumni Geodesi 96.

#### Transparansi

Seluruh anggota grup diberitahukan bahwa:

* Data percakapan dianalisis.
* Analisis digunakan untuk keperluan nostalgia dan statistik komunitas.

#### Pembatasan

Sistem tidak menampilkan:

* Isi percakapan lengkap.
* Riwayat chat per pengguna.
* Kutipan pesan individu.

#### Data yang Ditampilkan

Sistem hanya menampilkan:

* Statistik agregat
* Ranking
* Topik populer
* Word cloud
* Statistik aktivitas grup

#### Alumni Meninggal

Alumni dengan status meninggal:

Tidak dimasukkan ke dalam:

* Hall of Fame
* Ranking individu

Namun tetap dihitung pada statistik historis grup.

### 9.7 Audit Log

Sistem wajib mencatat aktivitas penting.

Contoh:

* Login
* Logout
* Ubah password
* Ubah profil
* Upload foto
* Upload video
* Hapus dokumentasi
* Verifikasi pembayaran
* Perubahan donasi

#### Tujuan Audit Log

* Investigasi masalah.
* Pelacakan perubahan data.
* Keamanan sistem.
* Monitoring aktivitas administrator.

### 9.8 Backup Policy

#### Tujuan

Menjamin data tetap dapat dipulihkan apabila terjadi:

* Kerusakan server.
* Kesalahan pengguna.
* Kegagalan perangkat keras.
* Kehilangan data.

#### Data yang Dibackup

##### Database

Seluruh database sistem.

##### Dokumentasi

* Foto profil
* Foto dokumentasi

##### Konfigurasi Sistem

* File konfigurasi
* Environment configuration
* Storage metadata

### 9.9 Jadwal Backup

#### Backup Harian

Dilakukan setiap hari.

Mencakup:

* Database

Retensi:

30 hari.

#### Backup Mingguan

Dilakukan setiap minggu.

Mencakup:

* Database
* Dokumentasi

Retensi:

12 minggu.

#### Backup Bulanan

Dilakukan setiap bulan.

Mencakup:

* Database
* Dokumentasi
* Arsip lengkap sistem

Retensi:

Permanen.

### 9.10 Lokasi Backup

Disarankan menggunakan minimal dua lokasi:

#### Primary Backup

Server utama.

#### Secondary Backup

Penyimpanan terpisah:

* Cloud Storage
* NAS
* External Storage

#### Prinsip

Backup tidak boleh hanya berada pada server yang sama dengan aplikasi.

### 9.11 Disaster Recovery

#### Tujuan

Memulihkan layanan apabila terjadi kegagalan sistem.

#### Target Recovery

##### Database

Recovery maksimal:

24 jam.

##### Dokumentasi

Recovery maksimal:

24 jam.

##### Website

Recovery maksimal:

48 jam.

### 9.12 Kebijakan Retensi Data

#### Data Alumni

Disimpan permanen.

#### Dokumentasi

Disimpan permanen.

#### Buku Kenangan

Disimpan permanen.

#### Timeline Alumni

Disimpan permanen.

#### WhatsApp Analytics

Disimpan permanen.

#### Audit Log

Disimpan minimal:

5 tahun.

Disarankan permanen.

### 9.13 Data Governance

#### Data Owner

Komunitas Alumni Teknik Geodesi UGM Angkatan 1996.

#### Data Steward

Administrator yang ditunjuk panitia reuni.

#### System Administrator

Superadmin sistem.

#### Financial Data Custodian

Bendahara.

### 9.14 Pengelolaan Akun

#### Akun Alumni

Dapat:

* Aktif
* Nonaktif

#### Alumni Meninggal

Akun login dinonaktifkan.

Profil memorial tetap tersedia.

#### Penghapusan Akun

Akun alumni tidak dihapus secara permanen.

Status akun diubah menjadi nonaktif.

### 9.15 Keamanan File Upload

#### Foto

Sistem wajib:

* Memvalidasi tipe file.
* Memvalidasi ukuran file.
* Melakukan resize.
* Melakukan kompresi.

#### Ekstensi yang Diizinkan

* JPG
* JPEG
* PNG
* WEBP

#### Video

Video tidak diunggah ke server.

Sistem hanya menyimpan URL video.

### 9.16 Keamanan Aplikasi

Rekomendasi implementasi Laravel:

#### CSRF Protection

Aktif pada seluruh form.

#### XSS Protection

Seluruh input pengguna harus disanitasi.

#### SQL Injection Protection

Menggunakan Eloquent ORM atau Query Builder.

#### Password Hashing

Menggunakan mekanisme bawaan Laravel.

#### File Access Protection

File privat tidak boleh dapat diakses langsung tanpa autentikasi.

### 9.17 Monitoring dan Maintenance

#### Monitoring

Meliputi:

* Kesehatan server
* Kapasitas storage
* Aktivitas login
* Error aplikasi

#### Maintenance

Dilakukan secara berkala untuk:

* Update framework
* Update dependency
* Optimasi database
* Verifikasi backup

### 9.18 Prinsip Keberlanjutan Sistem

Sistem dirancang sebagai:

1. Portal reuni.
2. Direktori alumni.
3. Buku kenangan digital.
4. Arsip dokumentasi.
5. Arsip sejarah angkatan.

Sehingga sistem tetap relevan dan dapat digunakan bertahun-tahun setelah reuni selesai.

## BAB 10 - REPORTING, ANALYTICS & DASHBOARD SPECIFICATION

### 10.1 Pendahuluan

Bab ini menjelaskan kebutuhan dashboard, laporan, statistik, dan analisis yang tersedia pada Sistem Manajemen Reuni Alumni Teknik Geodesi UGM Angkatan 1996.

Tujuan utama modul reporting dan analytics adalah:

1. Mendukung pengambilan keputusan panitia.
2. Memantau kesiapan reuni.
3. Memantau partisipasi alumni.
4. Menyajikan informasi komunitas alumni secara menarik.
5. Menjadi sarana nostalgia dan arsip digital jangka panjang.

### 10.2 Klasifikasi Dashboard

Sistem menyediakan empat kelompok dashboard utama:

1. Dashboard Alumni
2. Dashboard Administrator
3. Dashboard Bendahara
4. Dashboard Superadmin

### 10.3 Dashboard Alumni

Dashboard Alumni merupakan halaman utama setelah login.

Tujuan:

* Menampilkan informasi pribadi yang paling relevan.
* Menjadi pintu masuk menuju seluruh fitur sistem.

#### KPI Card Alumni

Menampilkan:

##### Status Kehadiran

Nilai:

* Belum Merespon
* Hadir
* Tidak Hadir

##### Status Pembayaran

Nilai:

* Belum Bayar
* Menunggu Verifikasi
* Lunas

##### Informasi Kamar

Nilai:

* Nama kamar
* Jumlah penghuni

##### Profil Alumni

Nilai:

* Persentase kelengkapan profil

#### Informasi Terbaru

Menampilkan:

* Berita terbaru
* Pengumuman terbaru

#### Dokumentasi Terbaru

Menampilkan:

* Foto terbaru
* Video terbaru

#### Hall of Fame Ringkas

Menampilkan:

* Active Member
* Nocturnal Chatter
* Emoji Champion

### 10.4 Dashboard Administrator

Dashboard Administrator digunakan untuk memantau kesiapan reuni.

#### KPI Alumni

Menampilkan:

* Total alumni
* Alumni aktif
* Alumni meninggal

#### KPI RSVP

Menampilkan:

* Belum merespon
* Hadir
* Tidak hadir

#### KPI Dokumentasi

Menampilkan:

* Total foto
* Total video
* Dokumentasi publik
* Dokumentasi internal

#### KPI Rooming

Menampilkan:

* Total kamar
* Kamar terisi
* Kamar belum penuh

#### KPI Berita

Menampilkan:

* Total berita
* Draft
* Published

### 10.5 Dashboard Bendahara

Dashboard Bendahara digunakan untuk memantau kondisi keuangan reuni.

#### KPI Pembayaran

Menampilkan:

* Belum bayar
* Menunggu verifikasi
* Lunas

#### KPI Donasi

Menampilkan:

* Total donatur
* Donatur anonim
* Donatur publik

#### KPI Keuangan

Menampilkan:

* Total pembayaran diterima
* Total donasi diterima
* Total dana terkumpul

Catatan:

Nominal hanya dapat dilihat oleh Bendahara dan Superadmin.

#### Monitoring Pembayaran

Tabel:

* Nama alumni
* Status pembayaran
* Tanggal pembayaran
* Tanggal verifikasi

#### Monitoring Donasi

Tabel:

* Nama donor
* Status publikasi donor
* Tanggal donasi

### 10.6 Dashboard Superadmin

Dashboard Superadmin digunakan untuk monitoring keseluruhan sistem.

#### KPI Sistem

Menampilkan:

* Total user
* Total alumni
* Total dokumentasi
* Total berita

#### KPI Aktivitas

Menampilkan:

* Login hari ini
* Upload dokumentasi hari ini
* Perubahan profil hari ini

#### KPI Infrastruktur

Menampilkan:

* Kapasitas storage
* Database size
* Backup terakhir
* Status sistem

#### Audit Summary

Menampilkan:

* Aktivitas administrator
* Aktivitas bendahara
* Aktivitas alumni

### 10.7 Laporan Alumni

#### Laporan Daftar Alumni

Menampilkan:

* Nama
* Panggilan
* Kota
* Negara
* Perusahaan
* Jabatan

Format:

* Excel
* PDF

#### Laporan Alumni Aktif

Menampilkan:

Alumni dengan akun aktif.

#### Laporan Memorial Alumni

Menampilkan:

Alumni yang telah meninggal.

#### Laporan Persebaran Alumni

Menampilkan:

* Berdasarkan kota
* Berdasarkan negara

#### Laporan Timeline Alumni

Menampilkan:

Riwayat lokasi alumni.

### 10.8 Laporan RSVP

#### Rekap RSVP

Menampilkan:

* Hadir
* Tidak hadir
* Belum merespon

#### Daftar Peserta Hadir

Menampilkan:

* Nama alumni
* Kota
* Negara

#### Daftar Peserta Tidak Hadir

Menampilkan:

* Nama alumni
* Kota
* Negara

#### Statistik Kehadiran

Grafik:

* Pie Chart Kehadiran
* Trend RSVP

### 10.9 Laporan Pembayaran

#### Rekap Pembayaran

Menampilkan:

* Belum bayar
* Menunggu verifikasi
* Lunas

#### Daftar Pembayaran

Menampilkan:

* Nama alumni
* Status pembayaran
* Tanggal pembayaran

#### Statistik Pembayaran

Grafik:

* Progress pembayaran
* Persentase pelunasan

### 10.10 Laporan Donasi

#### Rekap Donasi

Menampilkan:

* Jumlah donor
* Donor anonim
* Donor publik

#### Daftar Donatur

Menampilkan:

* Nama donor
* Status publikasi

#### Statistik Donasi

Grafik:

* Jumlah donor dari waktu ke waktu

### 10.11 Laporan Rooming

#### Rooming List

Menampilkan:

* Nama kamar
* Kapasitas
* Penghuni

#### Statistik Penginapan

Menampilkan:

* Jumlah kamar
* Tingkat okupansi

### 10.12 Laporan Dokumentasi

#### Statistik Dokumentasi

Menampilkan:

* Total foto
* Total video

#### Statistik Berdasarkan Tahun

Menampilkan:

Jumlah dokumentasi per tahun.

#### Statistik Berdasarkan Uploader

Menampilkan:

Top uploader dokumentasi.

#### Statistik Tag Alumni

Menampilkan:

Alumni yang paling banyak muncul dalam dokumentasi.

### 10.13 Analytics Peta Alumni

#### Statistik Negara

Menampilkan:

* Jumlah negara
* Jumlah alumni per negara

#### Statistik Kota

Menampilkan:

* Jumlah kota
* Jumlah alumni per kota

#### Top Negara

Menampilkan:

Negara dengan alumni terbanyak.

#### Top Kota

Menampilkan:

Kota dengan alumni terbanyak.

### 10.14 Analytics Timeline Alumni

#### Statistik Mobilitas Alumni

Menampilkan:

* Jumlah lokasi unik
* Jumlah negara unik
* Jumlah kota unik

#### Kota Favorit Alumni

Menampilkan:

Kota yang paling banyak menjadi tempat tinggal alumni.

#### Negara Favorit Alumni

Menampilkan:

Negara yang paling banyak menjadi tempat tinggal alumni.

### 10.15 WhatsApp Analytics Dashboard

Merupakan fitur analitik khusus komunitas alumni.

Hanya dapat diakses oleh alumni yang login.

### 10.16 Hall of Fame

Menampilkan:

#### Top 5 Active Member

Alumni dengan jumlah pesan terbanyak.

#### Top 5 Silent Reader

Alumni dengan aktivitas paling rendah.

#### Top 5 Link Poster

Alumni yang paling banyak membagikan tautan.

#### Top 5 Image Poster

Alumni yang paling banyak membagikan gambar.

#### Top 5 Nocturnal Chatter

Alumni yang paling aktif pada malam hari.

#### Top 5 Work Time Chatter

Alumni yang paling aktif pada jam kerja.

#### Top 5 Weekend Warrior

Alumni yang paling aktif pada akhir pekan.

#### Top 5 Emoji Champion

Alumni yang paling banyak menggunakan emoji.

### 10.17 Statistik Aktivitas Grup

Menampilkan:

#### Tahun Paling Ramai

Peringkat aktivitas per tahun.

#### Bulan Paling Ramai

Peringkat aktivitas per bulan.

#### Hari Paling Ramai

Peringkat aktivitas per hari.

#### Jam Paling Ramai

Peringkat aktivitas per jam.

### 10.18 Topik Populer

Menampilkan:

Top 10 topik yang paling sering dibahas.

Contoh:

* Reuni
* Geodesi
* Keluarga
* Pekerjaan
* UGM
* Jogja

Daftar aktual dihasilkan dari analisis data grup.

### 10.19 Nostalgia Word Cloud

Visualisasi:

* Kata paling sering digunakan
* Istilah khas angkatan
* Nama panggilan populer
* Istilah nostalgia

### 10.20 Insight Historis

Menampilkan informasi menarik seperti:

* Tahun paling aktif dalam grup
* Periode paling sepi
* Pertumbuhan anggota grup
* Momen reuni yang memicu lonjakan percakapan

### 10.21 Export Report

Laporan yang dapat diekspor:

#### Excel

* Alumni
* RSVP
* Pembayaran
* Donasi
* Rooming

#### PDF

* Alumni
* RSVP
* Rooming
* Dokumentasi
* Statistik

### 10.22 KPI Operasional Reuni

KPI utama yang dipantau panitia:

#### KPI Kehadiran

Jumlah alumni hadir dibanding target.

#### KPI Pembayaran

Jumlah alumni lunas dibanding total alumni.

#### KPI Donasi

Jumlah donor dibanding target donor.

#### KPI Profil Alumni

Persentase alumni yang telah melengkapi profil.

#### KPI Dokumentasi

Jumlah foto dan video yang berhasil dikumpulkan.

#### KPI Keterlibatan Alumni

Jumlah alumni yang:

* Login
* Mengisi profil
* Mengunggah dokumentasi
* Berpartisipasi pada sistem

### 10.23 Prinsip Analytics

Seluruh dashboard dan laporan dirancang berdasarkan prinsip:

1. Mudah dipahami.
2. Relevan dengan kebutuhan panitia.
3. Mendukung nostalgia dan kebersamaan alumni.
4. Tidak menampilkan data sensitif secara publik.
5. Tetap bermanfaat setelah reuni selesai.
6. Menjadi bagian dari arsip digital Geodesi 96.

## BAB 11 - DEPLOYMENT ARCHITECTURE & TECHNICAL INFRASTRUCTURE

### 11.1 Pendahuluan

Bab ini menjelaskan arsitektur teknis, infrastruktur deployment, kebutuhan server, storage, backup, dan komponen pendukung yang digunakan oleh Sistem Manajemen Reuni Alumni Teknik Geodesi UGM Angkatan 1996.

Tujuan utama bab ini adalah:

1. Menentukan kebutuhan infrastruktur sistem.
2. Menentukan standar deployment.
3. Menentukan strategi backup dan recovery.
4. Menjamin keberlanjutan sistem jangka panjang.
5. Menjadi acuan implementasi DevOps.

### 11.2 Arsitektur Sistem Tingkat Tinggi

Sistem menggunakan arsitektur web berbasis Laravel.

Struktur umum:

User Browser
↓
HTTPS / SSL
↓
Web Server
↓
Laravel Application
↓
Database Server
↓
File Storage

#### Komponen Utama

1. Frontend Website
2. Laravel Application
3. Database Server
4. File Storage
5. Backup Storage

### 11.3 Arsitektur Logis

Client Browser
↓
Nginx
↓
Laravel
↓
MySQL / MariaDB
↓
Storage

Laravel Scheduler
↓
Background Jobs
↓
Analytics & Maintenance

### 11.4 Teknologi Utama

#### Backend

Framework:

Laravel 13.x

Bahasa:

PHP 8.3

#### Frontend

Rekomendasi:

* Blade Template
* Livewire 4
* Flux UI 2
* Tailwind CSS 4

Alternatif:

* Alpine.js untuk interaksi ringan di sisi client

Untuk proyek ini Blade + Livewire + Flux UI + Tailwind CSS digunakan sebagai stack frontend utama.

#### Database

Rekomendasi utama:

MySQL 8.x

Alternatif:

MariaDB 11+

#### Web Server

Rekomendasi:

Nginx

Alternatif:

Apache

#### Operating System

Rekomendasi:

Ubuntu Server LTS

### 11.5 Infrastruktur Hosting

#### Lingkungan Pengembangan

Development Environment

Digunakan oleh programmer.

Komponen:

* Laravel
* MySQL
* Git
* Local Storage

#### Lingkungan Staging

Digunakan untuk pengujian sebelum produksi.

Komponen:

* Salinan database
* Salinan aplikasi
* Data uji

#### Lingkungan Produksi

Digunakan oleh pengguna akhir.

Komponen:

* Domain resmi
* SSL
* Database produksi
* Storage produksi

### 11.6 Kebutuhan Server Awal

Perkiraan jumlah alumni:

± 100 orang

Perkiraan pengguna aktif:

50–150 pengguna

Perkiraan dokumentasi:

5.000–20.000 foto

#### Spesifikasi Minimum

CPU:

2 Core

RAM:

4 GB

Storage:

100 GB SSD

#### Spesifikasi Rekomendasi

CPU:

4 Core

RAM:

8 GB

Storage:

250 GB SSD

### 11.7 Penyimpanan Dokumentasi

#### Foto

Disimpan pada server.

#### Strategi Penyimpanan

Saat upload:

1. Validasi file.
2. Resize otomatis.
3. Kompresi otomatis.
4. Simpan versi web.

#### Tidak Menyimpan

* RAW
* HD Original

Tujuan:

Menghemat storage.

#### Struktur Direktori

storage/app/public/

├── profile/
│   ├── college/
│   └── current/
│
├── photos/
│   ├── 1996/
│   ├── 1997/
│   └── ...
│
├── news/
│
└── temporary/

### 11.8 Penyimpanan Video

Video tidak disimpan pada server.

Video hanya berupa:

* YouTube URL
* Google Drive URL

Keuntungan:

* Hemat storage
* Hemat bandwidth
* Hemat backup

### 11.9 Domain dan DNS

#### Domain Utama

Rekomendasi:

reunigeodesi96.id

atau

geodesi96.id

atau

kembaliketitiknol.id

Nama final ditentukan panitia.

#### DNS

Minimal:

A Record
AAAA Record (jika tersedia)
MX Record
TXT Record

### 11.10 SSL dan HTTPS

Seluruh website wajib menggunakan HTTPS.

Rekomendasi:

Let's Encrypt

#### Keuntungan

* Data terenkripsi.
* Login lebih aman.
* SEO lebih baik.
* Browser tidak menampilkan peringatan keamanan.

### 11.11 Email Sistem

Email digunakan untuk:

* Reset password
* Notifikasi sistem
* Notifikasi administrator

#### Rekomendasi

SMTP:

* Google Workspace
* Microsoft 365
* Mailgun
* Amazon SES

### 11.12 Scheduler Laravel

Laravel Scheduler digunakan untuk:

#### Daily Tasks

* Backup database
* Membersihkan file temporary
* Generate statistik harian

#### Weekly Tasks

* Backup dokumentasi
* Verifikasi integritas data

#### Monthly Tasks

* Arsip backup bulanan

### 11.13 Queue System

Queue digunakan untuk proses berat.

#### Proses Queue

##### Resize Foto

Upload
↓
Queue
↓
Resize
↓
Kompresi
↓
Simpan

##### WhatsApp Analytics

Import File
↓
Queue
↓
Parsing
↓
Analisis
↓
Simpan Statistik

##### Backup

Backup berjalan melalui queue agar tidak mengganggu pengguna.

#### Rekomendasi Driver

Database Queue

Sudah cukup untuk kebutuhan sistem.

### 11.14 Infrastruktur Peta Alumni

#### Library Peta

Rekomendasi:

Leaflet

#### Basemap

Rekomendasi:

OpenStreetMap

#### Data Lokasi

Sumber:

* City
* Country
* Timeline

#### Visualisasi

* Marker
* Cluster Marker
* Heatmap (opsional)

### 11.15 Geocoding

Digunakan untuk memperoleh koordinat kota.

#### Metode

##### Otomatis

Berdasarkan:

* Kota
* Negara

##### Manual

Administrator dapat memperbaiki koordinat.

#### Rekomendasi

Nominatim OpenStreetMap

### 11.16 Infrastruktur WhatsApp Analytics

#### Input

File text hasil export WhatsApp.

#### Proses

Upload File
↓
Parsing
↓
Normalisasi Nama
↓
Pembersihan Data
↓
Analisis
↓
Penyimpanan Statistik

#### Output

* Ranking
* Statistik
* Topik
* Word Cloud

### 11.17 Monitoring Sistem

#### Monitoring Server

Memantau:

* CPU
* RAM
* Storage

#### Monitoring Aplikasi

Memantau:

* Error Laravel
* Queue
* Login gagal
* Kinerja aplikasi

#### Monitoring Backup

Memantau:

* Backup harian
* Backup mingguan
* Backup bulanan

### 11.18 Logging

#### Laravel Log

Digunakan untuk:

* Error aplikasi
* Debugging

#### Audit Log

Digunakan untuk:

* Aktivitas pengguna
* Aktivitas administrator
* Aktivitas bendahara

### 11.19 Backup Architecture

#### Backup Database

Harian

Retensi:

30 hari

#### Backup Dokumentasi

Mingguan

Retensi:

12 minggu

#### Backup Arsip Lengkap

Bulanan

Retensi:

Permanen

#### Prinsip

Backup harus tersimpan pada lokasi yang berbeda dari server utama.

### 11.20 Disaster Recovery

#### Recovery Database

Target:

≤ 24 jam

#### Recovery Dokumentasi

Target:

≤ 24 jam

#### Recovery Website

Target:

≤ 48 jam

### 11.21 Git Repository

#### Version Control

Menggunakan Git.

#### Branch

main

production

develop

feature/*

#### Repository

Private Repository.

### 11.22 Deployment Strategy

#### Deployment

Developer
↓
Git Repository
↓
Staging
↓
Testing
↓
Production

#### Approval

Deployment ke production dilakukan setelah:

* Functional test
* Security test
* UAT

selesai dilaksanakan.

### 11.23 Teknologi Pendukung yang Direkomendasikan

#### Backend

* Laravel 13
* PHP 8.3

#### Frontend

* Blade
* Livewire 4
* Flux UI 2
* Tailwind CSS 4

#### Database

* MySQL

#### Map

* Leaflet
* OpenStreetMap

#### Analytics

* Laravel Queue
* Laravel Scheduler

#### Storage

* Local Storage
* NAS Backup
* Cloud Backup

### 11.24 Estimasi Pertumbuhan Sistem

#### Tahun Pertama

* 100 alumni
* 5.000 foto

#### Tahun Kelima

* 100 alumni
* 20.000+ foto

#### Dampak

Sistem masih dapat berjalan dengan spesifikasi server rekomendasi tanpa perubahan arsitektur besar.

### 11.25 Arsitektur Target Final

Browser
↓
HTTPS
↓
Nginx
↓
Laravel Application
↓
MySQL Database
↓
Storage Foto

Laravel Scheduler
↓
Queue Worker
↓
Analytics & Maintenance

Backup Service
↓
NAS / Cloud Storage

### 11.26 Kesimpulan Arsitektur

Arsitektur yang dipilih berfokus pada:

1. Kesederhanaan implementasi.
2. Kemudahan pemeliharaan.
3. Biaya operasional rendah.
4. Keamanan yang memadai.
5. Skalabilitas yang cukup untuk komunitas alumni.
6. Keberlanjutan sebagai arsip digital jangka panjang.

Dengan arsitektur ini, sistem dapat melayani kebutuhan reuni tahun 2026 sekaligus berfungsi sebagai direktori alumni, buku kenangan digital, pusat dokumentasi, dan arsip sejarah Geodesi UGM Angkatan 1996 untuk jangka panjang.

## BAB 12 - API SPECIFICATION & INTEGRATION DESIGN

### 12.1 Pendahuluan

Bab ini menjelaskan rancangan API dan integrasi yang digunakan dalam Sistem Manajemen Reuni Alumni Teknik Geodesi UGM Angkatan 1996.

API digunakan untuk mendukung komunikasi antara frontend, backend Laravel, komponen asynchronous, fitur upload, peta alumni, dashboard analytics, dan integrasi eksternal.

Dokumen ini bertujuan menjadi acuan bagi tim developer dalam merancang route, controller, request validation, response format, dan integrasi sistem.

### 12.2 Prinsip Desain API

API dirancang dengan prinsip:

1. Konsisten.
2. Aman.
3. Mudah diuji.
4. Mudah dikembangkan.
5. Menggunakan format JSON.
6. Mengikuti standar RESTful sejauh diperlukan.
7. Mendukung role-based access control.
8. Tidak mengekspos data sensitif secara tidak perlu.

### 12.3 Jenis Endpoint

Sistem memiliki dua jenis endpoint:

#### Web Routes

Digunakan untuk halaman Laravel berbasis Blade, Livewire, Flux UI, dan Tailwind CSS.

Contoh:

* `/login`
* `/dashboard`
* `/profile`
* `/admin/alumni`

#### API Routes

Digunakan untuk request asynchronous, dashboard, upload, peta, dan analytics.

Contoh:

* `/api/alumni`
* `/api/photos`
* `/api/map/alumni`
* `/api/whatsapp/statistics`

### 12.4 Format Response Standar

Seluruh API menggunakan format response JSON yang konsisten.

#### Response Berhasil

```json
{
  "success": true,
  "message": "Data berhasil diproses.",
  "data": {}
}
```

#### Response Gagal

```json
{
  "success": false,
  "message": "Terjadi kesalahan.",
  "errors": {}
}
```

#### Response Pagination

```json
{
  "success": true,
  "message": "Data berhasil diambil.",
  "data": [],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 100,
    "last_page": 5
  }
}
```

### 12.5 Authentication API

#### POST /login

Digunakan untuk login pengguna.

##### Request

```json
{
  "whatsapp_number": "6281234567890",
  "password": "password"
}
```

##### Response

```json
{
  "success": true,
  "message": "Login berhasil.",
  "data": {
    "user": {
      "id": 1,
      "role": "alumni"
    }
  }
}
```

#### POST /logout

Digunakan untuk logout pengguna.

##### Response

```json
{
  "success": true,
  "message": "Logout berhasil."
}
```

#### POST /change-password

Digunakan untuk mengganti password.

##### Request

```json
{
  "current_password": "old-password",
  "new_password": "new-password",
  "new_password_confirmation": "new-password"
}
```

#### POST /change-whatsapp-number

Digunakan untuk mengganti nomor WhatsApp login.

##### Request

```json
{
  "new_whatsapp_number": "6289876543210",
  "password": "current-password"
}
```

### 12.6 Alumni Profile API

#### GET /api/profile

Mengambil profil alumni yang sedang login.

##### Response

```json
{
  "success": true,
  "data": {
    "id": 1,
    "full_name": "Nama Alumni",
    "nickname": "Panggilan",
    "email": "email@example.com",
    "city": "Denpasar",
    "country": "Indonesia",
    "company": "Nama Perusahaan",
    "job_title": "Jabatan"
  }
}
```

#### PUT /api/profile

Mengubah profil alumni yang sedang login.

##### Request

```json
{
  "nickname": "Panggilan",
  "email": "email@example.com",
  "current_city_id": 1,
  "current_country_id": 1,
  "company": "Nama Perusahaan",
  "job_title": "Jabatan",
  "special_notes": "Catatan khusus",
  "short_story": "Cerita singkat",
  "memorable_story": "Kenangan",
  "message_to_friends": "Pesan untuk teman alumni"
}
```

#### POST /api/profile/college-photo

Upload foto masa kuliah.

##### Request

Multipart form-data:

* `photo`

#### POST /api/profile/current-photo

Upload foto saat ini.

##### Request

Multipart form-data:

* `photo`

### 12.7 Alumni Directory API

#### GET /api/alumni

Mengambil daftar alumni.

##### Query Parameter

* `search`
* `city_id`
* `country_id`
* `status`
* `page`

##### Response

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "full_name": "Nama Alumni",
      "nickname": "Panggilan",
      "city": "Denpasar",
      "country": "Indonesia",
      "alumni_status": "active"
    }
  ]
}
```

#### GET /api/alumni/{id}

Mengambil detail profil alumni.

### 12.8 RSVP API

#### GET /api/rsvp

Mengambil status RSVP pengguna login.

#### PUT /api/rsvp

Mengubah status RSVP.

##### Request

```json
{
  "status": "attending",
  "notes": "Catatan tambahan"
}
```

##### Enum Status

* `pending`
* `attending`
* `not_attending`

### 12.9 Payment API

#### GET /api/payment/status

Mengambil status pembayaran alumni yang sedang login.

#### GET /api/admin/payments

Mengambil daftar pembayaran.

Role:

* Bendahara
* Superadmin

#### PUT /api/admin/payments/{id}/verify

Verifikasi pembayaran.

##### Request

```json
{
  "status": "paid",
  "payment_date": "2026-08-01",
  "notes": "Sudah diterima."
}
```

##### Enum Status

* `unpaid`
* `pending_verification`
* `paid`

### 12.10 Donation API

#### GET /api/donors

Mengambil daftar donatur untuk publik.

Data nominal tidak ditampilkan.

#### GET /api/admin/donations

Mengambil daftar donasi.

Role:

* Bendahara
* Superadmin

#### POST /api/admin/donations

Menambahkan donasi.

##### Request

```json
{
  "alumni_id": 1,
  "amount": 1000000,
  "publication_status": "show_name",
  "notes": "Donasi acara"
}
```

#### PUT /api/admin/donations/{id}

Mengubah donasi.

### 12.11 Rooming API

#### GET /api/room

Mengambil informasi kamar alumni yang sedang login.

#### GET /api/admin/rooms

Mengambil daftar kamar.

#### POST /api/admin/rooms

Membuat kamar.

##### Request

```json
{
  "room_name": "Kamar 01",
  "room_type": "Twin Share",
  "capacity": 2,
  "notes": "Dekat joglo utama"
}
```

#### POST /api/admin/room-assignments

Menempatkan alumni ke kamar.

##### Request

```json
{
  "room_id": 1,
  "alumni_id": 10,
  "notes": "Assignment awal"
}
```

### 12.12 Documentation API

#### GET /api/photos

Mengambil daftar foto.

##### Query Parameter

* `year`
* `month`
* `visibility`
* `uploaded_by`
* `tagged_alumni_id`

#### POST /api/photos

Upload foto.

##### Request

Multipart form-data:

* `photo`
* `title`
* `description`
* `month`
* `year`
* `visibility`
* `tagged_alumni_ids[]`

#### PUT /api/photos/{id}

Mengubah metadata foto.

#### DELETE /api/photos/{id}

Soft delete foto milik sendiri.

#### GET /api/videos

Mengambil daftar video.

#### POST /api/videos

Menambahkan video URL.

##### Request

```json
{
  "title": "Video Reuni",
  "description": "Dokumentasi acara",
  "video_url": "https://youtube.com/...",
  "provider": "youtube",
  "month": 8,
  "year": 2026,
  "visibility": "internal",
  "tagged_alumni_ids": [1, 2, 3]
}
```

#### DELETE /api/videos/{id}

Soft delete video milik sendiri.

### 12.13 News API

#### GET /api/news

Mengambil daftar berita published.

#### GET /api/news/{slug}

Mengambil detail berita.

#### POST /api/admin/news

Membuat berita.

Role:

* Administrator
* Superadmin

#### PUT /api/admin/news/{id}

Mengubah berita.

#### DELETE /api/admin/news/{id}

Soft delete berita.

### 12.14 Map API

#### GET /api/map/alumni

Mengambil data persebaran alumni.

##### Response

```json
{
  "success": true,
  "data": [
    {
      "city": "Denpasar",
      "country": "Indonesia",
      "latitude": -8.6500,
      "longitude": 115.2167,
      "total_alumni": 5
    }
  ]
}
```

#### GET /api/map/location/{city_id}

Mengambil daftar alumni pada kota tertentu.

### 12.15 Timeline API

#### GET /api/timelines/me

Mengambil timeline alumni yang sedang login.

#### POST /api/timelines

Menambah timeline lokasi.

##### Request

```json
{
  "month": 8,
  "year": 2026,
  "city_id": 1,
  "country_id": 1,
  "latitude": -8.6500,
  "longitude": 115.2167,
  "location_source": "manual",
  "notes": "Domisili saat ini"
}
```

#### PUT /api/timelines/{id}

Mengubah timeline lokasi.

#### DELETE /api/timelines/{id}

Menghapus timeline lokasi.

### 12.16 WhatsApp Analytics API

#### POST /api/admin/whatsapp/import

Upload file ekspor WhatsApp.

Role:

* Administrator
* Superadmin

##### Request

Multipart form-data:

* `chat_file`

#### POST /api/admin/whatsapp/import/{id}/process

Memproses file WhatsApp.

#### GET /api/whatsapp/statistics

Mengambil ringkasan statistik WhatsApp.

#### GET /api/whatsapp/hall-of-fame

Mengambil data Hall of Fame.

##### Category

* active_member
* silent_reader
* link_poster
* image_poster
* nocturnal_chatter
* work_time_chatter
* weekend_warrior
* emoji_champion

#### GET /api/whatsapp/topics

Mengambil top 10 topik populer.

#### GET /api/whatsapp/word-cloud

Mengambil data word cloud.

### 12.17 Admin Alumni API

#### GET /api/admin/alumni

Mengambil seluruh data alumni.

#### POST /api/admin/alumni

Membuat alumni baru.

#### PUT /api/admin/alumni/{id}

Mengubah data alumni.

#### PUT /api/admin/alumni/{id}/status

Mengubah status alumni.

##### Request

```json
{
  "alumni_status": "deceased"
}
```

#### POST /api/admin/alumni/import

Import data alumni dari file.

### 12.18 Master Data API

#### GET /api/countries

Mengambil daftar negara.

#### GET /api/cities

Mengambil daftar kota.

##### Query Parameter

* `country_id`
* `search`

#### POST /api/admin/countries

Menambah negara.

#### POST /api/admin/cities

Menambah kota.

### 12.19 Audit Log API

#### GET /api/admin/audit-logs

Mengambil audit log.

Role:

* Superadmin

##### Query Parameter

* `user_id`
* `action`
* `date_from`
* `date_to`

### 12.20 External Integration

Sistem menggunakan integrasi eksternal berikut:

#### OpenStreetMap / Leaflet

Digunakan untuk menampilkan peta alumni.

#### Nominatim Geocoding

Digunakan untuk mencari koordinat berdasarkan kota dan negara.

#### YouTube

Digunakan untuk menyimpan dan menampilkan video dokumentasi melalui URL.

#### Google Drive

Digunakan sebagai alternatif penyimpanan video dokumentasi melalui URL.

#### SMTP Server

Digunakan untuk:

* Reset password
* Notifikasi sistem
* Notifikasi administrator

### 12.21 File Upload Integration

#### Foto

Alur:

Upload
↓
Validasi file
↓
Resize
↓
Kompresi
↓
Simpan ke storage
↓
Simpan metadata ke database

#### Validasi Foto

* Format: JPG, JPEG, PNG, WEBP
* Ukuran maksimum ditentukan konfigurasi
* File wajib diproses sebelum disimpan permanen

### 12.22 Queue Integration

Proses berikut direkomendasikan menggunakan queue:

1. Resize foto.
2. Kompresi foto.
3. Import WhatsApp chat.
4. Generate WhatsApp Analytics.
5. Backup rutin.
6. Pengiriman email.

### 12.23 Scheduler Integration

Scheduler Laravel digunakan untuk:

1. Backup database harian.
2. Backup file mingguan.
3. Membersihkan temporary file.
4. Mengecek status queue.
5. Regenerate statistik apabila diperlukan.

### 12.24 Security Requirement API

Setiap endpoint privat wajib:

1. Menggunakan autentikasi.
2. Memeriksa role.
3. Memvalidasi input.
4. Menggunakan CSRF protection untuk web routes.
5. Menggunakan rate limiting untuk endpoint login.
6. Tidak menampilkan password atau hash.
7. Tidak mengekspos nominal donasi kepada publik/alumni biasa.
8. Tidak mengekspos raw chat WhatsApp.

### 12.25 Error Code

Kode error standar:

| HTTP Code | Keterangan       |
| --------- | ---------------- |
| 200       | OK               |
| 201       | Created          |
| 400       | Bad Request      |
| 401       | Unauthorized     |
| 403       | Forbidden        |
| 404       | Not Found        |
| 422       | Validation Error |
| 500       | Server Error     |

### 12.26 Catatan Implementasi Laravel

Rekomendasi struktur controller:

* AuthController
* ProfileController
* AlumniController
* RsvpController
* PaymentController
* DonationController
* RoomController
* DocumentationController
* PhotoController
* VideoController
* NewsController
* MapController
* TimelineController
* WhatsappAnalyticsController
* AdminController
* AuditLogController

### 12.27 Kesimpulan

Rancangan API ini disusun untuk mendukung implementasi Laravel yang modular, aman, dan mudah dikembangkan.

API tidak hanya mendukung kebutuhan halaman web, tetapi juga mendukung fitur interaktif seperti dashboard, peta alumni, dokumentasi, dan WhatsApp Analytics.

Dengan rancangan ini, sistem tetap dapat dikembangkan lebih lanjut apabila di masa depan diperlukan integrasi mobile application atau layanan eksternal lainnya.

## BAB 13 - SCREEN-BY-SCREEN UI SPECIFICATION

### 13.1 Pendahuluan

Bab ini menjelaskan spesifikasi antarmuka setiap halaman pada Sistem Manajemen Reuni Alumni Teknik Geodesi UGM Angkatan 1996.

Dokumen ini digunakan sebagai acuan bagi:

1. UI/UX Designer.
2. Frontend Developer.
3. Backend Developer.
4. Tester.
5. Tim dokumentasi.

Setiap halaman dijelaskan berdasarkan:

* Tujuan halaman.
* Role yang dapat mengakses.
* Komponen utama.
* Field input.
* Tombol aksi.
* Validasi.
* Kondisi tampilan.

### 13.2 Public Website – Home Page

#### Tujuan

Menjadi halaman utama website reuni.

#### Akses

Publik.

#### Komponen

* Header.
* Logo acara.
* Hero banner.
* Tema reuni.
* Tanggal acara.
* Countdown.
* Ringkasan acara.
* Highlight dokumentasi.
* Berita terbaru.
* Tombol Login.
* Footer.

#### Tombol

* Login.
* Lihat Rundown.
* Lihat Lokasi.
* Lihat Galeri Publik.

#### Kondisi Khusus

Sebelum acara, countdown menampilkan waktu menuju reuni.

Setelah acara selesai, countdown diganti menjadi narasi arsip:

“Dokumentasi 30 Tahun Paseduluran Geodesi 96”.

### 13.3 Public Website – Tentang Reuni

#### Tujuan

Menjelaskan filosofi kegiatan.

#### Akses

Publik.

#### Komponen

* Judul halaman.
* Narasi Kembali ke Titik Nol.
* Makna logo.
* Makna tagline.
* Visual identitas reuni.
* Ringkasan acara.

### 13.4 Public Website – Rundown Acara

#### Tujuan

Menampilkan jadwal kegiatan reuni.

#### Akses

Publik.

#### Komponen

* Tab Hari 1.
* Tab Hari 2.
* Tab Gala Dinner.
* Daftar kegiatan.
* Waktu mulai.
* Waktu selesai.
* Lokasi.

#### Kondisi Khusus

Jika rundown belum final, tampilkan label “Tentatif”.

### 13.5 Public Website – Lokasi Acara

#### Tujuan

Menampilkan lokasi kegiatan.

#### Akses

Publik.

#### Komponen

* Kampung Wisata Tembi.
* Departemen Teknik Geodesi UGM.
* Lokasi gala dinner.
* Peta.
* Tombol arah.

#### Kondisi Khusus

Jika lokasi gala dinner belum final, tampilkan status “Dalam proses seleksi”.

### 13.6 Public Website – Galeri Publik

#### Tujuan

Menampilkan dokumentasi yang disetujui sebagai publik.

#### Akses

Publik.

#### Komponen

* Grid foto.
* Grid video.
* Filter tahun.
* Filter jenis dokumentasi.
* Detail foto/video.

### 13.7 Public Website – Donatur

#### Tujuan

Menampilkan daftar donatur.

#### Akses

Publik.

#### Komponen

* Daftar nama donatur.
* Donatur anonim.
* Ucapan terima kasih.

#### Catatan

Nominal donasi tidak ditampilkan.

### 13.8 Login Page

#### Tujuan

Memungkinkan pengguna masuk ke sistem.

#### Akses

Publik.

#### Field

* Nomor WhatsApp.
* Password.

#### Tombol

* Login.

#### Validasi

* Nomor WhatsApp wajib diisi.
* Password wajib diisi.
* Kombinasi login harus valid.

#### Error Message

* Nomor WhatsApp atau password salah.
* Akun tidak aktif.
* Akun tidak ditemukan.

### 13.9 Dashboard Alumni

#### Tujuan

Menampilkan ringkasan informasi utama alumni.

#### Akses

Alumni.

#### Komponen

* Welcome card.
* Foto profil.
* Status RSVP.
* Status pembayaran.
* Informasi kamar.
* Kelengkapan profil.
* Berita terbaru.
* Dokumentasi terbaru.
* Ringkasan WhatsApp Analytics.
* Mini peta alumni.

#### Tombol

* Lengkapi Profil.
* Isi RSVP.
* Lihat Dokumentasi.
* Lihat Peta Alumni.

### 13.10 Profil Saya – Informasi Dasar

#### Tujuan

Mengelola data pribadi alumni.

#### Akses

Alumni.

#### Field

* Nama lengkap.
* Nama panggilan.
* Nomor WhatsApp.
* Email.
* Kota.
* Negara.
* Instansi/perusahaan.
* Profesi/jabatan.
* Catatan khusus.

#### Tombol

* Simpan.
* Batal.

#### Validasi

* Nama lengkap wajib diisi.
* Nomor WhatsApp wajib diisi.
* Nomor WhatsApp harus unik.
* Email harus valid jika diisi.

### 13.11 Profil Saya – Foto Masa Kuliah

#### Tujuan

Mengunggah foto utama masa kuliah.

#### Akses

Alumni.

#### Komponen

* Preview foto.
* Upload file.

#### Tombol

* Upload.
* Ganti Foto.

#### Validasi

* Format JPG, JPEG, PNG, atau WEBP.
* Ukuran maksimum mengikuti konfigurasi sistem.

### 13.12 Profil Saya – Foto Saat Ini

#### Tujuan

Mengunggah foto utama saat ini.

#### Akses

Alumni.

#### Komponen

* Preview foto.
* Upload file.

#### Tombol

* Upload.
* Ganti Foto.

#### Validasi

* Format JPG, JPEG, PNG, atau WEBP.
* Ukuran maksimum mengikuti konfigurasi sistem.

### 13.13 Profil Saya – Cerita Alumni

#### Tujuan

Mengisi cerita singkat alumni.

#### Akses

Alumni.

#### Field

* Cerita singkat.

#### Tombol

* Simpan.

### 13.14 Profil Saya – Kenangan Alumni

#### Tujuan

Mengisi kenangan lucu atau tak terlupakan.

#### Akses

Alumni.

#### Field

* Kenangan alumni.

#### Tombol

* Simpan.

### 13.15 Profil Saya – Pesan Alumni

#### Tujuan

Mengisi pesan untuk rekan alumni.

#### Akses

Alumni.

#### Field

* Pesan untuk rekan alumni.

#### Tombol

* Simpan.

### 13.16 Profil Saya – Timeline Lokasi

#### Tujuan

Mengelola riwayat lokasi alumni.

#### Akses

Alumni.

#### Komponen

* Tabel timeline.
* Form tambah/edit timeline.

#### Field

* Bulan.
* Tahun.
* Kota.
* Negara.
* Latitude.
* Longitude.
* Catatan.

#### Tombol

* Tambah Timeline.
* Simpan.
* Edit.
* Hapus.

#### Validasi

* Tahun wajib diisi.
* Kota wajib diisi.
* Negara wajib diisi.
* Bulan opsional.

### 13.17 Direktori Alumni

#### Tujuan

Menampilkan daftar alumni.

#### Akses

Alumni.

#### Komponen

* Search bar.
* Filter kota.
* Filter negara.
* Filter status alumni.
* Grid alumni.

#### Card Alumni

Menampilkan:

* Foto.
* Nama.
* Nama panggilan.
* Kota.
* Negara.
* Status alumni.

#### Tombol

* Lihat Profil.

### 13.18 Detail Profil Alumni

#### Tujuan

Menampilkan profil lengkap alumni.

#### Akses

Alumni.

#### Komponen

* Foto masa kuliah.
* Foto saat ini.
* Nama lengkap.
* Nama panggilan.
* Kota.
* Negara.
* Instansi.
* Jabatan.
* Cerita.
* Kenangan.
* Pesan.
* Timeline.
* Uploaded Gallery.
* Tagged Gallery.

### 13.19 Memorial Profile

#### Tujuan

Menampilkan profil alumni yang telah meninggal.

#### Akses

Alumni.

#### Komponen

* Banner “In Memoriam”.
* Foto alumni.
* Nama alumni.
* Cerita.
* Kenangan.
* Pesan.
* Foto dan video tagged.
* Timeline.

#### Kondisi Khusus

Akun login alumni memorial dinonaktifkan.

### 13.20 RSVP Page

#### Tujuan

Mengisi status kehadiran reuni.

#### Akses

Alumni.

#### Field

* Status RSVP.

#### Pilihan

* Hadir.
* Tidak Hadir.

#### Tombol

* Simpan.

#### Validasi

* Status wajib dipilih.

### 13.21 Payment Status Page

#### Tujuan

Menampilkan status pembayaran alumni.

#### Akses

Alumni.

#### Komponen

* Status pembayaran.
* Tanggal pembayaran.
* Tanggal verifikasi.
* Catatan bendahara.

#### Status

* Belum Bayar.
* Menunggu Verifikasi.
* Lunas.

### 13.22 Room Information Page

#### Tujuan

Menampilkan informasi kamar alumni.

#### Akses

Alumni.

#### Komponen

* Nama kamar.
* Tipe kamar.
* Kapasitas kamar.
* Daftar penghuni.
* Catatan kamar.

### 13.23 Documentation Gallery

#### Tujuan

Menampilkan foto dan video.

#### Akses

Alumni.

#### Komponen

* Search bar.
* Filter tahun.
* Filter bulan.
* Filter jenis dokumentasi.
* Filter visibility.
* Grid galeri.

#### Tombol

* Upload Foto.
* Tambah Video.

### 13.24 Upload Photo Page

#### Tujuan

Mengunggah foto dokumentasi.

#### Akses

Alumni.

#### Field

* Judul.
* Deskripsi.
* File foto.
* Bulan.
* Tahun.
* Visibility.
* Tag alumni.

#### Tombol

* Upload.
* Batal.

#### Validasi

* File foto wajib diisi.
* Tahun wajib diisi.
* Visibility wajib dipilih.
* Format file harus valid.

### 13.25 Add Video Page

#### Tujuan

Menambahkan dokumentasi video melalui URL.

#### Akses

Alumni.

#### Field

* Judul.
* Deskripsi.
* URL video.
* Provider.
* Bulan.
* Tahun.
* Visibility.
* Tag alumni.

#### Tombol

* Simpan.
* Batal.

#### Validasi

* Judul wajib diisi.
* URL wajib diisi.
* Tahun wajib diisi.
* Visibility wajib dipilih.

### 13.26 Documentation Detail Page

#### Tujuan

Menampilkan detail foto atau video.

#### Akses

Alumni.

#### Komponen

* Foto/video utama.
* Judul.
* Deskripsi.
* Uploader.
* Tahun.
* Bulan.
* Tag alumni.
* Visibility.

#### Tombol

Untuk uploader:

* Edit.
* Hapus.

Untuk admin:

* Ubah Visibility.
* Restore.
* Hapus Permanen.

### 13.27 Buku Kenangan Digital

#### Tujuan

Menampilkan buku kenangan alumni.

#### Akses

Alumni.

#### Komponen

* Grid alumni.
* Filter nama.
* Filter kota.
* Filter status alumni.
* Link ke profil alumni.

### 13.28 Peta Alumni

#### Tujuan

Menampilkan persebaran alumni.

#### Akses

Alumni.

#### Komponen

* Peta interaktif.
* Marker kota.
* Cluster marker.
* Statistik kota.
* Statistik negara.

#### Interaksi

Klik marker menampilkan daftar alumni pada kota tersebut.

### 13.29 Timeline Explorer

#### Tujuan

Menampilkan timeline perjalanan alumni.

#### Akses

Alumni.

#### Komponen

* Filter alumni.
* Timeline visual.
* Tahun.
* Kota.
* Negara.
* Catatan.

### 13.30 WhatsApp Analytics Page

#### Tujuan

Menampilkan statistik grup WhatsApp alumni.

#### Akses

Alumni.

#### Komponen

* Hall of Fame.
* Statistik aktivitas grup.
* Top 10 topik.
* Word cloud.
* Insight historis.

#### Catatan

Raw chat tidak ditampilkan.

### 13.31 Admin Dashboard

#### Tujuan

Menampilkan ringkasan operasional reuni.

#### Akses

Administrator, Superadmin.

#### Komponen

* Total alumni.
* RSVP hadir.
* RSVP tidak hadir.
* Dokumentasi.
* Kamar.
* Berita terbaru.
* Quick action.

### 13.32 Admin – Manajemen Alumni

#### Tujuan

Mengelola data alumni.

#### Akses

Administrator, Superadmin.

#### Komponen

* Tabel alumni.
* Search.
* Filter status.
* Filter kota.
* Filter negara.

#### Tombol

* Tambah Alumni.
* Import Alumni.
* Edit.
* Reset Password.
* Ubah Status.

### 13.33 Admin – Form Alumni

#### Tujuan

Menambah atau mengubah data alumni.

#### Akses

Administrator, Superadmin.

#### Field

* Nama lengkap.
* Nama panggilan.
* Nomor WhatsApp.
* Email.
* Kota.
* Negara.
* Instansi.
* Jabatan.
* Status alumni.
* Status akun.

#### Tombol

* Simpan.
* Batal.

### 13.34 Admin – RSVP Management

#### Tujuan

Memantau RSVP.

#### Akses

Administrator, Superadmin.

#### Komponen

* Tabel RSVP.
* Filter status.
* Export Excel.
* Export PDF.

### 13.35 Admin – Room Management

#### Tujuan

Mengelola kamar.

#### Akses

Administrator, Superadmin.

#### Komponen

* Tabel kamar.
* Tabel penghuni kamar.

#### Tombol

* Tambah Kamar.
* Edit Kamar.
* Assign Alumni.
* Cetak Rooming List.

### 13.36 Admin – Documentation Management

#### Tujuan

Mengelola seluruh dokumentasi.

#### Akses

Administrator, Superadmin.

#### Komponen

* Tabel foto.
* Tabel video.
* Filter visibility.
* Filter uploader.
* Filter tahun.

#### Tombol

* Ubah Visibility.
* Restore.
* Hapus Permanen.

### 13.37 Admin – News Management

#### Tujuan

Mengelola berita dan pengumuman.

#### Akses

Administrator, Superadmin.

#### Komponen

* Tabel berita.
* Status draft/published/archived.

#### Tombol

* Tambah Berita.
* Edit.
* Publish.
* Archive.
* Hapus.

### 13.38 Bendahara Dashboard

#### Tujuan

Menampilkan ringkasan keuangan.

#### Akses

Bendahara, Superadmin.

#### Komponen

* Pembayaran lunas.
* Pembayaran menunggu verifikasi.
* Donatur.
* Total dana terkumpul.

### 13.39 Bendahara – Payment Management

#### Tujuan

Mengelola pembayaran reuni.

#### Akses

Bendahara, Superadmin.

#### Komponen

* Tabel pembayaran.
* Filter status.

#### Field

* Status pembayaran.
* Tanggal pembayaran.
* Nominal.
* Catatan.

#### Tombol

* Edit.
* Verifikasi.
* Export.

### 13.40 Bendahara – Donation Management

#### Tujuan

Mengelola donasi.

#### Akses

Bendahara, Superadmin.

#### Komponen

* Tabel donasi.
* Filter publikasi.

#### Field

* Alumni.
* Nominal.
* Status publikasi.
* Catatan.

#### Pilihan Status Publikasi

* Tampilkan nama saya.
* Donatur anonim.

#### Tombol

* Tambah Donasi.
* Edit.
* Hapus.
* Export.

### 13.41 Superadmin – User Management

#### Tujuan

Mengelola akun pengguna.

#### Akses

Superadmin.

#### Komponen

* Tabel user.
* Filter role.
* Filter status akun.

#### Tombol

* Tambah User.
* Edit Role.
* Reset Password.
* Aktifkan.
* Nonaktifkan.

### 13.42 Superadmin – Audit Log

#### Tujuan

Melihat aktivitas penting sistem.

#### Akses

Superadmin.

#### Komponen

* Tabel audit log.
* Filter user.
* Filter aksi.
* Filter tanggal.

### 13.43 Superadmin – Configuration Page

#### Tujuan

Mengelola konfigurasi dasar sistem.

#### Akses

Superadmin.

#### Field

* Nama website.
* Logo.
* Tema warna.
* Tanggal reuni.
* Lokasi reuni.
* Maksimum ukuran upload.
* Pengaturan backup.

#### Tombol

* Simpan Konfigurasi.

### 13.44 Empty State

Setiap halaman wajib memiliki empty state.

Contoh:

* Belum ada dokumentasi.
* Belum ada berita.
* Belum ada timeline.
* Belum ada data kamar.
* Belum ada hasil analytics.

### 13.45 Loading State

Setiap halaman yang mengambil data secara asynchronous harus memiliki loading state.

Contoh:

* Spinner.
* Skeleton card.
* Loading table.

### 13.46 Error State

Setiap halaman wajib menampilkan pesan error yang jelas.

Contoh:

* Data gagal dimuat.
* File terlalu besar.
* Format file tidak didukung.
* Anda tidak memiliki akses.

### 13.47 Success Notification

Setiap aksi berhasil wajib menampilkan notifikasi.

Contoh:

* Profil berhasil disimpan.
* RSVP berhasil diperbarui.
* Foto berhasil diunggah.
* Pembayaran berhasil diverifikasi.

### 13.48 Mobile Behaviour

Pada perangkat mobile:

* Menu menggunakan hamburger menu.
* Tabel berubah menjadi card list jika diperlukan.
* Form menggunakan single column.
* Tombol aksi utama selalu mudah dijangkau.
* Galeri menggunakan grid responsif.
* Peta tetap dapat di-scroll dan di-zoom.

## BAB 14 - DEVELOPMENT ROADMAP & SPRINT PLAN

### 14.1 Pendahuluan

Bab ini menjelaskan rencana pengembangan Sistem Manajemen Reuni Alumni Teknik Geodesi UGM Angkatan 1996 secara bertahap.

Tujuan penyusunan roadmap adalah:

1. Menentukan prioritas pengembangan.
2. Membagi pekerjaan menjadi fase yang terukur.
3. Membantu tim developer menyusun sprint.
4. Memastikan fitur utama selesai sebelum acara reuni.
5. Memisahkan fitur wajib, fitur pendukung, dan fitur pasca acara.

### 14.2 Strategi Pengembangan

Pengembangan sistem dilakukan menggunakan pendekatan bertahap.

Fase utama:

1. Foundation Phase
2. Core Feature Phase
3. Event Management Phase
4. Documentation & Archive Phase
5. Analytics Phase
6. Stabilization & Deployment Phase

### 14.3 Prioritas Fitur

#### Prioritas 1 – Wajib

Fitur yang harus tersedia sebelum sistem digunakan:

* Login
* Role dan hak akses
* Manajemen alumni
* Profil alumni
* RSVP
* Pembayaran
* Donasi
* Rooming
* Berita
* Galeri dasar

#### Prioritas 2 – Penting

Fitur yang sangat mendukung pengalaman reuni:

* Buku kenangan digital
* Peta alumni
* Timeline alumni
* Upload foto
* Tagging alumni
* Video URL
* Galeri publik/internal

#### Prioritas 3 – Menarik

Fitur tambahan untuk meningkatkan engagement:

* WhatsApp Analytics
* Word cloud
* Hall of Fame
* Dashboard statistik
* Export laporan

#### Prioritas 4 – Pasca Acara

Fitur penguatan arsip digital:

* Kurasi dokumentasi
* Berita pasca reuni
* Arsip final acara
* Optimasi galeri
* Backup permanen

### 14.4 Roadmap Pengembangan

#### Phase 1 – Project Setup & Foundation

Tujuan:

Menyiapkan kerangka dasar aplikasi.

Output:

* Repository Git
* Instalasi Laravel
* Struktur folder
* Setup database
* Authentication
* Role management
* Layout dasar

Estimasi:

1 sprint

#### Phase 2 – Alumni Core Module

Tujuan:

Membangun modul inti alumni.

Output:

* Manajemen alumni
* Akun alumni
* Profil alumni
* Foto masa kuliah
* Foto saat ini
* Direktori alumni
* Memorial profile

Estimasi:

1–2 sprint

#### Phase 3 – Event Operation Module

Tujuan:

Membangun fitur operasional reuni.

Output:

* RSVP
* Pembayaran
* Donasi
* Rooming
* Dashboard admin
* Dashboard bendahara

Estimasi:

1–2 sprint

#### Phase 4 – Public Website

Tujuan:

Membangun halaman publik.

Output:

* Home page
* Tentang reuni
* Rundown
* Lokasi acara
* Berita
* Galeri publik
* Donatur
* Kontak panitia

Estimasi:

1 sprint

#### Phase 5 – Documentation & Memory Book

Tujuan:

Membangun arsip digital.

Output:

* Upload foto
* Resize dan kompresi foto
* Video URL
* Tagging alumni
* Galeri uploaded
* Galeri tagged
* Buku kenangan digital

Estimasi:

2 sprint

#### Phase 6 – Map & Timeline Module

Tujuan:

Membangun fitur khas Geodesi.

Output:

* Master negara
* Master kota
* Geocoding
* Peta persebaran alumni
* Timeline lokasi alumni
* Statistik kota/negara

Estimasi:

1–2 sprint

#### Phase 7 – WhatsApp Analytics

Tujuan:

Membangun fitur analisis grup WhatsApp.

Output:

* Upload file chat
* Parsing chat
* Normalisasi nama
* Hall of Fame
* Topik populer
* Statistik waktu
* Word cloud

Estimasi:

2 sprint

#### Phase 8 – Reporting & Export

Tujuan:

Membangun laporan dan export.

Output:

* Laporan alumni
* Laporan RSVP
* Laporan pembayaran
* Laporan donasi
* Rooming list
* Export Excel
* Export PDF

Estimasi:

1 sprint

#### Phase 9 – Security, Testing & Deployment

Tujuan:

Menstabilkan sistem sebelum go-live.

Output:

* Security hardening
* Testing
* Backup automation
* Deployment staging
* Deployment production
* UAT

Estimasi:

1–2 sprint

### 14.5 Sprint Plan

#### Sprint 0 – Preparation

Aktivitas:

* Finalisasi requirement
* Finalisasi blueprint
* Setup repository
* Setup development environment
* Setup project management board

Deliverable:

* Repository siap
* Environment siap
* Backlog awal tersedia

#### Sprint 1 – Foundation

Aktivitas:

* Install Laravel
* Setup authentication
* Setup role
* Setup layout
* Setup migration awal
* Seeder role

Deliverable:

* Login berjalan
* Role tersedia
* Dashboard dasar tersedia

#### Sprint 2 – Alumni Management

Aktivitas:

* CRUD alumni
* Import alumni
* User-alumni relation
* Status alumni aktif/meninggal
* Reset password alumni

Deliverable:

* Admin dapat mengelola data alumni
* Akun alumni dapat digunakan

#### Sprint 3 – Alumni Profile & Directory

Aktivitas:

* Profil saya
* Edit profil
* Upload foto masa kuliah
* Upload foto saat ini
* Direktori alumni
* Detail profil alumni
* Memorial profile

Deliverable:

* Alumni dapat melengkapi profil
* Direktori alumni dapat digunakan

#### Sprint 4 – RSVP, Payment & Donation

Aktivitas:

* RSVP alumni
* Payment management
* Donation management
* Dashboard bendahara
* Status pembayaran alumni

Deliverable:

* Panitia dapat memantau kehadiran
* Bendahara dapat mengelola pembayaran dan donasi

#### Sprint 5 – Rooming & Public Website

Aktivitas:

* Room management
* Room assignment
* Home page
* Tentang reuni
* Rundown
* Lokasi
* Donatur

Deliverable:

* Informasi publik tersedia
* Alumni dapat melihat informasi kamar

#### Sprint 6 – News & Documentation Basic

Aktivitas:

* News management
* Galeri publik
* Upload foto
* Kompresi foto
* Upload video URL

Deliverable:

* Berita dapat dipublikasikan
* Dokumentasi dasar dapat digunakan

#### Sprint 7 – Documentation Advanced

Aktivitas:

* Tagging alumni
* Uploaded gallery
* Tagged gallery
* Detail dokumentasi
* Soft delete dokumentasi
* Admin documentation management

Deliverable:

* Dokumentasi kolektif berjalan lengkap

#### Sprint 8 – Memory Book

Aktivitas:

* Buku kenangan digital
* Halaman cerita alumni
* Halaman kenangan alumni
* Halaman pesan alumni
* Integrasi profil dan galeri

Deliverable:

* Buku kenangan digital dapat digunakan

#### Sprint 9 – Map & Timeline

Aktivitas:

* Master country
* Master city
* Timeline lokasi alumni
* Geocoding
* Peta alumni
* Statistik kota/negara

Deliverable:

* Peta alumni dan timeline tersedia

#### Sprint 10 – WhatsApp Analytics

Aktivitas:

* Upload file chat
* Parser WhatsApp
* Normalisasi nama
* Statistik anggota
* Statistik waktu
* Topik populer
* Word cloud

Deliverable:

* WhatsApp Analytics dapat diakses alumni

#### Sprint 11 – Reporting & Export

Aktivitas:

* Export alumni
* Export RSVP
* Export pembayaran
* Export donasi
* Export rooming
* Dashboard analytics

Deliverable:

* Laporan operasional tersedia

#### Sprint 12 – Stabilization & UAT

Aktivitas:

* Bug fixing
* Security testing
* Performance testing
* Backup testing
* UAT bersama panitia
* Final deployment

Deliverable:

* Sistem siap go-live

### 14.6 MVP Definition

Minimum Viable Product harus mencakup:

1. Login alumni.
2. Role pengguna.
3. Manajemen alumni.
4. Profil alumni.
5. Direktori alumni.
6. RSVP.
7. Pembayaran.
8. Donasi.
9. Rooming.
10. Public website dasar.
11. Upload foto.
12. Galeri internal.

Fitur berikut boleh menyusul setelah MVP:

* WhatsApp Analytics.
* Word cloud.
* Timeline visual lanjutan.
* Export laporan lengkap.
* Dashboard statistik lanjutan.

### 14.7 Estimasi Timeline

Dengan asumsi 1 sprint berdurasi 1 minggu:

* Minimum development: 8–10 minggu.
* Development ideal: 12 minggu.
* Testing dan stabilization: 2 minggu.
* Total ideal: 12–14 minggu.

Dengan asumsi 1 sprint berdurasi 2 minggu:

* Total ideal: 5–6 bulan.

### 14.8 Rekomendasi Tim

#### Minimum Team

* 1 Backend Laravel Developer
* 1 Frontend Developer
* 1 UI/UX Designer
* 1 Tester
* 1 Project Coordinator

#### Ideal Team

* 1 Project Manager
* 1 System Analyst
* 1 UI/UX Designer
* 2 Laravel Developer
* 1 Frontend Developer
* 1 QA Tester
* 1 DevOps Support

### 14.9 Backlog Utama

#### Authentication

* Login
* Logout
* Change password
* Change WhatsApp number

#### Alumni

* CRUD alumni
* Import alumni
* Edit profile
* Memorial profile
* Directory

#### Event

* RSVP
* Payment
* Donation
* Rooming

#### Content

* News
* Public page
* Gallery
* Video URL

#### Archive

* Memory book
* Uploaded gallery
* Tagged gallery
* Timeline
* Map

#### Analytics

* WhatsApp import
* Hall of Fame
* Word cloud
* Dashboard

#### System

* User management
* Audit log
* Backup
* Configuration

### 14.10 Acceptance Criteria

Sistem dianggap siap digunakan apabila:

1. Alumni dapat login menggunakan nomor WhatsApp dan password.
2. Alumni dapat melengkapi profil.
3. Alumni dapat mengisi RSVP.
4. Bendahara dapat mengelola pembayaran.
5. Bendahara dapat mengelola donasi.
6. Administrator dapat mengelola data alumni.
7. Administrator dapat mengelola rooming.
8. Alumni dapat mengunggah foto.
9. Alumni dapat melihat galeri internal.
10. Website publik dapat diakses tanpa login.
11. Data sensitif tidak tampil pada halaman publik.
12. Backup database berhasil berjalan.
13. Role dan hak akses berjalan sesuai rancangan.
14. Sistem berhasil melewati UAT.

### 14.11 Risiko Pengembangan

#### Risiko Requirement Berubah

Mitigasi:

* Gunakan blueprint sebagai baseline.
* Setiap perubahan dicatat sebagai change request.

#### Risiko Data Alumni Tidak Lengkap

Mitigasi:

* Siapkan template import.
* Administrator mengisi data awal.
* Alumni melengkapi sendiri.

#### Risiko Upload Foto Membesar

Mitigasi:

* Resize dan kompresi otomatis.
* Batasi ukuran upload.
* Monitoring storage.

#### Risiko WhatsApp Analytics Kompleks

Mitigasi:

* Jadikan fitur ini fase terpisah.
* Mulai dari statistik sederhana.
* Tambahkan fitur lanjutan belakangan.

#### Risiko Keterlambatan Development

Mitigasi:

* Prioritaskan MVP.
* Tunda fitur non-kritis.
* Lakukan UAT bertahap.

### 14.12 Go-Live Checklist

Sebelum production:

1. Domain aktif.
2. SSL aktif.
3. Database production siap.
4. Storage siap.
5. Backup berjalan.
6. Admin user dibuat.
7. Role dibuat.
8. Data alumni awal diimport.
9. Login diuji.
10. Upload foto diuji.
11. RSVP diuji.
12. Payment dan donation diuji.
13. Rooming diuji.
14. Hak akses diuji.
15. Public website diuji.
16. UAT selesai.
17. Dokumentasi penggunaan tersedia.

### 14.13 Post Go-Live Plan

Setelah go-live:

#### Minggu Pertama

* Monitoring error.
* Memperbaiki bug kritis.
* Mendampingi admin dan bendahara.

#### Bulan Pertama

* Evaluasi penggunaan.
* Tambah fitur kecil.
* Optimasi UI/UX.

#### Setelah Acara

* Kurasi dokumentasi.
* Publish galeri publik.
* Lengkapi buku kenangan.
* Simpan backup permanen.
* Ubah fokus sistem menjadi arsip digital.

### 14.14 Kesimpulan Roadmap

Pengembangan sistem sebaiknya difokuskan terlebih dahulu pada fitur inti yang mendukung pelaksanaan reuni.

Fitur dokumentasi, peta alumni, timeline, dan WhatsApp Analytics merupakan fitur pembeda yang memberikan nilai nostalgia dan arsip digital jangka panjang.

Dengan roadmap ini, tim developer dapat mengerjakan sistem secara bertahap, terukur, dan tetap menjaga prioritas utama agar sistem siap digunakan sebelum acara reuni berlangsung.
