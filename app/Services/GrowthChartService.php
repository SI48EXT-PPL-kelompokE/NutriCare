<?php

namespace App\Services;

use App\Models\Balita;
use App\Models\PengukuranKms;
use App\Models\StandardLms;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Menyusun data grafik pertumbuhan balita (KF-16) dari riwayat pengukuran.
 *
 * Service ini hanya MEMBACA hasil z-score (KF-14) dan status gizi (KF-15);
 * tidak menghitung ulang z-score maupun mengubah skema data.
 */
class GrowthChartService
{
    /** Rata-rata jumlah hari per bulan menurut WHO. */
    public const HARI_PER_BULAN = 30.4375;

    /** Jarak usia (bulan) untuk membedakan panjang badan vs tinggi badan. */
    private const BATAS_USIA_TERLENTANG = 24;

    /** Garis kurva acuan yang ditampilkan: [z-score, label legenda]. */
    private const GARIS_KURVA = [
        [-3, 'Rujukan (-3 SD)'],
        [-2, 'Batas Waspada (-2 SD)'],
        [0, 'Median WHO (0 SD)'],
        [2, 'Batas Atas (+2 SD)'],
    ];

    /**
     * Bangun struktur data grafik siap kirim ke view.
     *
     * @return array{balita:array, indikator:array, ringkasan:array, riwayat:array, jumlah_pengukuran:int}
     */
    public function bangun(Balita $balita): array
    {
        $balita->loadMissing('orangTua');

        // Satu query pengukuran (urut naik) + eager load petugas (tanpa N+1).
        $pengukurans = PengukuranKms::where('balita_id', $balita->id)
            ->with('petugas')
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        // Pakai balita yang sudah diketahui agar accessor usia tidak memicu query baru.
        foreach ($pengukurans as $p) {
            $p->setRelation('balita', $balita);
        }

        $terakhir = $pengukurans->last();

        return [
            'balita' => $this->dataBalita($balita),
            'indikator' => [
                'bb_u' => $this->indikatorBbU($balita, $pengukurans),
                'tb_u' => $this->indikatorTbU($balita, $pengukurans),
                'bb_tb' => $this->indikatorBbTb($balita, $pengukurans),
            ],
            'ringkasan' => $this->ringkasan($balita, $pengukurans, $terakhir),
            'riwayat' => $this->riwayat($pengukurans),
            'jumlah_pengukuran' => $pengukurans->count(),
        ];
    }

    /**
     * Hitung usia desimal (bulan) antara tanggal lahir dan tanggal ukur.
     * Rumus: selisih hari / 30,4375, dibulatkan 2 desimal.
     */
    public static function usiaDesimal(\DateTimeInterface $lahir, \DateTimeInterface $ukur): float
    {
        $hari = abs((float) Carbon::parse($lahir)->diffInDays(Carbon::parse($ukur)));

        return round($hari / self::HARI_PER_BULAN, 2);
    }

    /**
     * Rentang sumbu X (bulan): 0 sampai min(60, max(24, ceil(usia/6)*6 + 6)).
     *
     * @return array{0:int,1:int}
     */
    public static function rentangSumbuUsia(float $usiaBulanTerakhir): array
    {
        $atas = (int) ceil(max(0, $usiaBulanTerakhir) / 6) * 6 + 6;
        $atas = min(60, max(24, $atas));

        return [0, $atas];
    }

    /**
     * Nilai SD pada z-score tertentu. Rumus diambil dari ZScoreService (LMS WHO).
     */
    public static function nilaiSD(float $l, float $m, float $s, float $k): float
    {
        return ZScoreService::hitungSD($l, $m, $s, $k);
    }

    private function dataBalita(Balita $balita): array
    {
        $lahir = $balita->birth_date;
        $usiaSekarang = $lahir ? self::usiaDesimal($lahir, Carbon::now()) : null;

        return [
            'id' => $balita->id,
            'nama' => $balita->name,
            'inisial' => mb_strtoupper(mb_substr((string) $balita->name, 0, 2)),
            'jenis_kelamin' => $balita->jenis_kelamin,
            'jenis_kelamin_label' => $balita->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
            'tanggal_lahir' => $lahir?->toDateString(),
            'tanggal_lahir_label' => $lahir?->isoFormat('D MMMM YYYY'),
            'usia_sekarang_label' => $usiaSekarang !== null
                ? self::labelUsia((int) floor($usiaSekarang))
                : null,
            'orang_tua' => $balita->orangTua?->name,
        ];
    }

    private function indikatorBbU(Balita $balita, Collection $pengukurans): array
    {
        $titik = [];
        $terakhir = $pengukurans->last();

        foreach ($pengukurans as $p) {
            if ($p->berat_badan === null) {
                continue;
            }

            $z = $p->z_score_bbu !== null ? (float) $p->z_score_bbu : null;

            $titik[] = [
                'x' => self::usiaDesimal($balita->birth_date, $p->date),
                'y' => round((float) $p->berat_badan, 2),
                'tanggal' => $p->date->isoFormat('D MMM YYYY'),
                'tanggal_iso' => $p->date->toDateString(),
                'usia_label' => $p->usia_label,
                'usia_bulan' => $p->usia_bulan,
                'z' => $z,
                'status' => StatusGiziService::kategoriBbU($z),
                'terbaru' => $terakhir !== null && $p->is($terakhir),
            ];
        }

        $rentang = self::rentangSumbuUsia(
            $terakhir ? self::usiaDesimal($balita->birth_date, $terakhir->date) : 0
        );

        return $this->susunIndikator('bb_u', 'Berat / Umur', 'BB', 'kg', 'bulan', 'BB_U', $titik, $balita->jenis_kelamin, $rentang);
    }

    private function indikatorTbU(Balita $balita, Collection $pengukurans): array
    {
        $titik = [];
        $terakhir = $pengukurans->last();

        foreach ($pengukurans as $p) {
            if ($p->tinggi_badan === null) {
                continue;
            }

            $z = $p->z_score_tbu !== null ? (float) $p->z_score_tbu : null;

            $titik[] = [
                'x' => self::usiaDesimal($balita->birth_date, $p->date),
                'y' => round((float) $p->tinggi_badan, 2),
                'tanggal' => $p->date->isoFormat('D MMM YYYY'),
                'tanggal_iso' => $p->date->toDateString(),
                'usia_label' => $p->usia_label,
                'usia_bulan' => $p->usia_bulan,
                'z' => $z,
                'status' => StatusGiziService::kategoriTbU($z),
                'terbaru' => $terakhir !== null && $p->is($terakhir),
            ];
        }

        $rentang = self::rentangSumbuUsia(
            $terakhir ? self::usiaDesimal($balita->birth_date, $terakhir->date) : 0
        );

        return $this->susunIndikator('tb_u', 'Tinggi / Umur', 'TB', 'cm', 'bulan', 'TB_U', $titik, $balita->jenis_kelamin, $rentang);
    }

    private function indikatorBbTb(Balita $balita, Collection $pengukurans): array
    {
        $titik = [];
        $terakhir = $pengukurans->last();

        foreach ($pengukurans as $p) {
            if ($p->berat_badan === null || $p->tinggi_badan === null) {
                continue;
            }

            $z = $p->z_score_bbtb !== null ? (float) $p->z_score_bbtb : null;

            $titik[] = [
                'x' => round((float) $p->tinggi_badan, 2),
                'y' => round((float) $p->berat_badan, 2),
                'tanggal' => $p->date->isoFormat('D MMM YYYY'),
                'tanggal_iso' => $p->date->toDateString(),
                'usia_label' => $p->usia_label,
                'usia_bulan' => $p->usia_bulan,
                'z' => $z,
                'status' => StatusGiziService::kategoriBbTb($z),
                'terbaru' => $terakhir !== null && $p->is($terakhir),
            ];
        }

        // Penyederhanaan: satu indikator acuan untuk seluruh titik.
        // BB_PB bila usia pengukuran terakhir < 24 bulan, selain itu BB_TB.
        $usiaTerakhir = $terakhir?->usia_bulan;
        $indikatorAcuan = ($usiaTerakhir !== null && $usiaTerakhir < self::BATAS_USIA_TERLENTANG) ? 'BB_PB' : 'BB_TB';

        return $this->susunIndikator('bb_tb', 'BB / TB', 'BB', 'kg', 'cm', $indikatorAcuan, $titik, $balita->jenis_kelamin, $this->rentangSumbuTinggi($titik));
    }

    /**
     * Rakit satu indikator: titik, kurva acuan (bila tersedia), rentang sumbu X.
     */
    private function susunIndikator(
        string $kode,
        string $label,
        string $labelUkuran,
        string $satuanY,
        string $satuanX,
        string $indikatorAcuan,
        array $titik,
        string $jenisKelamin,
        array $rentangX
    ): array {
        $kurva = $this->kurvaAcuan($indikatorAcuan, $jenisKelamin);

        return [
            'kode' => $kode,
            'label' => $label,
            'label_ukuran' => $labelUkuran,
            'satuan_y' => $satuanY,
            'satuan_x' => $satuanX,
            'indikator_acuan' => $kurva !== null ? $indikatorAcuan : null,
            'titik' => $titik,
            'jumlah_titik' => count($titik),
            'kurva' => $kurva,
            'catatan_kurva' => $kurva === null
                ? 'Kurva acuan WHO untuk indikator ini belum tersedia.'
                : null,
            'rentang_x' => $rentangX,
        ];
    }

    /**
     * Bangun empat garis kurva acuan (-3, -2, 0, +2 SD) dari parameter L, M, S.
     * Mengembalikan null bila baris LMS untuk indikator tersebut belum ada.
     */
    private function kurvaAcuan(string $indikator, string $jenisKelamin): ?array
    {
        $baris = StandardLms::where('indikator', $indikator)
            ->where('jenis_kelamin', $jenisKelamin)
            ->orderBy('acuan_nilai')
            ->get();

        if ($baris->isEmpty()) {
            return null;
        }

        $garis = [];
        foreach (self::GARIS_KURVA as [$k, $label]) {
            $data = [];
            foreach ($baris as $lm) {
                $data[] = [
                    'x' => (float) $lm->acuan_nilai,
                    'y' => round(
                        self::nilaiSD((float) $lm->l, (float) $lm->m, (float) $lm->s, (float) $k),
                        2
                    ),
                ];
            }
            $garis[] = ['label' => $label, 'k' => $k, 'data' => $data];
        }

        return $garis;
    }

    /**
     * Rentang sumbu X untuk indikator BB/TB (panjang/tinggi badan, cm).
     */
    private function rentangSumbuTinggi(array $titik): array
    {
        if ($titik === []) {
            return [45, 120];
        }

        $xs = array_column($titik, 'x');
        $min = (int) floor((min($xs) - 5) / 5) * 5;
        $max = (int) ceil((max($xs) + 5) / 5) * 5;

        return [max(45, $min), min(120, $max)];
    }

    private function ringkasan(Balita $balita, Collection $pengukurans, ?PengukuranKms $terakhir): array
    {
        if ($terakhir === null) {
            return [
                'bb_u_terkini' => $this->kartuKosong('kg'),
                'tb_u_terkini' => $this->kartuKosong('cm'),
                'kenaikan_terakhir' => ['gram' => null, 'bulan' => null, 'label' => '-'],
                'status' => [
                    'nilai' => null,
                    'lengkap' => false,
                    'indeks_tersedia' => ['bb_u' => false, 'tb_u' => false, 'bb_tb' => false],
                    'stunting' => false,
                    'stunting_keterangan' => 'Belum dapat dinilai',
                    'perlu_dicek' => false,
                ],
                'tanggal_terakhir' => null,
            ];
        }

        $zBbu = $terakhir->z_score_bbu !== null ? (float) $terakhir->z_score_bbu : null;
        $zTbu = $terakhir->z_score_tbu !== null ? (float) $terakhir->z_score_tbu : null;

        // Kenaikan berat terhadap pengukuran sebelumnya.
        $kenaikan = ['gram' => null, 'bulan' => null, 'label' => '-'];
        if ($pengukurans->count() >= 2) {
            $sebelumnya = $pengukurans->get($pengukurans->count() - 2);
            if ($terakhir->berat_badan !== null && $sebelumnya->berat_badan !== null) {
                $gram = (int) round(((float) $terakhir->berat_badan - (float) $sebelumnya->berat_badan) * 1000);
                $bulan = $this->jarakBulan($sebelumnya->date, $terakhir->date);
                $tanda = $gram >= 0 ? '+' : '-';
                $kenaikan = [
                    'gram' => $gram,
                    'bulan' => $bulan,
                    'label' => $tanda . $this->formatAngka(abs($gram), 0) . " g dalam {$bulan} bln",
                ];
            }
        }

        $hasil = StatusGiziService::untukPengukuran($terakhir);

        $stuntingKeterangan = ! $hasil['indeks_tersedia']['tb_u']
            ? 'Belum dapat dinilai'
            : ($hasil['stunting'] ? 'Terindikasi stunting' : 'Tidak terindikasi stunting');

        return [
            'bb_u_terkini' => [
                'nilai' => $terakhir->berat_badan !== null ? (float) $terakhir->berat_badan : null,
                'satuan' => 'kg',
                'nilai_label' => $terakhir->berat_badan !== null ? $this->formatAngka((float) $terakhir->berat_badan, 1) : null,
                'z' => $zBbu,
                'z_label' => $zBbu !== null ? $this->formatZ($zBbu) : null,
                'kategori' => StatusGiziService::kategoriBbU($zBbu),
                'tanggal' => $terakhir->date->isoFormat('D MMM YYYY'),
            ],
            'tb_u_terkini' => [
                'nilai' => $terakhir->tinggi_badan !== null ? (float) $terakhir->tinggi_badan : null,
                'satuan' => 'cm',
                'nilai_label' => $terakhir->tinggi_badan !== null ? $this->formatAngka((float) $terakhir->tinggi_badan, 1) : null,
                'z' => $zTbu,
                'z_label' => $zTbu !== null ? $this->formatZ($zTbu) : null,
                'kategori' => StatusGiziService::kategoriTbU($zTbu),
                'tanggal' => $terakhir->date->isoFormat('D MMM YYYY'),
            ],
            'kenaikan_terakhir' => $kenaikan,
            'status' => [
                'nilai' => $hasil['status'],
                'lengkap' => $hasil['lengkap'],
                'indeks_tersedia' => $hasil['indeks_tersedia'],
                'stunting' => $hasil['stunting'],
                'stunting_keterangan' => $stuntingKeterangan,
                'perlu_dicek' => $hasil['perlu_dicek'],
            ],
            'tanggal_terakhir' => $terakhir->date->toDateString(),
        ];
    }

    private function kartuKosong(string $satuan): array
    {
        return [
            'nilai' => null,
            'satuan' => $satuan,
            'nilai_label' => null,
            'z' => null,
            'z_label' => null,
            'kategori' => null,
            'tanggal' => null,
        ];
    }

    /**
     * Riwayat pengukuran ringkas (terbaru dulu) beserta selisih berat dan cara ukur.
     */
    private function riwayat(Collection $pengukurans): array
    {
        $list = $pengukurans->values();
        $hasil = [];

        foreach ($list as $i => $p) {
            $sebelumnya = $i > 0 ? $list[$i - 1] : null;

            $selisihBb = null;
            if ($sebelumnya && $p->berat_badan !== null && $sebelumnya->berat_badan !== null) {
                $selisihBb = round((float) $p->berat_badan - (float) $sebelumnya->berat_badan, 1);
            }

            $hasil[] = [
                'id' => $p->id,
                'tanggal' => $p->date->isoFormat('D MMM YYYY'),
                'tanggal_iso' => $p->date->toDateString(),
                'usia_label' => $p->usia_label,
                'berat_badan' => $p->berat_badan !== null ? (float) $p->berat_badan : null,
                'berat_label' => $p->berat_badan !== null ? $this->formatAngka((float) $p->berat_badan, 1) : null,
                'selisih_bb' => $selisihBb,
                'selisih_bb_label' => $selisihBb !== null
                    ? ($selisihBb >= 0 ? '+' : '') . $this->formatAngka($selisihBb, 1)
                    : null,
                'tinggi_badan' => $p->tinggi_badan !== null ? (float) $p->tinggi_badan : null,
                'tinggi_label' => $p->tinggi_badan !== null ? $this->formatAngka((float) $p->tinggi_badan, 1) : null,
                'cara_ukur' => $p->usia_bulan < self::BATAS_USIA_TERLENTANG ? 'Terlentang' : 'Berdiri',
                'lingkar_lengan_atas' => $p->lingkar_lengan_atas !== null ? (float) $p->lingkar_lengan_atas : null,
                'lingkar_lengan_atas_label' => $p->lingkar_lengan_atas !== null
                    ? $this->formatAngka((float) $p->lingkar_lengan_atas, 1)
                    : null,
                'status' => $p->status_gizi,
                'petugas' => $p->petugas?->name,
            ];
        }

        return array_reverse($hasil);
    }

    /**
     * Jarak bulan (dibulatkan) antara dua tanggal; minimal 1 bulan.
     */
    private function jarakBulan(\DateTimeInterface $awal, \DateTimeInterface $akhir): int
    {
        $bulan = (int) round(abs((float) Carbon::parse($awal)->diffInMonths(Carbon::parse($akhir))));

        return max(1, $bulan);
    }

    /**
     * Format angka dengan koma desimal (mis. 12,1).
     */
    private function formatAngka(float $nilai, int $desimal): string
    {
        return number_format($nilai, $desimal, ',', '.');
    }

    /**
     * Format z-score dengan tanda, mis. "-0,42 SD".
     */
    private function formatZ(float $z): string
    {
        return ($z >= 0 ? '+' : '-') . $this->formatAngka(abs($z), 2) . ' SD';
    }

    /**
     * Label usia dari jumlah bulan, mis. "26 bulan (2 th 2 bln)".
     */
    public static function labelUsia(int $bulan): string
    {
        $bulan = max(0, $bulan);

        if ($bulan < 12) {
            return "{$bulan} bulan";
        }

        $tahun = intdiv($bulan, 12);
        $sisa = $bulan % 12;

        return $sisa === 0
            ? "{$bulan} bulan ({$tahun} th)"
            : "{$bulan} bulan ({$tahun} th {$sisa} bln)";
    }
}
