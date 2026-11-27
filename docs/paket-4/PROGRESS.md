# PROGRESS: Paket 4 NutriCare

## Checklist PBI 11: Jadwal Agenda Posyandu
- [x] **11A**: Model, migration additive, dan fixture jadwal
- [x] **11B**: Aturan bentrok interval, serialisasi locking nakes, transaksi DB
- [x] **11C**: Request validation, controller tipis, policy/permission, routes
- [x] **11D**: UI CRUD dan kalender FullCalendar 6 interaktif
- [x] **11E**: Pengujian otomatis (Unit & Feature Test PHPUnit) — **17/17 PASSED**
- [x] **11F**: Review keselarasan KF-35/36 dan persiapan PR — **Commit `51fb354` di branch `syifa`**

## Checklist PBI 12: Presensi Kunjungan Posyandu via QR
- [x] **12A**: Model & Factory Presensi (`database/factories/PresensiFactory.php`)
- [x] **12B**: Service `checkInViaQr`, penanganan scan berulang idempotent, transaksi & locking, rekap kehadiran (`app/Services/PresensiService.php`)
- [x] **12C**: Policy otorisasi Kader & Admin (`app/Policies/PresensiPolicy.php`)
- [x] **12D**: Controller & endpoint check-in QR, koreksi status, hapus (`app/Http/Controllers/PresensiController.php`)
- [x] **12E**: UI dua kolom: scanner kamera `html5-qrcode` & kartu identitas hasil scan, serta tabel kehadiran (`resources/views/operasional/presensi/index.blade.php`)
- [x] **12F**: Pengujian Unit & Feature — **15/15 PASSED**

## Checklist PBI 13: Reservasi Kunjungan & Reschedule
- [x] **13A**: Model & Factory Reservasi (`database/factories/ReservasiFactory.php`, `canReschedule()`, `isActive()`)
- [x] **13B**: Service pengelolaan kuota atomik, pencegahan double booking, dan pemindahan slot reschedule atomik (`app/Services/ReservasiService.php`)
- [x] **13C**: Policy otorisasi Warga, Kader, Admin (`app/Policies/ReservasiPolicy.php`)
- [x] **13D**: Controller & Form Requests (`StoreReservasiRequest`, `RescheduleReservasiRequest`, `ReservasiController`)
- [x] **13E**: UI daftar reservasi, form pendaftaran kuota, dan detail/reschedule (`resources/views/operasional/reservasi/`)
- [x] **13F**: Pengujian Unit & Feature — **16/16 PASSED**

## Checklist PBI 14: CRUD Surat Rujukan & Rekomendasi KMS
- [x] **14A**: Service nomor surat otomatis atomik sekuensial (`SR-YYYYMMDD-XXXX`), UUID QR verifikasi, dan penyaringan saran balita perlu dirujuk berdasarkan KMS (`app/Services/SuratRujukanService.php`)
- [x] **14B**: Policy otorisasi Nakes/Bidan dan Admin (`app/Policies/SuratRujukanPolicy.php`) & registrasi di `AppServiceProvider`
- [x] **14C**: Form Request validasi (`StoreSuratRujukanRequest`, `UpdateSuratRujukanRequest`)
- [x] **14D**: Controller CRUD, PDF stream/download, dan verifikasi publik via QR (`app/Http/Controllers/SuratRujukanController.php`)
- [x] **14E**: Factory Surat Rujukan (`database/factories/SuratRujukanFactory.php`)
- [x] **14F**: Template cetak PDF ber-KOP resmi dan QR verifikasi (`resources/views/operasional/rujukan/pdf.blade.php`) serta halaman publik verifikasi (`resources/views/operasional/rujukan/verify.blade.php`)
- [x] **14G**: UI daftar rujukan, widget saran rujukan KMS, form penerbitan, dan detail (`resources/views/operasional/rujukan/`)
- [x] **14H**: Pengujian Unit & Feature — **19/19 PASSED**

---

## Log Riwayat Pengerjaan
* **2026-11-03**:
  * [00A-00C] Inisialisasi Paket 4. PBI 11 selesai (17/17 test passed).
* **2026-11-18**:
  * Resolusi merge conflict integrasi `origin/main` (Providers, Factories, Layout, Routes).
  * **PBI 12 SELESAI**:
    * Check-in QR balita tanpa input identitas manual via `html5-qrcode`.
    * Validasi server terhadap integritas balita, jadwal aktif, dan status kehadiran.
    * Penanganan scan berulang secara konsisten & idempotent tanpa menambah baris baru.
    * Koreksi status dan penghapusan presensi oleh Kader/Admin.
    * 15 skenario test (Unit & Feature) lulus 100%.
  * **PBI 13 SELESAI**:
    * Pendaftaran reservasi kuota dengan proteksi transaksi & pessimistic lock (mencegah overbooking).
    * Validasi kepemilikan balita untuk warga (hanya balita dalam keluarga sendiri).
    * Reschedule atomik dengan urutan lock deterministik (mencegah deadlock).
    * Batas waktu reschedule maksimal H-1 kegiatan (`canReschedule()`).
    * Pembatalan reservasi yang mengembalikan sisa kuota jadwal secara konsisten.
    * 16 skenario test (Unit & Feature) lulus 100%.
  * **PBI 14 SELESAI**:
    * Pembangkitan nomor surat rujukan otomatis (`SR-YYYYMMDD-XXXX`) dengan serialisasi transaksi & `lockForUpdate()`.
    * Pembuatan surat rujukan oleh Nakes/Bidan dengan faskes tujuan dan indikasi klinis.
    * Integrasi rekomendasi rujukan balita berbasis antropometri/KMS digital eksisting (`status_gizi` dan z-score kritis) tanpa mengarang ambang batas baru.
    * Ekspor dokumen cetak PDF (Kop resmi puskesmas, data balita & ortu, data antropometri, dan tanda tangan).
    * QR Code verifikasi dokumen dan halaman publik otentikasi keaslian dokumen (`/verifikasi-rujukan/{code}`).
    * 19 skenario test (6 Unit + 13 Feature) lulus 100%.
  * **Total pengujian Paket 4 (PBI 11–14)**: 67/67 test passed (180 assertions).
