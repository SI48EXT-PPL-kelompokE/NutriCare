<?php

namespace App\Services;

use App\Models\StandardLms;
use App\Models\PengukuranKms;

class ZScoreService
{
    /**
     * Cache data LMS dalam memori selama satu instans
     * @var array<string, StandardLms>
     */
    private array $cache = [];

    /**
     * Flag apakah semua data sudah dimuat ke memori
     * @var bool
     */
    private bool $semuaDimuat = false;

    /**
     * Hitung z-score dari nilai pengukuran dengan metode LMS WHO
     *
     * Rumus LMS:
     * - Jika L != 0: Z = ((y / M)^L - 1) / (L * S)
     * - Jika L == 0: Z = ln(y / M) / S
     *
     * @param float $y Nilai pengukuran (berat atau tinggi)
     * @param float $l Parameter L dari tabel LMS
     * @param float $m Parameter M dari tabel LMS (median)
     * @param float $s Parameter S dari tabel LMS (coefficient of variation)
     * @param bool $koreksiEkstrem Aktifkan koreksi nilai ekstrem WHO (> ±3 SD)
     * @return float Z-score yang sudah dibulatkan 2 desimal
     */
    public static function hitungZ(float $y, float $l, float $m, float $s, bool $koreksiEkstrem = false): float
    {
        // Rumus LMS standar
        if (abs($l) > 0.0001) {
            // L != 0: Z = ((y/M)^L - 1) / (L*S)
            $z = (pow($y / $m, $l) - 1) / ($l * $s);
        } else {
            // L == 0: Z = ln(y/M) / S
            $z = log($y / $m) / $s;
        }

        // Koreksi nilai ekstrem WHO (untuk indikator berbasis BB)
        if ($koreksiEkstrem) {
            if ($z > 3) {
                $sd3 = self::hitungSD($l, $m, $s, 3);
                $sd2 = self::hitungSD($l, $m, $s, 2);
                $z = 3 + ($y - $sd3) / ($sd3 - $sd2);
            } elseif ($z < -3) {
                $sdMinus3 = self::hitungSD($l, $m, $s, -3);
                $sdMinus2 = self::hitungSD($l, $m, $s, -2);
                $z = -3 + ($y - $sdMinus3) / ($sdMinus2 - $sdMinus3);
            }
        }

        // Bulatkan 2 desimal
        $result = round($z, 2);

        // Normalkan "-0.00" menjadi "0.00"
        if ($result == -0.0) {
            $result = 0.0;
        }

        return $result;
    }

    /**
     * Hitung nilai SD pada z-score tertentu dari parameter LMS
     *
     * Rumus:
     * - L != 0: SD(k) = M * (1 + L*S*k)^(1/L)
     * - L == 0: SD(k) = M * exp(S*k)
     *
     * @param float $l Parameter L
     * @param float $m Parameter M
     * @param float $s Parameter S
     * @param float $k Z-score target (-3, -2, -1, 0, 1, 2, 3)
     * @return float Nilai SD yang sesuai
     */
    private static function hitungSD(float $l, float $m, float $s, float $k): float
    {
        if (abs($l) > 0.0001) {
            return $m * pow(1 + $l * $s * $k, 1 / $l);
        } else {
            return $m * exp($s * $k);
        }
    }

    /**
     * Hitung z-score untuk indikator tertentu
     *
     * @param string $indikator Kode indikator (BB_U, TB_U, BB_PB, BB_TB)
     * @param string $jenisKelamin Jenis kelamin (L/P)
     * @param float $nilai Nilai pengukuran (kg untuk BB, cm untuk TB)
     * @param float|int $acuan Nilai acuan (usia bulan untuk BB_U/TB_U, tinggi cm untuk BB_PB/BB_TB)
     * @return float|null Z-score atau null jika data acuan tidak tersedia
     */
    public function hitung(string $indikator, string $jenisKelamin, float $nilai, float|int $acuan): ?float
    {
        // Tentukan apakah perlu koreksi ekstrem
        $koreksiEkstrem = config("zscore.extreme_adjustment.{$indikator}", false);

        // Untuk indikator berbasis tinggi (BB_PB/BB_TB), perlu interpolasi
        if (in_array($indikator, ['BB_PB', 'BB_TB'])) {
            return $this->hitungDenganInterpolasi($indikator, $jenisKelamin, $nilai, $acuan, $koreksiEkstrem);
        }

        // Lookup data LMS
        $lms = $this->cariLms($indikator, $jenisKelamin, $acuan);

        if (!$lms) {
            return null;
        }

        return self::hitungZ(
            $nilai,
            (float) $lms->l,
            (float) $lms->m,
            (float) $lms->s,
            $koreksiEkstrem
        );
    }

    /**
     * Hitung z-score dengan interpolasi linear untuk indikator berbasis tinggi
     *
     * @param string $indikator BB_PB atau BB_TB
     * @param string $jenisKelamin L/P
     * @param float $beratBadan Berat badan (kg)
     * @param float $tinggiBadan Tinggi badan (cm)
     * @param bool $koreksiEkstrem Aktifkan koreksi ekstrem
     * @return float|null Z-score atau null jika di luar rentang
     */
    private function hitungDenganInterpolasi(
        string $indikator,
        string $jenisKelamin,
        float $beratBadan,
        float $tinggiBadan,
        bool $koreksiEkstrem
    ): ?float {
        // Cari dua baris yang mengapit tinggi badan
        $bawah = $this->cariLmsRange($indikator, $jenisKelamin, $tinggiBadan, '<=');
        $atas = $this->cariLmsRange($indikator, $jenisKelamin, $tinggiBadan, '>=');

        if (!$bawah || !$atas) {
            return null; // Di luar rentang tabel
        }

        // Jika tepat sama dengan satu baris
        if ($bawah->acuan_nilai == $atas->acuan_nilai) {
            return self::hitungZ(
                $beratBadan,
                (float) $bawah->l,
                (float) $bawah->m,
                (float) $bawah->s,
                $koreksiEkstrem
            );
        }

        // Interpolasi linear L, M, S
        $ratio = ($tinggiBadan - $bawah->acuan_nilai) / ($atas->acuan_nilai - $bawah->acuan_nilai);
        
        $l = $bawah->l + ($atas->l - $bawah->l) * $ratio;
        $m = $bawah->m + ($atas->m - $bawah->m) * $ratio;
        $s = $bawah->s + ($atas->s - $bawah->s) * $ratio;

        return self::hitungZ($beratBadan, $l, $m, $s, $koreksiEkstrem);
    }

    /**
     * Cari data LMS dari cache atau database
     *
     * @param string $indikator
     * @param string $jenisKelamin
     * @param float|int $acuan
     * @return StandardLms|null
     */
    private function cariLms(string $indikator, string $jenisKelamin, float|int $acuan): ?StandardLms
    {
        $key = "{$indikator}_{$jenisKelamin}_{$acuan}";

        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        // Lookup dengan indeks unik
        $lms = StandardLms::where('indikator', $indikator)
            ->where('jenis_kelamin', $jenisKelamin)
            ->where('acuan_nilai', $acuan)
            ->first();

        if ($lms) {
            $this->cache[$key] = $lms;
        }

        return $lms;
    }

    /**
     * Cari baris LMS terdekat untuk interpolasi
     *
     * @param string $indikator
     * @param string $jenisKelamin
     * @param float $acuan
     * @param string $operator '<=' untuk batas bawah, '>=' untuk batas atas
     * @return StandardLms|null
     */
    private function cariLmsRange(string $indikator, string $jenisKelamin, float $acuan, string $operator): ?StandardLms
    {
        $query = StandardLms::where('indikator', $indikator)
            ->where('jenis_kelamin', $jenisKelamin)
            ->where('acuan_nilai', $operator, $acuan);

        if ($operator === '<=') {
            $query->orderByDesc('acuan_nilai');
        } else {
            $query->orderBy('acuan_nilai');
        }

        return $query->first();
    }

    /**
     * Hitung semua z-score untuk satu pengukuran
     *
     * @param PengukuranKms $p Data pengukuran dengan relasi balita
     * @return array ['z_score_bbu' => ?float, 'z_score_tbu' => ?float, 'z_score_bbtb' => ?float]
     */
    public function untukPengukuran(PengukuranKms $p): array
    {
        // Pastikan relasi balita dimuat
        if (!$p->relationLoaded('balita')) {
            $p->load('balita');
        }

        $balita = $p->balita;
        $jenisKelamin = $balita->jenis_kelamin;
        $usiaBulan = $p->usia_bulan;

        // BB/U: Berat badan menurut umur
        $zScoreBbu = null;
        if ($p->berat_badan && $usiaBulan !== null) {
            $zScoreBbu = $this->hitung('BB_U', $jenisKelamin, $p->berat_badan, $usiaBulan);
        }

        // TB/U: Tinggi/panjang badan menurut umur
        $zScoreTbu = null;
        if ($p->tinggi_badan && $usiaBulan !== null) {
            $zScoreTbu = $this->hitung('TB_U', $jenisKelamin, $p->tinggi_badan, $usiaBulan);
        }

        // BB/PB atau BB/TB: Berat badan menurut panjang/tinggi badan
        $zScoreBbtb = null;
        if ($p->berat_badan && $p->tinggi_badan) {
            $indikator = $usiaBulan < 24 ? 'BB_PB' : 'BB_TB';
            $zScoreBbtb = $this->hitung($indikator, $jenisKelamin, $p->berat_badan, $p->tinggi_badan);
        }

        return [
            'z_score_bbu' => $zScoreBbu,
            'z_score_tbu' => $zScoreTbu,
            'z_score_bbtb' => $zScoreBbtb,
        ];
    }

    /**
     * Muat semua data LMS ke memori untuk pemrosesan massal
     * Gunakan untuk command atau batch processing
     *
     * @return void
     */
    public function muatSemua(): void
    {
        if ($this->semuaDimuat) {
            return;
        }

        $semuaLms = StandardLms::all();

        foreach ($semuaLms as $lms) {
            $key = "{$lms->indikator}_{$lms->jenis_kelamin}_{$lms->acuan_nilai}";
            $this->cache[$key] = $lms;
        }

        $this->semuaDimuat = true;
    }
}
