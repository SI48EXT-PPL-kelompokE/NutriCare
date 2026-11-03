# CONTRACT: Paket 4 NutriCare

## 1. Pemetaan Model & Data Aktual

| Entitas / Tabel | Kolom Kunci Eksisting | Kolom Tambahan Paket 4 (Additive) | Keterangan Relasi |
|---|---|---|---|
| `jadwal_posyandu` | `id`, `user_id`, `judul`, `date`, `hour`, `location`, `kuota_jadwal` | `jam_mulai` (time), `jam_selesai` (time), `status` (enum/string: aktif, dibatalkan) | `user_id` -> `users.id` (Nakes penanggung jawab) |
| `reservasi` | `id`, `jadwal_id`, `orang_tua_id`, `balita_id`, `status`, `batas_reschedule` | - | Relasi ke `jadwal_posyandu`, `orang_tua`, `balita` |
| `presensi` | `id`, `jadwal_id`, `balita_id`, `presence_status` | - | Unique `['jadwal_id', 'balita_id']` |
| `surat_rujukan` | `id`, `balita_id`, `user_id`, `nomor_surat`, `faskes_tujuan`, `status_follow_up`, `alasan`, `qr_verification_code` | - | Unique `nomor_surat`, `qr_verification_code` |

## 2. Aturan Bisnis PBI 11 (Jadwal Posyandu)

1. **Interval Waktu**:
   * Jadwal memiliki waktu mulai (`jam_mulai` / `starts_at`) dan waktu selesai (`jam_selesai` / `ends_at`).
   * Validasi: `jam_selesai > jam_mulai`.
   * Format penyimpanan: Time/Datetime dengan timezone `Asia/Jakarta`.
2. **Aturan Bentrok Nakes**:
   * Bentrok terjadi jika untuk nakes yang sama pada tanggal yang sama: `existing.jam_mulai < new.jam_selesai` DAN `existing.jam_selesai > new.jam_mulai`.
   * Model interval setengah terbuka `[mulai, selesai)`: jika jadwal A selesai pukul 10:00 dan jadwal B mulai tepat pukul 10:00, ini **TIDAK** bentrok (diperbolehkan).
   * Pada operasi Update, pengecekan bentrok mengecualikan ID jadwal yang sedang diedit (`id != current.id`).
   * Jadwal berstatus `dibatalkan` tidak memblokir interval.
3. **Konkurensi & Locking**:
   * Pengecekan bentrok dan penyimpanan dilakukan di dalam transaksi database (`DB::transaction`).
   * Titik serialisasi locking: Mengunci baris `User` (nakes penanggung jawab) menggunakan `User::where('id', $nakesId)->lockForUpdate()->first()` untuk mencegah *race condition* dua pembuat jadwal yang memilih nakes sama di waktu bersamaan.
4. **Hak Akses & Otorisasi**:
   * Mengacu pada modul `jadwal` di tabel `permissions`:
     * Role `Admin / Kepala Puskesmas` dan `Kader Posyandu`: `can_view`, `can_create`, `can_edit`, `can_delete`.
     * Role `Warga / Orang Tua`: `can_view` saja.
     * Nakes: `can_view` jadwal terkait.
5. **Jadwal Terisi**:
   * Kuota jadwal tidak boleh diturunkan hingga lebih kecil dari jumlah reservasi aktif yang sudah ada.
   * Pembatalan/penghapusan jadwal tidak melakukan *hard-delete cascade* yang merusak data reservasi atau presensi historis.

## 3. Acceptance Criteria PBI 11 (KF-35, KF-36)
* [AC-11-1] Admin dan Kader Posyandu dapat melihat daftar jadwal dalam tampilan kalender (FullCalendar) dan tabel.
* [AC-11-2] Admin dan Kader dapat membuat jadwal baru dengan data nakes, judul, tanggal, jam mulai, jam selesai, lokasi, dan kuota.
* [AC-11-3] Backend menolak pembuatan/pembaruan jadwal jika terjadi bentrok waktu untuk nakes yang sama.
* [AC-11-4] Update jadwal sendiri tanpa mengubah waktu/nakes tidak dianggap sebagai bentrok diri sendiri.
* [AC-11-5] Waktu selesai harus lebih besar dari waktu mulai; kuota harus berupa bilangan bulat positif.
* [AC-11-6] API/endpoint feed kalender menerima parameter rentang `start` dan `end` dan mengembalikan event JSON sesuai format FullCalendar.

