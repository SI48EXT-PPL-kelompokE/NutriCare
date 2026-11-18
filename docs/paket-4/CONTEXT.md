# CONTEXT: Paket 4 NutriCare (Operasional & Rujukan)

## 1. Identitas & Batas Kepemilikan
* **Pengembang**: Syifa Dwi Yulia Hasanah (NIM: 102022580047)
* **Modul**: Paket 4: Operasional & Rujukan
* **Cakupan PBI**:
  * PBI 11: Jadwal Posyandu (KF-35, KF-36)
  * PBI 12: Presensi Balita via QR (KF-37, KF-38)
  * PBI 13: Reservasi Kunjungan Posyandu (KF-39 s.d. KF-41)
  * PBI 14: Surat Rujukan Digital (KF-42 s.d. KF-44)
* **Batasan Modul**:
  * Tidak membuat/mengubah model/migration user/orang tua/balita tandingan (milik Adhani / Paket 1).
  * Tidak menghitung ulang rumus LMS KMS atau mengubah ambang klinis (milik Zaki / Paket 2).
  * Tidak mengubah modul konseling/dashboard tanpa koordinasi (milik Permata / Paket 5).

## 2. Stack & Versi Repo Aktual (Baseline origin/main)
* **Framework**: Laravel 11.x, PHP ^8.2 (Lokal PHP 8.1.25)
* **Database**: MySQL 8.0 / MariaDB (InnoDB, utf8mb4)
* **Aset & Frontend**: Tailwind CSS v4, Vite 5, FullCalendar 6.1.22, html5-qrcode 2.3.8
* **PDF & QR**: barryvdh/laravel-dompdf 3.1, simplesoftwareio/simple-qrcode 4.2
* **Pengujian**: PHPUnit 10.5, Laravel Dusk 8.7
* **Timezone**: `Asia/Jakarta`

## 3. Pola Arsitektur & Struktur Folder
* **Model**: Menggunakan nama tabel tunggal eksplisit `protected $table = 'nama_tabel';`
* **Controller**: `app/Http/Controllers/JadwalPosyanduController.php` (tipis, delegasi ke service)
* **Service**: `app/Services/JadwalPosyanduService.php` (logika bentrok, serialisasi nakes, transaksi DB)
* **Form Request**: `app/Http/Requests/StoreJadwalPosyanduRequest.php`, `UpdateJadwalPosyanduRequest.php`
* **Policy / Otorisasi**: Integrasi dengan tabel `permissions` dan `roles` milik Adhani (`can_view`, `can_create`, `can_edit`, `can_delete` untuk modul `jadwal`)
* **Views**: `resources/views/operasional/jadwal/` menggunakan layout bersama `resources/views/layouts/app.blade.php`
* **Routes**: File rute terpisah `routes/paket4.php` yang di-*include* dari `routes/web.php`
* **Migrations**: Additive migration (tidak mengubah/menghapus file migrasi yang sudah dibagikan)
* **Tests**: `tests/Unit/JadwalPosyanduTest.php` dan `tests/Feature/JadwalPosyanduTest.php`

