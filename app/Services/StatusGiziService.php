<?php

namespace App\Services;

use App\Enums\StatusGizi;
use App\Models\PengukuranKms;

class StatusGiziService
{
    /**
     * Urutan keparahan cadangan bila config tidak tersedia (mis. unit test tanpa bootstrap Laravel).
     * Wajib sama dengan config/status_gizi.php -> urutan_keparahan.
     */
    private const URUTAN_DEFAULT = [
        'Gizi Buruk',
        'Sangat Pendek',
        'Berat Badan Sangat Kurang',
        'Gizi Kurang',
        'Stunting',
        'Berat Badan Kurang',
        'Obesitas',
        'Gizi Lebih',
        'Risiko Gizi Lebih',
        'Tinggi',
        'Normal',
    ];

    private const RENTANG_DEFAULT = [
        'bb_u' => [-6.00, 5.00],
        'tb_u' => [-6.00, 6.00],
        'bb_tb' => [-5.00, 5.00],
    ];

    /**
     * Ambil nilai konfigurasi status gizi; kembalikan default bila tidak tersedia.
     */
    private static function konfigurasi(string $key, array $default): array
    {
        try {
            $nilai = config('status_gizi.' . $key);
        } catch (\Throwable) {
            // Konfigurasi Laravel belum tersedia (mis. unit test murni)
            return $default;
        }

        return is_array($nilai) && $nilai !== [] ? $nilai : $default;
    }

    /**
     * Posisi status pada urutan keparahan. Nilai lebih kecil = lebih kritis.
     * Status yang tidak tercantum ditempatkan paling akhir.
     */
    private static function indeksPrioritas(StatusGizi $status, array $urutan): int
    {
        $indeks = array_search($status->value, $urutan, true);

        return $indeks === false ? PHP_INT_MAX : (int) $indeks;
    }

    public static function kategoriBbU(?float $z): ?string
    {
        if ($z === null) {
            return null;
        }

        return match (true) {
            $z < -3.00 => 'Berat Badan Sangat Kurang',
            $z < -2.00 => 'Berat Badan Kurang',
            $z <= 1.00 => 'Berat Badan Normal',
            default => 'Risiko Berat Badan Lebih',
        };
    }

    public static function kategoriTbU(?float $z): ?string
    {
        if ($z === null) {
            return null;
        }

        return match (true) {
            $z < -3.00 => 'Sangat Pendek',
            $z < -2.00 => 'Pendek',
            $z <= 3.00 => 'Normal',
            default => 'Tinggi',
        };
    }

    public static function kategoriBbTb(?float $z): ?string
    {
        if ($z === null) {
            return null;
        }

        return match (true) {
            $z < -3.00 => 'Gizi Buruk',
            $z < -2.00 => 'Gizi Kurang',
            $z <= 1.00 => 'Gizi Baik',
            $z <= 2.00 => 'Berisiko Gizi Lebih',
            $z <= 3.00 => 'Gizi Lebih',
            default => 'Obesitas',
        };
    }

    public static function tentukan(?float $zBbu, ?float $zTbu, ?float $zBbtb): array
    {
        $kategori = [
            'bb_u' => self::kategoriBbU($zBbu),
            'tb_u' => self::kategoriTbU($zTbu),
            'bb_tb' => self::kategoriBbTb($zBbtb),
        ];

        $stunting = in_array($kategori['tb_u'], ['Pendek', 'Sangat Pendek'], true);
        $wasting = in_array($kategori['bb_tb'], ['Gizi Kurang', 'Gizi Buruk'], true);
        $underweight = in_array($kategori['bb_u'], ['Berat Badan Kurang', 'Berat Badan Sangat Kurang'], true);
        $giziLebih = in_array($kategori['bb_tb'], ['Berisiko Gizi Lebih', 'Gizi Lebih', 'Obesitas'], true)
            || $kategori['bb_u'] === 'Risiko Berat Badan Lebih';

        $indeksTersedia = [
            'bb_u' => $zBbu !== null,
            'tb_u' => $zTbu !== null,
            'bb_tb' => $zBbtb !== null,
        ];
        $lengkap = $indeksTersedia['bb_u'] && $indeksTersedia['tb_u'] && $indeksTersedia['bb_tb'];

        $candidates = [];
        if ($kategori['bb_tb'] === 'Gizi Buruk') {
            $candidates[] = StatusGizi::GIZI_BURUK;
        }
        if ($kategori['tb_u'] === 'Sangat Pendek') {
            $candidates[] = StatusGizi::SANGAT_PENDEK;
        }
        if ($kategori['bb_u'] === 'Berat Badan Sangat Kurang') {
            $candidates[] = StatusGizi::BERAT_BADAN_SANGAT_KURANG;
        }
        if ($kategori['bb_tb'] === 'Gizi Kurang') {
            $candidates[] = StatusGizi::GIZI_KURANG;
        }
        if ($kategori['tb_u'] === 'Pendek') {
            $candidates[] = StatusGizi::STUNTING;
        }
        if ($kategori['bb_u'] === 'Berat Badan Kurang') {
            $candidates[] = StatusGizi::BERAT_BADAN_KURANG;
        }
        if ($kategori['bb_tb'] === 'Obesitas') {
            $candidates[] = StatusGizi::OBESITAS;
        }
        if ($kategori['bb_tb'] === 'Gizi Lebih') {
            $candidates[] = StatusGizi::GIZI_LEBIH;
        }
        if ($kategori['bb_tb'] === 'Berisiko Gizi Lebih' || $kategori['bb_u'] === 'Risiko Berat Badan Lebih') {
            $candidates[] = StatusGizi::RISIKO_GIZI_LEBIH;
        }
        if ($kategori['tb_u'] === 'Tinggi') {
            $candidates[] = StatusGizi::TINGGI;
        }

        $status = null;
        if ($candidates !== []) {
            // Urutan keparahan dari config; status terparah (paling awal) dipilih.
            $urutan = self::konfigurasi('urutan_keparahan', self::URUTAN_DEFAULT);
            usort($candidates, function (StatusGizi $a, StatusGizi $b) use ($urutan) {
                return self::indeksPrioritas($a, $urutan) <=> self::indeksPrioritas($b, $urutan);
            });
            $status = $candidates[0];
        } elseif ($indeksTersedia['bb_u'] || $indeksTersedia['tb_u'] || $indeksTersedia['bb_tb']) {
            $status = StatusGizi::NORMAL;
        }

        $rentang = self::konfigurasi('rentang_kewajaran', self::RENTANG_DEFAULT);
        $perluDicek = false;
        foreach ([
            'bb_u' => $zBbu,
            'tb_u' => $zTbu,
            'bb_tb' => $zBbtb,
        ] as $nama => $z) {
            if ($z !== null && ($z < $rentang[$nama][0] || $z > $rentang[$nama][1])) {
                $perluDicek = true;
            }
        }

        return [
            'status' => $status,
            'kategori' => $kategori,
            'stunting' => $stunting,
            'wasting' => $wasting,
            'underweight' => $underweight,
            'gizi_lebih' => $giziLebih,
            'lengkap' => $lengkap,
            'indeks_tersedia' => $indeksTersedia,
            'perlu_dicek' => $perluDicek,
        ];
    }

    public static function untukPengukuran(PengukuranKms $pengukuran): array
    {
        return self::tentukan(
            $pengukuran->z_score_bbu !== null ? (float) $pengukuran->z_score_bbu : null,
            $pengukuran->z_score_tbu !== null ? (float) $pengukuran->z_score_tbu : null,
            $pengukuran->z_score_bbtb !== null ? (float) $pengukuran->z_score_bbtb : null,
        );
    }
}
