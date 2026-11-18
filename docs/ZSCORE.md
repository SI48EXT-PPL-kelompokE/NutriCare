# Dokumentasi Z-Score (KF-14)

## Gambaran Umum

Modul z-score menghitung indeks antropometri balita menggunakan metode LMS (Lambda-Mu-Sigma) WHO untuk menilai status pertumbuhan. Z-score dihitung otomatis saat data pengukuran KMS disimpan atau diperbarui.

## Sumber Data

- **Standar Acuan**: WHO Child Growth Standards 2006
- **Dasar Hukum**: Selaras dengan Permenkes No. 2 Tahun 2020 tentang Standar Antropometri Anak
- **Data**: Tabel LMS disimpan di `database/seeders/data/standard_lms_*.csv`

## Rumus Perhitungan

### Rumus LMS WHO

Untuk menghitung z-score dari nilai pengukuran (y):

```
Jika L ≠ 0:  Z = ((y / M)^L - 1) / (L * S)
Jika L = 0:  Z = ln(y / M) / S
```

Dimana:
- **L**: Parameter transformasi Box-Cox
- **M**: Median (nilai pada z = 0)
- **S**: Koefisien variasi

### Perhitungan Nilai SD

Untuk menghitung nilai pada z-score tertentu (k):

```
Jika L ≠ 0:  SD(k) = M * (1 + L*S*k)^(1/L)
Jika L = 0:  SD(k) = M * exp(S*k)
```

### Koreksi Nilai Ekstrem WHO

**⚠️ PENTING**: Koreksi nilai ekstrem adalah metode statistik WHO untuk menandai data outlier (di luar ±3 SD). Nilai ekstrem **HARUS dikonfirmasi** oleh tenaga kesehatan profesional karena dapat menunjukkan:
- Kesalahan pengukuran
- Kondisi medis serius
- Data pencilan (outlier)

Untuk indikator berbasis **berat badan** (BB_U, BB_PB, BB_TB), jika hasil perhitungan z > 3 atau z < -3, diterapkan koreksi:

```
Jika Z > 3:   Z = 3 + (y - SD(3)) / (SD(3) - SD(2))
Jika Z < -3:  Z = -3 + (y - SD(-3)) / (SD(-2) - SD(-3))
```

Untuk indikator **tinggi/panjang badan** (TB_U), koreksi **TIDAK diterapkan**.

Pengaturan koreksi per indikator dapat diubah di `config/zscore.php`:

```php
'extreme_adjustment' => [
    'BB_U' => true,   // Berat badan menurut umur
    'TB_U' => false,  // Tinggi/panjang badan menurut umur
    'BB_PB' => true,  // Berat badan menurut panjang badan
    'BB_TB' => true,  // Berat badan menurut tinggi badan
],
```

**Status**: Koreksi ekstrem saat ini **AKTIF** untuk semua indikator berbasis berat badan. Jika diperlukan perubahan kebijakan, edit file konfigurasi di atas.

## Indikator yang Dihitung

### 1. BB/U - Berat Badan menurut Umur

- **Acuan**: Usia saat ukur (bulan bulat, 0-60)
- **Nilai**: Berat badan (kg)
- **Keterangan**: Menilai kecukupan berat badan dibanding usia

### 2. TB/U - Tinggi/Panjang Badan menurut Umur

- **Acuan**: Usia saat ukur (bulan bulat, 0-60)
- **Nilai**: Tinggi/panjang badan (cm)
- **Keterangan**: Menilai pertumbuhan linear (tinggi badan)
- **Status**: Tabel TB_U **belum dimuat**, z_score_tbu = `null`

### 3. BB/PB atau BB/TB - Berat Badan menurut Tinggi/Panjang Badan

- **BB/PB**: Untuk usia < 24 bulan (panjang badan berbaring)
- **BB/TB**: Untuk usia ≥ 24 bulan (tinggi badan berdiri)
- **Acuan**: Tinggi/panjang badan (cm, dengan interpolasi linear)
- **Nilai**: Berat badan (kg)
- **Keterangan**: Menilai proporsionalitas berat terhadap tinggi
- **Status**: Tabel BB_PB dan BB_TB **belum dimuat**, z_score_bbtb = `null`

## Aturan Pemilihan Data Acuan

### Usia

- Usia dihitung sebagai **bulan kalender penuh** (dibulatkan ke bawah)
- Menggunakan accessor `usia_bulan` dari model `PengukuranKms`
- Rentang valid: 0-60 bulan
- Di luar rentang: z-score = `null`

### Jenis Kelamin

- Laki-laki (L) dan Perempuan (P) memiliki tabel acuan berbeda
- Diambil dari kolom `jenis_kelamin` pada tabel `balita`

### Tinggi/Panjang Badan untuk BB/PB dan BB/TB

- Tabel LMS memiliki langkah acuan tinggi badan yang **tidak bulat** (0,1 atau 0,5 cm)
- Jika tinggi badan tepat sama dengan satu baris: gunakan baris itu
- Jika tinggi badan di antara dua baris: **interpolasi linear** parameter L, M, S
- Jika tinggi badan di luar rentang tabel: z-score = `null`

**Contoh Interpolasi**:
```
Tinggi 50,5 cm berada di antara 50,0 cm dan 51,0 cm
Ratio = (50,5 - 50,0) / (51,0 - 50,0) = 0,5

L_interpolasi = L_50 + (L_51 - L_50) * 0,5
M_interpolasi = M_50 + (M_51 - M_50) * 0,5
S_interpolasi = S_50 + (S_51 - S_50) * 0,5
```

## Pembulatan

- Hasil akhir dibulatkan **2 desimal** dengan metode "half away from zero"
- Tidak ada pembulatan di langkah antara
- Nilai "-0,00" dinormalkan menjadi "0,00"

**Contoh**:
```
Z = 1.235 → 1.24
Z = -2.125 → -2.13
Z = -0.004 → 0.00
```

## Batasan Implementasi

### 1. Usia Dibulatkan ke Bawah

Usia dihitung sebagai bulan kalender penuh, bukan usia eksak dalam hari. Misalnya:
- Lahir: 15 Januari 2024
- Ukur: 20 Februari 2024
- Usia: 1 bulan (bukan 1,17 bulan)

### 2. Tidak Ada Koreksi 0,7 cm

WHO merekomendasikan menambah 0,7 cm pada panjang badan berbaring untuk menyesuaikan dengan tinggi badan berdiri. **Koreksi ini TIDAK diterapkan** karena:
- Cara pengukuran (berbaring/berdiri) tidak disimpan di database
- Konsistensi dengan data historis yang sudah ada

**Implikasi**: Hasil z-score BB/PB dan BB/TB bisa sedikit berbeda dari kalkulasi manual WHO yang menerapkan koreksi.

### 3. Tabel LMS Belum Lengkap

Saat ini **hanya BB_U** yang sudah dimuat. Indikator lain menghasilkan `null`:
- `z_score_tbu` = `null` (tabel TB_U belum dimuat)
- `z_score_bbtb` = `null` (tabel BB_PB dan BB_TB belum dimuat)

Lihat bagian "Menambah Indikator Baru" untuk cara memuatnya.

## Menambah Indikator Baru

Untuk menambahkan tabel LMS indikator lain (TB_U, BB_PB, BB_TB):

### 1. Siapkan File CSV

Buat file `database/seeders/data/standard_lms_<indikator>.csv` dengan format:

```csv
indikator,jenis_kelamin,umur_bulan atau acuan_nilai,l,m,s,sd_min3,sd_min2,sd_min1,sd_0,sd_plus1,sd_plus2,sd_plus3
TB_U,L,0,0.xxxx,xx.xxxx,0.xxxxx,xx.x,xx.x,xx.x,xx.x,xx.x,xx.x,xx.x
...
```

**Catatan**:
- Kolom `umur_bulan` atau `acuan_nilai` bisa digunakan (keduanya dipetakan ke `acuan_nilai`)
- Untuk BB_PB/BB_TB, kolom ketiga adalah tinggi badan (cm)
- Nilai `l`, `m`, `s` harus presisi 5 desimal (sesuai WHO)
- Nilai `sd_*` dibulatkan 1 desimal (untuk verifikasi, bukan perhitungan)

### 2. Jalankan Seeder

Seeder otomatis membaca **semua** file `standard_lms_*.csv`:

```bash
php artisan db:seed --class=StandardLmsSeeder
```

Atau pada migrate fresh:

```bash
php artisan migrate:fresh --seed
```

### 3. Hitung Ulang Z-Score

Setelah tabel baru dimuat, hitung ulang z-score yang sebelumnya `null`:

```bash
# Dry-run dulu untuk melihat ringkasan
php artisan kms:hitung-zscore

# Terapkan perubahan
php artisan kms:hitung-zscore --apply
```

## Command: `php artisan kms:hitung-zscore`

Command untuk menghitung ulang z-score dari data pengukuran yang sudah ada.

### Opsi

```bash
php artisan kms:hitung-zscore [opsi]

Opsi:
  --apply           Terapkan perubahan ke database (default: dry-run)
  --balita=ID       Batasi pada satu balita
  --no-interaction  Lewati konfirmasi (untuk automation)
```

### Mode Dry-Run (Default)

Tanpa `--apply`, command hanya menampilkan **ringkasan** tanpa mengubah data:
- Total pengukuran
- Jumlah z_score_bbu yang terhitung dan null
- Selisih maksimum dan rata-rata dengan nilai tersimpan
- Jumlah selisih > 0,03 (threshold kewajaran)
- Proyeksi z_score_tbu dan z_score_bbtb yang akan menjadi null

**Contoh**:
```bash
php artisan kms:hitung-zscore
```

### Mode Apply

Dengan `--apply`, command **menulis ulang** ketiga kolom z-score:

**⚠️ PERINGATAN**:
- Nilai dummy lama `z_score_tbu` dan `z_score_bbtb` akan dikosongkan jika tabelnya belum dimuat
- Akan diminta konfirmasi (kecuali dengan `--no-interaction`)

**Contoh**:
```bash
# Dengan konfirmasi
php artisan kms:hitung-zscore --apply

# Untuk automation (lewati konfirmasi)
php artisan kms:hitung-zscore --apply --no-interaction
```

### Batasi Satu Balita

Untuk menghitung ulang z-score balita tertentu:

```bash
php artisan kms:hitung-zscore --balita=123 --apply
```

### Output Contoh

```
===========================================
  COMMAND: Hitung Ulang Z-Score KMS
===========================================

📊 MODE: DRY-RUN (tidak mengubah database)
    Gunakan --apply untuk menerapkan perubahan

Memuat tabel LMS ke memori...
✓ Tabel LMS dimuat

Total pengukuran: 150

 150/150 [============================] 100% Memproses...

===========================================
  RINGKASAN HASIL
===========================================

Total pengukuran diproses: 150

Z-score BB/U (Berat Badan menurut Umur):
  ✓ Terhitung: 145
  ✗ Null (data acuan tidak tersedia): 5

Selisih dengan nilai tersimpan:
  Maksimum: 0.0283 SD
  Rata-rata: 0.0052 SD
  Selisih > 0,03: 0 pengukuran

⚠ Z-score TB/U dan BB/TB (tabel belum dimuat):
  TB/U akan menjadi null: 150
  BB/TB akan menjadi null: 150

💡 Gunakan opsi --apply untuk menerapkan perubahan
```

## Membuat Ulang Fixture Uji

Untuk memverifikasi implementasi z-score secara independen:

### 1. Jalankan Skrip Python

```bash
cd tests/Fixtures
python generate_zscore_cases.py
```

Skrip ini:
- Membaca data LMS dari `database/seeders/data/standard_lms_bb_u.csv`
- Mengimplementasi rumus LMS secara independen (bukan menyalin kode PHP)
- Menghasilkan 144 kasus uji untuk berbagai usia, jenis kelamin, dan z-score target
- Menyimpan hasil ke `tests/Fixtures/zscore_expected.json`

### 2. Jalankan Test

```bash
php artisan test --filter=test_verifikasi_dengan_fixture_json
```

Test membandingkan hasil service PHP dengan hasil Python (toleransi 0,01).

## Integrasi dengan Controller

Z-score dihitung otomatis pada:

### 1. Store Pengukuran (POST /kms)

```php
$data['z_score_bbu'] = $zScores['z_score_bbu'];
$data['z_score_tbu'] = $zScores['z_score_tbu'];
$data['z_score_bbtb'] = $zScores['z_score_bbtb'];

PengukuranKms::create($data);
```

### 2. Update Pengukuran (PUT /kms/{id})

Setiap kali berat badan, tinggi badan, atau tanggal diubah, z-score **dihitung ulang**.

### Error Handling

Jika perhitungan gagal (misalnya usia di luar rentang), penyimpanan **tetap berhasil** dengan z-score = `null`. Error dicatat ke log:

```php
Log::warning('Gagal menghitung z-score saat store pengukuran', [
    'error' => $e->getMessage(),
    'data' => $data,
]);
```

## Keamanan

### Input User

Kolom `z_score_bbu`, `z_score_tbu`, `z_score_bbtb` **TIDAK** bisa diisi langsung dari request user:
- Tidak ada di `PengukuranKmsRequest` validation rules
- Tidak ada di `$fillable` model
- Hanya diisi dari `ZScoreService`

### Mass Assignment

Model `PengukuranKms` hanya mengizinkan mass-assign:
```php
$fillable = ['balita_id', 'user_id', 'date', 'berat_badan', 'tinggi_badan', 'lingkar_lengan_atas'];
```

Kolom z-score harus diisi eksplisit:
```php
$pengukuran->z_score_bbu = $zScores['z_score_bbu'];
$pengukuran->save();
```

## Kinerja

Optimasi yang diterapkan:

### 1. Singleton Service

`ZScoreService` didaftarkan sebagai singleton di `AppServiceProvider` untuk caching optimal dalam satu request.

### 2. Lookup dengan Indeks

Pencarian data LMS menggunakan indeks unik `(indikator, jenis_kelamin, acuan_nilai)` untuk query cepat.

### 3. Memori Cache

Service menyimpan hasil lookup di array internal untuk menghindari query berulang dalam satu instans.

### 4. Batch Processing

Method `muatSemua()` memuat seluruh tabel LMS ke memori sekali untuk command `kms:hitung-zscore` (chunking 200 baris).

### 5. Chunk Processing

Command menggunakan `chunkById(200)` untuk memproses data massal tanpa membanjiri memori.

### Target Kinerja (KNF-01)

- ✅ 5.000 perhitungan setelah `muatSemua()`: < 1 detik
- ✅ Store pengukuran HTTP: < 2 detik
- ✅ Query ke `standard_lms` per store: ≤ 3 query

## Testing

### Unit Test (`tests/Unit/ZScoreServiceTest.php`)

- Test rumus LMS tanpa database
- Verifikasi pembulatan dan normalisasi "-0.00"
- Test koreksi nilai ekstrem
- Verifikasi dengan fixture JSON (144 kasus dari Python)

### Feature Test (`tests/Feature/ZScoreIntegrationTest.php`)

- Lookup per jenis kelamin dan umur
- Kewajaran terhadap kolom SD tabel WHO
- Interpolasi BB_PB/BB_TB
- Integrasi dengan HTTP store/update
- Command dry-run dan apply
- Test kinerja (5.000 perhitungan, waktu HTTP, jumlah query)

### Browser Test (`tests/Browser/ZScoreTest.php`)

- Alur lengkap: login, tambah pengukuran, lihat z-score di detail

### Menjalankan Test

```bash
# Semua test
php artisan test

# Hanya unit test z-score
php artisan test --filter=ZScoreServiceTest

# Hanya feature test z-score
php artisan test --filter=ZScoreIntegrationTest

# Browser test (perlu ChromeDriver)
php artisan dusk --filter=ZScoreTest
```

## Troubleshooting

### Z-score selalu null

**Penyebab**:
- Tabel `standard_lms` kosong atau tidak sesuai
- Usia di luar 0-60 bulan
- Indikator belum dimuat (TB_U, BB_PB, BB_TB)

**Solusi**:
```bash
# Cek jumlah baris
SELECT COUNT(*) FROM standard_lms;  # Harus >= 122 (BB_U saja)

# Re-seed
php artisan db:seed --class=StandardLmsSeeder
```

### Selisih z-score > 0,03 dengan nilai lama

**Penyebab**:
- Data lama menggunakan berat badan yang dibulatkan terlalu kasar
- Rumus LMS lama tidak presisi

**Solusi**: Ini normal. Command `kms:hitung-zscore` akan menampilkan jumlah selisih besar. Jika > 10% pengukuran, periksa data seed lama.

### Command "Class StandardLmsSeeder not found"

**Penyebab**: Composer autoload belum diperbarui.

**Solusi**:
```bash
composer dump-autoload
```

### Test "Fixture zscore_expected.json belum ada"

**Penyebab**: Belum menjalankan skrip Python.

**Solusi**:
```bash
cd tests/Fixtures
python generate_zscore_cases.py
```

## Referensi

- [WHO Child Growth Standards 2006](https://www.who.int/tools/child-growth-standards)
- [WHO Anthro Software](https://www.who.int/tools/child-growth-standards/software)
- [Permenkes No. 2 Tahun 2020](https://peraturan.bpk.go.id/Details/163049/permenkes-no-2-tahun-2020)
- LMS Method: Cole TJ, Green PJ. Smoothing reference centile curves: the LMS method and penalized likelihood. Stat Med. 1992;11(10):1305-1319.

---

**Pemilik Modul**: Zaki (Paket 2 - Rekam Medis & Klinis)  
**Jira Story**: SCRUM-75 [KF-14]  
**Tanggal Pembaruan**: 18 November 2026
