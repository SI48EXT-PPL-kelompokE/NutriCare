<?php

namespace App\Services;

use App\Models\BarangPmt;
use App\Models\PaketPmt;
use InvalidArgumentException;

class PmtNutritionService
{
    /**
     * Hitung total nutrisi dari array item komposisi.
     * Format item: ['barang_id' => int, 'jumlah' => float]
     *
     * @param array<int, array{barang_id: int, jumlah: float|int}> $items
     * @return array{total_energi_kcal: float, total_protein: float, total_lemak: float, total_karbohidrat: float}
     */
    public function calculateTotals(array $items): array
    {
        $totalEnergi = 0.0;
        $totalProtein = 0.0;
        $totalLemak = 0.0;
        $totalKarbo = 0.0;

        if (empty($items)) {
            return [
                'total_energi_kcal' => 0.0,
                'total_protein' => 0.0,
                'total_lemak' => 0.0,
                'total_karbohidrat' => 0.0,
            ];
        }

        $barangIds = array_column($items, 'barang_id');
        $barangs = BarangPmt::whereIn('id', $barangIds)->get()->keyBy('id');

        foreach ($items as $item) {
            $barangId = (int) ($item['barang_id'] ?? 0);
            $jumlah = (float) ($item['jumlah'] ?? 0);

            if ($jumlah <= 0 || !isset($barangs[$barangId])) {
                continue;
            }

            $barang = $barangs[$barangId];
            // Nilai gizi barang disimpan per 100 gram/unit
            $faktor = $jumlah / 100;

            $totalEnergi += ((float) $barang->energi_kcal) * $faktor;
            $totalProtein += ((float) $barang->protein) * $faktor;
            $totalLemak += ((float) $barang->lemak) * $faktor;
            $totalKarbo += ((float) $barang->karbohidrat) * $faktor;
        }

        return [
            'total_energi_kcal' => round($totalEnergi, 2),
            'total_protein' => round($totalProtein, 2),
            'total_lemak' => round($totalLemak, 2),
            'total_karbohidrat' => round($totalKarbo, 2),
        ];
    }

    /**
     * Hitung ulang total nutrisi untuk model PaketPmt berdasarkan relasi komposisi saat ini.
     */
    public function recalculateForPaket(PaketPmt $paket): PaketPmt
    {
        $paket->loadMissing('komposisiPaketPmt.barang');

        $totalEnergi = 0.0;
        $totalProtein = 0.0;
        $totalLemak = 0.0;
        $totalKarbo = 0.0;

        foreach ($paket->komposisiPaketPmt as $komposisi) {
            $barang = $komposisi->barang;
            if (!$barang) {
                continue;
            }

            $faktor = ((float) $komposisi->jumlah) / 100;
            $totalEnergi += ((float) $barang->energi_kcal) * $faktor;
            $totalProtein += ((float) $barang->protein) * $faktor;
            $totalLemak += ((float) $barang->lemak) * $faktor;
            $totalKarbo += ((float) $barang->karbohidrat) * $faktor;
        }

        $paket->update([
            'total_energi_kcal' => round($totalEnergi, 2),
            'total_protein' => round($totalProtein, 2),
            'total_lemak' => round($totalLemak, 2),
            'total_karbohidrat' => round($totalKarbo, 2),
        ]);

        return $paket;
    }

    /**
     * Validasi kesesuaian usia sasaran PMT Balita.
     * Sesuai standar Kemenkes/WHO:
     * - Usia minimal: 6 bulan (periode MP-ASI / PMT, usia 0-5 bulan adalah ASI eksklusif).
     * - Usia maksimal: 59 bulan (batas usia balita).
     * - Usia minimal harus lebih kecil atau sama dengan usia maksimal.
     *
     * @return array<string> Daftar pesan kesalahan jika ada
     */
    public function validateAgeTarget(int $minAge, int $maxAge): array
    {
        $errors = [];

        if ($minAge < 6) {
            $errors[] = 'Usia sasaran minimal tidak boleh kurang dari 6 bulan (0-5 bulan adalah periode ASI eksklusif).';
        }

        if ($maxAge > 59) {
            $errors[] = 'Usia sasaran maksimal tidak boleh melebihi 59 bulan (batas usia balita).';
        }

        if ($minAge > $maxAge) {
            $errors[] = 'Usia sasaran minimal harus lebih kecil atau sama dengan usia sasaran maksimal.';
        }

        return $errors;
    }
}
