# NutriCare

Platform Monitoring Gizi & Intervensi Stunting Balita berbasis web untuk Posyandu dan Puskesmas.

## Latar Belakang

Stunting masih menjadi tantangan kesehatan di Indonesia. Prevalensi nasional turun dari 21,5% (2023) menjadi 19,8% (2024, SSGI), dengan target 14,2% pada 2029. Di banyak Posyandu, pencatatan dan pengelolaan data balita masih manual sehingga pemantauan dan tindak lanjut menjadi sulit. NutriCare menyatukan pencatatan, pemantauan, dan tindak lanjut gizi balita dalam satu platform terpusat.

## Tujuan

- Mendukung pemantauan pertumbuhan dan status gizi balita secara lebih sistematis.
- Mengaitkan data pertumbuhan dengan imunisasi, vitamin dan suplemen, PMT, konseling gizi, dan rujukan.
- Membantu kader Posyandu dan tenaga kesehatan dalam pemantauan dan tindak lanjut.
- Memberi orang tua akses informasi perkembangan anak dan layanan Posyandu.

## Fitur Utama

- Manajemen akun, role, dan matriks hak akses (RBAC)
- Data orang tua/ibu hamil dan balita (QR Code identitas)
- KMS digital: kalkulasi z-score metode LMS (WHO), status gizi otomatis, grafik pertumbuhan
- Riwayat imunisasi, vitamin, dan suplemen
- Logistik PMT: master paket, stok gudang (FEFO), distribusi, berita acara
- Jadwal Posyandu, presensi via scan QR, reservasi kunjungan
- Surat rujukan digital dengan QR verifikasi
- Artikel edukasi, konseling gizi, feedback dan pengaduan
- Dashboard KPI dan ekspor laporan PDF/Excel

## Pengguna

Admin/Kepala Puskesmas, Tenaga Kesehatan/Bidan, Kader Posyandu, Warga/Orang Tua.

## Batasan

NutriCare tidak menggantikan diagnosis atau keputusan medis. Perhitungan status gizi adalah alat bantu monitoring; keputusan penanganan dan rujukan tetap oleh tenaga kesehatan.

## Teknologi

PHP 8.2+, Laravel 11, MySQL 8.0 / MariaDB 10.6+, Blade + Tailwind CSS, Vite, Chart.js, FullCalendar, html5-qrcode, Quill, DomPDF, Laravel Excel, simple-qrcode, PHPUnit, Laravel Dusk.

## Instalasi (Pengembangan Lokal)

**Prasyarat:**
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL/XAMPP
- Git

**Langkah-langkah:**

```bash
# Clone repository
git clone https://github.com/SI48EXT-PPL-kelompokE/NutriCare.git
cd NutriCare

# Install dependensi
composer install
npm install

# Konfigurasi environment
cp .env.example .env
# Edit .env dan isi DB_USERNAME dan DB_PASSWORD

# Buat database
# Buat database 'nutricare' dengan charset utf8mb4_unicode_ci

# Generate application key
php artisan key:generate

# Migrasi dan seed database
php artisan migrate --seed

# Build assets
npm run dev

# Jalankan server
php artisan serve
```

Untuk detail skema database, lihat [docs/DATABASE.md](docs/DATABASE.md).

## Alur Git

- `main`: branch default, hanya menerima merge lewat Pull Request.
- Branch kerja per anggota: `zaki`, `adhani`, `fadilyas`, `syifa`, `permata`.
- Alur: `git checkout <nama-branch>` → `git pull origin main --rebase` → kerjakan → commit → `git push origin <nama-branch>` → buat Pull Request ke `main` → review minimal 1 anggota lain → merge.
- Format commit: `<KODE-ISSUE-JIRA> <deskripsi singkat>`, contoh: `SCRUM-201 Instalasi & konfigurasi proyek Laravel 11 + database`. Kode issue wajib ada agar commit otomatis terhubung ke Jira.
- Jangan pernah commit file `.env`.

## Tim Pengembang

| Nama | NIM | Paket |
|---|---|---|
| Naufal Adhani Putra | 102022580060 | Paket 1: Kependudukan & Akses |
| Zaki Affandi | 102022580028 | Paket 2: Rekam Medis & Klinis |
| Muhammad Fadilyas Fathuris Haryanto | 102022580034 | Paket 3: Logistik PMT |
| Syifa Dwi Yulia Hasanah | 102022580047 | Paket 4: Operasional & Rujukan |
| Permata Nurkhalida Hydra | 102022580020 | Paket 5: Edukasi, Layanan Warga & Dashboard |

## Metode Pengembangan

Agile Scrum, 4 sprint (masing-masing 1 minggu): Sprint 1 fondasi dan data induk, Sprint 2 modul pendukung, Sprint 3 penyelesaian PBI dan integrasi, Sprint 4 dashboard, ekspor, pengujian, dan finalisasi. Backlog dikelola di Jira.
