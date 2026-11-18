# Status Gizi Otomatis (SCRUM-78 / KF-15)

## Sumber dan cakupan

Aturan klasifikasi mengacu pada Permenkes No. 2 Tahun 2020. Tabel Lampiran belum tersedia di file unduhan yang diperiksa, sehingga ambang batas pada implementasi belum diverifikasi terhadap Lampiran. Sebelum keputusan klinis, konfirmasi ambang batas dan urutan keparahan kepada tenaga kesehatan.

Fitur ini hanya mengklasifikasikan z-score hasil ukur. Tidak menghitung IMT/U, tren pertumbuhan, atau rujukan otomatis.

## Ambang batas

| Indeks | Sangat rendah | Rendah | Normal / baik | Lebih |
|---|---|---|---|---|
| BB/U | z < -3: Berat Badan Sangat Kurang; -3 <= z < -2: Berat Badan Kurang | - | -2 <= z <= 1: Normal | z > 1: Risiko Berat Badan Lebih |
| TB/U | z < -3: Sangat Pendek; -3 <= z < -2: Pendek | - | -2 <= z <= 3: Normal | z > 3: Tinggi |
| BB/TB | z < -3: Gizi Buruk; -3 <= z < -2: Gizi Kurang | - | -2 <= z <= 1: Gizi Baik | 1 < z <= 2: Berisiko; 2 < z <= 3: Gizi Lebih; z > 3: Obesitas |

Batas ditulis inklusif pada sisi kiri dan eksklusif pada sisi kanan untuk rentang negatif, sesuai aturan `z <`, `-3 <= z < -2`, dan seterusnya. Z-score harus sudah dibulatkan dua desimal.

## Penanda utama

Urutan usulan teknis: Gizi Buruk, Sangat Pendek, Berat Badan Sangat Kurang, Gizi Kurang, Stunting, Berat Badan Kurang, Obesitas, Gizi Lebih, Risiko Gizi Lebih, Tinggi, Normal. Urutan ini perlu dikonfirmasi tenaga kesehatan.

Penanda turunan: Pendek/Sangat Pendek = Stunting; Gizi Kurang/Gizi Buruk = Wasting; BB/U Kurang/Sangat Kurang = Underweight; kategori BB/TB Berisiko/Gizi Lebih/Obesitas atau risiko BB/U = Gizi Lebih.

## Data tidak lengkap

Indeks dengan z-score `null` tidak dianggap normal dan tidak ikut klasifikasi. Tampilan menampilkan "Penilaian sebagian" beserta indeks yang belum tersedia. Jika semua z-score `null`, status disimpan `null`.

Nilai di luar rentang kewajaran (BB/U -6..5, TB/U -6..6, BB/TB -5..5) menampilkan peringatan "Periksa kembali data pengukuran".

## Batasan dan data tambahan

- Belum ada klasifikasi IMT/U.
- Belum ada tren pertumbuhan.
- Tidak ada rujukan otomatis.
- Tabel LMS TB_U dan BB_TB menunggu data WHO.
- Z-score TB/U dan BB/TB pada data seed saat ini memakai nilai dummy dan statusnya berasal dari z-score dummy tersebut hingga tabel LMS dimuat.

## Menambah tabel LMS

Tambahkan baris LMS pada tabel `standard_lms` dengan indikator, jenis kelamin, dan `acuan_nilai` yang sesuai, lalu jalankan ulang perhitungan z-score. `ZScoreService` dapat menggunakan indikator `TB_U` dan `BB_TB`; klasifikasi kemudian membaca z-score tersimpan dan tidak menghitung ulang z-score.

Alur setelah mengisi tabel LMS (mis. TB_U dan BB_TB dari WHO):

```bash
# 1. Hitung ulang z-score seluruh pengukuran dari tabel LMS
php artisan kms:hitung-zscore --apply

# 2. Hitung ulang status gizi dari z-score tersimpan (dry-run dulu, lalu apply)
php artisan kms:hitung-zscore --status
php artisan kms:hitung-zscore --status --apply
```

`kms:hitung-zscore --status` bersifat dry-run secara default (tidak menulis) dan mencetak ringkasan perubahan. Tambahkan `--apply` untuk menulis. Opsi `--balita=<id>` membatasi proses pada satu balita.

## Titik sambung KF-44 (saran rujukan)

Fitur saran rujukan (KF-44) dapat memanfaatkan scope `PengukuranKms::perluPerhatian()` untuk mengambil pengukuran yang statusnya bukan Normal/Tinggi/null. Status gizi juga tersedia melalui enum `App\Enums\StatusGizi` pada atribut `status_gizi` dan accessor `hasil_status`. KF-44 TIDAK diimplementasikan di sini dan keputusan rujukan tetap oleh tenaga kesehatan.
