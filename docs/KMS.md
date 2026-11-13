# Dokumentasi Modul KMS (Kartu Menuju Sehat) Digital

## SCRUM-72 [KF-13] - CRUD Pengukuran Balita

### Gambaran Umum
Modul ini mencatat hasil pengukuran antropometri balita meliputi:
- Berat Badan (BB)
- Tinggi/Panjang Badan (TB/PB)
- Lingkar Lengan Atas (LILA) - opsional

### Aturan Bisnis

#### 1. Perhitungan Usia Saat Ukur
**Rumus:** Selisih bulan kalender penuh antara `balita.birth_date` dan `tanggal ukur`, dibulatkan ke bawah.

**Implementasi:**
- Di Carbon 3, `diffInMonths()` mengembalikan `float` dan bisa bertanda
- Urutan argumen: `$birthDate->floatDiffInMonths($dateUkur)`
- Gunakan `(int) floor(max(0, $months))` agar tidak negatif

**Contoh:**
- Lahir 31 Jan, ukur 28 Feb = **0 bulan** (belum 1 bulan kalender penuh)
- Lahir 15 Jan, ukur 15 Mar = **2 bulan** (tepat 2 bulan kalender)
- Lahir 15 Jan, ukur 14 Mar = **1 bulan** (kurang 1 hari dari 2 bulan)

**Accessor Model:**
- `usia_bulan` (integer): Mengembalikan usia dalam bulan bulat
- `usia_label` (string): Format "14 bulan (1 th 2 bln)" atau "5 bulan" (< 12 bulan)

**Satu Sumber Kebenaran:**  
Accessor ini adalah satu-satunya tempat perhitungan usia. Jangan duplikasi rumus di tempat lain. KF-14 (z-score) akan menggunakan accessor ini.

#### 2. Petugas
- Kolom `user_id` **diisi otomatis** dari `auth()->id()` saat membuat data
- Tidak ada input petugas di form
- Saat edit, petugas asli **tidak berubah**
- Tampilan: nama petugas (read-only)

**Fallback Sementara (Environment Local):**
```php
// TODO: hapus setelah autentikasi (Adhani) tersedia
$data['user_id'] = auth()->id() ?? User::whereHas('role', function ($q) {
    $q->whereIn('name', ['Nakes / Bidan', 'Kader Posyandu']);
})->first()->id;
```

#### 3. Z-Score dan Status Gizi (KF-14/KF-15)
- Kolom `z_score_bbu`, `z_score_tbu`, `z_score_bbtb`, `status_gizi` **TIDAK diisi** di KF-13
- Biarkan `null`
- Tampilan sementara: pil abu "Belum dihitung"
- Komentar di controller:
  ```php
  // KF-14/KF-15: hitung z-score dan status gizi saat simpan
  ```

#### 4. Petunjuk Pengukuran (UI)
- Usia < 24 bulan: "💡 Ukur panjang badan (terlentang)"
- Usia >= 24 bulan: "💡 Ukur tinggi badan (berdiri)"
- Ini **hanya petunjuk** visual; nilai tetap disimpan di kolom `tinggi_badan`

#### 5. Hapus Data
- Hard delete (tabel tidak punya `active_status`)
- Dengan konfirmasi modal/alert

### Validasi

#### Rentang Nilai
| Field | Min | Max | Satuan | Nullable |
|-------|-----|-----|--------|----------|
| `berat_badan` | 0,5 | 30 | kg | No |
| `tinggi_badan` | 30 | 130 | cm | No |
| `lingkar_lengan_atas` | 5 | 30 | cm | Yes |

**Alasan Rentang:**
- BB 0,5-30 kg: Balita prematur hingga usia 5 tahun
- TB 30-130 cm: Bayi baru lahir hingga balita 60 bulan
- LILA 5-30 cm: Rentang normal balita

#### Aturan Tanggal
1. Tidak boleh di masa depan (`before_or_equal:today`)
2. Tidak boleh sebelum tanggal lahir balita
3. Usia saat ukur **tidak boleh > 60 bulan** (tabel standar WHO hanya sampai 60 bulan)

#### Duplikasi
Tidak boleh ada pengukuran lain untuk balita yang sama pada tanggal yang sama (abaikan record yang sedang diedit saat update).

Pesan: "Balita ini sudah memiliki pengukuran pada tanggal tersebut."

#### Normalisasi Input
`prepareForValidation()` di Request:
- Ubah koma desimal (`,`) menjadi titik (`.`)
- Trim spasi

Contoh: `"9,5"` → `9.5`

### Titik Sambung untuk KF-14 dan KF-15

#### Di Model `PengukuranKms`
```php
// Accessor usia_bulan dan usia_label sudah siap dipakai
$pengukuran->usia_bulan; // 14
$pengukuran->usia_label; // "14 bulan (1 th 2 bln)"
```

#### Di Controller (store dan update)
```php
// KF-14/KF-15: hitung z-score dan status gizi saat simpan
// Gunakan service/helper untuk hitung z-score berdasarkan usia_bulan, jenis_kelamin, BB, TB
// $data['z_score_bbu'] = ...
// $data['z_score_tbu'] = ...
// $data['z_score_bbtb'] = ...
// $data['status_gizi'] = ...
```

#### Di View (index dan show)
Ganti pil "Belum dihitung" dengan status gizi dinamis:
```blade
@if($item->status_gizi)
    <x-status-pill tone="{{ $toneMap[$item->status_gizi] }}">
        {{ $item->status_gizi }}
    </x-status-pill>
@else
    <span class="...">Belum dihitung</span>
@endif
```

### File yang Dibuat/Diubah

**Backend:**
- `app/Models/PengukuranKms.php` (accessor usia)
- `app/Http/Requests/PengukuranKmsRequest.php` (validasi)
- `app/Http/Controllers/PengukuranKmsController.php` (CRUD)
- `routes/web.php` (route `kms.*`)

**Views:**
- `resources/views/kms/index.blade.php`
- `resources/views/kms/create.blade.php`
- `resources/views/kms/edit.blade.php`
- `resources/views/kms/show.blade.php`
- `resources/views/kms/_form.blade.php`

**Testing:**
- `database/factories/PengukuranKmsFactory.php`
- `tests/Unit/UsiaSaatUkurTest.php` (10 test case)
- `tests/Feature/PengukuranKmsCrudTest.php` (23 test case)
- `tests/Browser/PengukuranKmsTest.php` (Dusk)

**Dokumentasi:**
- `docs/KMS.md` (ini)

### Route
```
GET    /kms                  kms.index      Daftar pengukuran
GET    /kms/create           kms.create     Form tambah
POST   /kms                  kms.store      Simpan
GET    /kms/{kms}            kms.show       Detail
GET    /kms/{kms}/edit       kms.edit       Form edit
PUT    /kms/{kms}            kms.update     Update
DELETE /kms/{kms}            kms.destroy    Hapus
```

### Navigasi
Tab di KMS Digital:
1. **Pengukuran** (KF-13, aktif)
2. Imunisasi (KF-17, sudah ada)
3. Vitamin & Suplemen (belum ada, placeholder)

### Permissions
Module `kms` di `PermissionSeeder`:
- **Nakes / Bidan**: CRUD (1,1,1,0)
- **Kader Posyandu**: CRUD (1,1,1,0)
- **Warga / Orang Tua**: Read-only (1,0,0,0) - untuk melihat KMS anak sendiri (story lain)

### Dependencies pada Pekerjaan Anggota Lain
1. **Autentikasi (Adhani):** Middleware `auth` dan role/permission enforcement
2. **Layout & Komponen (sudah ada):** `layouts.app`, form components, empty-state, code-chip
3. **CRUD Balita (KF-10):** Belum ada controller, tapi data seeder ada
4. **Z-Score LMS (KF-14) dan Status Gizi (KF-15):** Belum dikerjakan

### Testing

#### Unit Test (UsiaSaatUkurTest)
10 test case untuk perhitungan usia:
- Hari yang sama = 0 bulan
- Lahir 31 Jan ukur 28 Feb = 0 bulan
- Lahir 15 Jan ukur 15 Mar = 2 bulan
- Tepat 12, 14, 23, 24, 59, 60 bulan
- Lahir 29 Feb tahun kabisat

#### Feature Test (PengukuranKmsCrudTest)
23 test case:
- Nakes dan kader dapat membuka index, create, show
- Store valid menyimpan data, petugas terisi otomatis
- Store menerima desimal berkoma
- Store gagal: balita tidak ada, tanggal masa depan, tanggal sebelum lahir, usia > 60 bulan, BB/TB/LILA di luar rentang, field wajib kosong, pengukuran ganda
- Update mengubah data tanpa mengganti petugas
- Update tidak gagal oleh validasi duplikat terhadap dirinya sendiri
- Destroy menghapus data
- Pencarian, filter tanggal, filter balita_id
- Pengguna belum login ditolak, role Warga ditolak (akan diperketat setelah middleware tersedia)

#### Browser Test (PengukuranKmsTest)
Alur lengkap (Dusk):
1. Login sebagai nakes
2. Buka daftar pengukuran
3. Tambah pengukuran
4. Pratinjau usia muncul
5. Data tampil di daftar
6. Ubah data
7. Hapus dengan konfirmasi

**Catatan:** Test ini memerlukan ChromeDriver. Jika tidak tersedia, test akan di-skip.

### Verifikasi Manual

#### Checklist
- [ ] `php artisan route:list --name=kms` menampilkan semua route CRUD
- [ ] `npm run build` sukses
- [ ] `php artisan view:cache` tanpa error
- [ ] Halaman index: tab aktif, filter, pencarian, pagination
- [ ] Halaman create/edit: pratinjau usia muncul, validasi bekerja, desimal berkoma diterima
- [ ] Halaman show: detail lengkap, riwayat dengan selisih BB dan TB
- [ ] Hapus dengan konfirmasi
- [ ] Responsif 360-1920 px, tidak ada horizontal scroll pada body
- [ ] Tidak ada N+1 query pada index (eager load `balita.orangTua` dan `petugas`)
- [ ] Tidak ada CDN atau permintaan jaringan eksternal baru

#### Screenshot (Opsional)
Jika memungkinkan, ambil screenshot headless lebar 360, 768, dan 1440 px:
- Halaman index (dengan data)
- Form create (dengan pratinjau usia)
- Halaman show (detail + riwayat)

### Known Issues & Limitations
1. **Test lambat:** Unit dan Feature test memakan waktu lama (>3 menit). Perlu optimasi atau skip seeder.
2. **Middleware auth belum ada:** Fallback sementara di controller untuk `user_id`.
3. **Dusk test memerlukan ChromeDriver:** Jika tidak tersedia, test akan di-skip.
4. **Grafik pertumbuhan (KF-16):** Belum ada di halaman show, hanya tabel riwayat.

### Langkah Selanjutnya
1. **Buat Pull Request:** Branch `zaki` ke `main`
2. **Ubah status sub-task di Jira:** SCRUM-72 → Done
3. **KF-14 (Z-Score LMS):** Implementasi perhitungan z-score menggunakan accessor `usia_bulan`
4. **KF-15 (Status Gizi):** Penentuan status gizi berdasarkan z-score
5. **KF-16 (Grafik Chart.js):** Visualisasi pertumbuhan balita

---

**Developer:** Zaki (Paket 2 Rekam Medis & Klinis)  
**Tanggal:** 12 November 2026  
**Versi:** 1.0
