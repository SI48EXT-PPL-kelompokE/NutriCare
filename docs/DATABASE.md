# Dokumentasi Database NutriCare

## Daftar Tabel (22 Tabel Utama)

### 1. Tabel Pengguna & Akses
- **roles** - Peran pengguna (Admin, Nakes, Kader, Warga)
- **users** - Akun pengguna sistem
- **permissions** - Matriks hak akses per role per modul
- **password_reset_tokens** - Token reset password
- **sessions** - Sesi login pengguna

### 2. Tabel Kependudukan
- **orang_tua** - Data orang tua/wali balita
- **balita** - Data balita

### 3. Tabel Rekam Medis & Klinis
- **pengukuran_kms** - Riwayat pengukuran berat/tinggi badan dan z-score
- **imunisasi** - Riwayat imunisasi balita
- **pemberian_vitamin** - Riwayat pemberian vitamin & suplemen
- **standard_lms** - Tabel referensi WHO LMS untuk kalkulasi z-score

### 4. Tabel Logistik PMT
- **barang_pmt** - Master barang/bahan PMT
- **paket_pmt** - Paket PMT dengan target usia
- **komposisi_paket_pmt** - Komposisi bahan dalam paket
- **stok_gudang** - Stok per batch dengan exp_date (FEFO)
- **distribusi_pmt** - Distribusi PMT ke balita
- **detail_distribusi_stok** - Detail pengambilan stok per batch

### 5. Tabel Operasional & Rujukan
- **jadwal_posyandu** - Jadwal kegiatan posyandu
- **reservasi** - Reservasi kunjungan posyandu
- **presensi** - Presensi balita di posyandu
- **surat_rujukan** - Surat rujukan ke faskes lanjutan

### 6. Tabel Edukasi & Layanan Warga
- **artikel** - Artikel edukasi kesehatan
- **sesi_konseling** - Konseling gizi untuk orang tua
- **pengaduan** - Pengaduan dan feedback warga

---

## Perubahan dari ERD Original

### Penambahan Kolom [+]
- **users**: `username` (untuk login), `active_status` (status aktif/nonaktif)
- **pengukuran_kms**: tidak ada perubahan
- **barang_pmt**: `energi_kcal`, `protein`, `lemak`, `karbohidrat` (nilai gizi per satuan)
- **paket_pmt**: `total_energi_kcal`, `total_protein`, `total_lemak`, `total_karbohidrat` (dihitung otomatis)
- **jadwal_posyandu**: `judul` (nama kegiatan)
- **reservasi**: `balita_id` (agar bisa reservasi untuk balita tertentu)
- **surat_rujukan**: `alasan` (alasan rujukan), `qr_verification_code` (verifikasi digital)
- **artikel**: `konten` (isi artikel HTML), `published_at` (tanggal publikasi)
- **sesi_konseling**: `hasil` (hasil konseling)
- **pengaduan**: `nomor_tiket` (nomor unik untuk tracking)

### Perubahan Nama Kolom [~]
- **users**: `hash_password` → `password` (standar Laravel)
- **distribusi_pmt**: `package_id` → `paket_id` (konsistensi penamaan)
- **permissions**: `access` → `can_view`, `can_create`, `can_edit`, `can_delete` (lebih granular)

### Penghapusan Kolom [-]
- **distribusi_pmt**: TIDAK ada kolom `stok_id` (diganti dengan tabel `detail_distribusi_stok` untuk mendukung FEFO)

**Alasan perubahan**: Mendukung FEFO (First Expired First Out) - satu distribusi bisa mengambil dari multiple batch stok.

---

## Catatan Penting

### Nama Model Menggunakan $table Eksplisit
Karena nama tabel tidak mengikuti konvensi plural Laravel (mis. `balita`, `artikel`, `orang_tua`), setiap model WAJIB mendefinisikan:
```php
protected $table = 'nama_tabel';
```

### Urutan Seeding
Seeder dijalankan dengan urutan yang menghormati foreign key dependencies:
1. StandardLmsSeeder
2. RoleSeeder
3. UserSeeder, PermissionSeeder
4. OrangTuaSeeder
5. BalitaSeeder
6. PengukuranKmsSeeder, ImunisasiSeeder, PemberianVitaminSeeder
7. BarangPmtSeeder → PaketPmtSeeder → StokGudangSeeder → DistribusiPmtSeeder
8. JadwalPosyanduSeeder → ReservasiSeeder, PresensiSeeder
9. SuratRujukanSeeder
10. ArtikelSeeder, SesiKonselingSeeder, PengaduanSeeder

### Akun Dummy untuk Testing

| Username | Password | Role | Status |
|---|---|---|---|
| admin | password | Admin / Kepala Puskesmas | Aktif |
| bidan1 | password | Nakes / Bidan | Aktif |
| bidan2 | password | Nakes / Bidan | **Nonaktif** |
| bidan3 | password | Nakes / Bidan | Aktif |
| kader1 | password | Kader Posyandu | Aktif |
| kader2 | password | Kader Posyandu | Aktif |
| kader3 | password | Kader Posyandu | Aktif |
| warga1 - warga10 | password | Warga / Orang Tua | Aktif |

### Cara Reset Data
```bash
php artisan migrate:fresh --seed
```
**Peringatan**: Perintah ini akan menghapus SEMUA data dan membuat ulang dari seeder.

---

## Catatan Data Sementara (DUMMY)

### z_score_tbu dan z_score_bbtb
Saat ini **z_score_tbu** (Tinggi Badan menurut Umur) dan **z_score_bbtb** (Berat Badan menurut Tinggi Badan) diisi dengan angka DUMMY acak yang masuk akal, BUKAN hasil perhitungan rumus LMS.

**Alasan**: Tabel `standard_lms` saat ini hanya berisi data LMS untuk indikator **BB_U** (Berat Badan menurut Umur) dari WHO.

**Catatan**: Jika di kemudian hari tabel LMS untuk TB_U dan BB_TB ditambahkan, perhitungan z-score di `PengukuranKmsSeeder` perlu diperbarui.

### Nilai Gizi Barang PMT
Nilai gizi (`energi_kcal`, `protein`, `lemak`, `karbohidrat`) pada tabel `barang_pmt` adalah **pendekatan umum** per 100 gram, BUKAN data resmi.

**Acuan Resmi**: [Tabel Komposisi Pangan Indonesia (TKPI)](https://www.panganku.org/) - Kementerian Kesehatan RI.

Angka saat ini hanya untuk contoh dan pengujian sistem.

---

## Jumlah Data Seeder

Setelah menjalankan `php artisan migrate:fresh --seed`:

| Tabel | Jumlah Baris (approx) |
|---|---|
| standard_lms | 122 |
| roles | 4 |
| users | 17 |
| permissions | ~25 |
| orang_tua | 30 |
| balita | 45 |
| pengukuran_kms | ~150 |
| imunisasi | ~120 |
| pemberian_vitamin | ~20 |
| barang_pmt | 8 |
| paket_pmt | 4 |
| komposisi_paket_pmt | ~12 |
| stok_gudang | ~20 |
| distribusi_pmt | ~12 |
| detail_distribusi_stok | ~36 |
| jadwal_posyandu | 6 |
| reservasi | ~40 |
| presensi | ~27 |
| surat_rujukan | 5 |
| artikel | 8 |
| sesi_konseling | 8 |
| pengaduan | 10 |

---

## Database Testing

Database `nutricare_testing` digunakan untuk PHPUnit test agar tidak merusak data development.

Konfigurasi ada di `phpunit.xml` dengan env override:
```xml
<env name="DB_DATABASE" value="nutricare_testing"/>
```

Buat database testing di MySQL:
```sql
CREATE DATABASE nutricare_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
