# Grafik Pertumbuhan Balita (SCRUM-81 / KF-16)

Halaman grafik pertumbuhan menampilkan kurva pertumbuhan seorang balita dari riwayat pengukuran (KMS digital). Fitur ini hanya MEMBACA hasil perhitungan z-score (KF-14) dan status gizi (KF-15); tidak menghitung ulang keduanya dan tidak mengubah skema tabel.

- Halaman grafik satu balita: `GET /balita/{balita}/pertumbuhan` (nama route `balita.pertumbuhan`).
- Halaman pemilihan anak untuk warga: `GET /kms-anak-saya` (nama route `kms.anak-saya`).

## Sumber dan acuan

- Perhitungan z-score memakai `App\Services\ZScoreService` (KF-14).
- Klasifikasi kategori memakai `App\Services\StatusGiziService` (KF-15).
- Kurva acuan WHO dihitung dari parameter L, M, S pada tabel `standard_lms`.
- Alat bantu pemantauan. Keputusan penanganan dan rujukan ditetapkan oleh tenaga kesehatan.

## Aturan data

### Sumbu X

- Indikator BB/U dan TB/U: sumbu X adalah usia dalam bulan desimal saat pengukuran.
- Rumus: `selisih hari (tanggal lahir -> tanggal ukur) / 30,4375`, dibulatkan 2 desimal.
- Usia desimal HANYA untuk posisi titik pada grafik. Z-score tetap memakai usia bulan penuh dari KF-13/14 dan tidak diubah.
- Rentang sumbu X: 0 sampai `min(60, max(24, ceil(usia_terakhir / 6) * 6 + 6))` bulan.
- Indikator BB/TB: sumbu X adalah tinggi/panjang badan (cm), sumbu Y adalah berat badan (kg).

### Tiga indikator (tab)

| Tab | Titik | Kurva acuan |
|---|---|---|
| Berat / Umur (BB/U) | (usia bulan desimal, berat kg) | `standard_lms` indikator `BB_U` sesuai jenis kelamin |
| Tinggi / Umur (TB/U) | (usia bulan desimal, tinggi cm) | `standard_lms` indikator `TB_U` bila tersedia |
| BB / TB | (tinggi cm, berat kg) | `BB_PB` bila usia pengukuran terakhir < 24 bulan, selain itu `BB_TB`, bila tersedia |

Catatan penyederhanaan: untuk indikator BB/TB dipilih satu indikator acuan (`BB_PB` atau `BB_TB`) untuk seluruh titik, bukan per titik.

### Kurva acuan

Empat garis: -3 SD, -2 SD, median (0 SD), dan +2 SD. Dihitung dari L, M, S (BUKAN dari kolom `sd_*` yang sudah dibulatkan 1 desimal):

- Bila `L != 0`: `SD(k) = M * (1 + L * S * k)^(1/L)`
- Bila `L = 0`: `SD(k) = M * exp(S * k)`

Hasil dibulatkan 2 desimal untuk tampilan. Area antara -2 SD dan +2 SD diberi arsiran lembut sebagai zona normal.

Label legenda mengikuti mockup:

- "Median WHO (0 SD)"
- "Batas Waspada (-2 SD)"
- "Rujukan (-3 SD)"
- "Batas Atas (+2 SD)"

### Kurva yang belum tersedia

Saat ini hanya `standard_lms` indikator `BB_U` yang tersedia (122 baris). Untuk indikator yang belum punya data acuan (TB_U, BB_PB, BB_TB), halaman hanya menampilkan titik hasil ukur dan catatan "Kurva acuan WHO untuk indikator ini belum tersedia." Tidak ada kurva palsu atau perkiraan.

Kurva akan muncul otomatis begitu baris `standard_lms` untuk indikator tersebut dimuat, tanpa perubahan kode. Muat data WHO (lalu jalankan seeder):

```bash
# 1. Isi standard_lms indikator yang dituju (mis. TB_U, BB_PB, BB_TB)
php artisan db:seed --class=StandardLmsSeeder

# 2. Hitung ulang z-score dan status gizi dari tabel LMS
php artisan kms:hitung-zscore --apply
php artisan kms:hitung-zscore --status --apply
```

Alur ini sama dengan yang didokumentasikan pada [STATUS_GIZI.md](STATUS_GIZI.md).

### Perilaku per keadaan data

| Keadaan | Tampilan |
|---|---|
| 0 pengukuran | Empty-state "Belum ada data pengukuran." + tombol "Input Pengukuran" (peran yang berhak). Tanpa kanvas grafik. |
| 1 pengukuran | Titik terhadap kurva acuan + catatan "Tambahkan pengukuran berikutnya untuk melihat tren pertumbuhan." |
| >= 2 pengukuran | Garis penghubung antar titik. |

### Kartu ringkasan (dari pengukuran terbaru)

- BB/U terkini: berat (kg) + z-score BB/U tersimpan + kategori BB/U. Null tampil "Belum tersedia".
- TB/U terkini: tinggi (cm) + z-score TB/U + kategori TB/U. Null tampil "Belum tersedia".
- Kenaikan terakhir: selisih berat (gram) terhadap pengukuran sebelumnya dan jarak waktunya (mis. "+300 g dalam 2 bln"). Satu pengukuran: "-".
- Status: status utama dari `status_gizi` (KF-15). Bila hasil KF-15 `lengkap = false`, ditampilkan keterangan "Penilaian sebagian" dan tidak ditulis "Bebas Stunting"; bila TB/U belum tersedia, keterangan stunting menjadi "Belum dapat dinilai".

## Arsitektur

### Backend

- `app/Services/GrowthChartService.php` — method `bangun(Balita): array` mengembalikan `balita`, `indikator` (bb_u, tb_u, bb_tb), `ringkasan`, `riwayat`, `jumlah_pengukuran`.
  - Satu query pengukuran (urut naik) dengan eager load `petugas`; satu query `standard_lms` per indikator yang dipakai. Tanpa N+1 (relasi `balita` di-set manual pada hasil query agar accessor usia tidak memicu query baru).
  - Method statis murni untuk pengujian: `usiaDesimal`, `rentangSumbuUsia`, `nilaiSD` (delegasi ke `ZScoreService::hitungSD`), `labelUsia`.
- `app/Policies/BalitaPolicy.php` — `lihatPertumbuhan(User, Balita)`.
- `app/Http/Controllers/GrafikPertumbuhanController.php` — `show()` dan `anakSaya()`.
- Route di grup middleware `auth`.

### Hak akses

- Nakes/Bidan dan Kader Posyandu: semua balita.
- Admin: semua balita (melalui `before` policy).
- Warga/Orang Tua: hanya balita milik orang tua yang `user_id`-nya sama dengan `auth()->id()`; selain itu 403.
- NIK orang tua hanya tampil untuk pengguna dengan gate `view-balita-identity` (bukan warga) di halaman ini.
- Menu "KMS Anak Saya" memakai gate `access-kms-anak-saya` (khusus Warga).

Catatan: penegakan hak akses penuh (middleware/permission buatan Paket 1) menunggu integrasi Adhani; policy pada fitur ini sudah menegakkan aturan dasar di atas.

### Frontend (Chart.js)

- `resources/js/charts/register.js` — mendaftarkan hanya komponen yang dipakai (tree-shaking): `LineController`, `LineElement`, `PointElement`, `LinearScale`, `Filler`, `Tooltip`, `Legend`.
- `resources/js/pages/growth-chart.js` — entry Vite terpisah. Dimuat hanya di halaman grafik lewat `@vite` pada `@push('scripts')` agar bundle utama tidak membengkak. Data dibaca dari atribut `data-chart` (JSON dari `@json`), bukan dari AJAX.
- Satu instance Chart; tab (Alpine) mengganti dataset dan sumbu tanpa memuat ulang halaman; instance dihancurkan sebelum dibuat ulang dan saat `beforeunload`. Sumbu X bertipe `linear` dengan `parsing: false`.
- Warna dibaca dari CSS variable token tema (`--color-primary-*`, dll) dengan fallback. Pola garis (solid/putus-putus) membedakan setiap kurva agar tidak hanya bergantung pada warna.

## Batasan dan pengembangan lanjutan

- Tanpa tabel KBM (kenaikan berat badan minimal). Kartu "Kenaikan terakhir" hanya menampilkan selisih berat dan jarak waktu; pembandingan KBM adalah pengembangan lanjutan setelah tabel acuan tersedia.
- Tanpa tombol Cetak KMS dan tanpa Ekspor CSV.
- Indikator BB/TB memakai satu indikator acuan untuk seluruh titik (penyederhanaan).
- Kurva acuan TB/U dan BB/TB menunggu tabel WHO (`standard_lms`).
- Perhitungan z-score (KF-14) dan status gizi (KF-15) tidak diubah; hanya dibaca.
- Grafik dashboard (KF-53) bukan bagian fitur ini.

## Titik integrasi

- Tautan "Lihat Grafik" pada halaman daftar/detail pengukuran (KF-13).
- Tombol "Lihat KMS" pada daftar balita (KF-10, milik Paket 1) diarahkan ke halaman grafik ini.
- Menu "KMS Anak Saya" pada sidebar untuk peran Warga.
