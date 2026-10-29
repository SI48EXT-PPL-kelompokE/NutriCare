<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaketPmt;
use App\Models\BarangPmt;
use App\Models\KomposisiPaketPmt;

class PaketPmtSeeder extends Seeder
{
    public function run(): void
    {
        $barangs = BarangPmt::all()->keyBy('nama');

        // Paket 1: PMT Pemulihan 6-23 Bulan
        $paket1 = PaketPmt::create([
            'nama_paket_pmt' => 'PMT Pemulihan 6-23 Bulan',
            'deskripsi' => 'Paket makanan tambahan untuk balita usia 6-23 bulan yang mengalami gizi kurang',
            'min_age_target' => 6,
            'max_age_target' => 23,
            'unit_type' => 'paket',
            'status' => true,
        ]);

        $komposisi1 = [
            ['barang' => 'Telur Ayam', 'jumlah' => 200, 'unit' => 'gram'], // ~4 butir
            ['barang' => 'Tempe', 'jumlah' => 100, 'unit' => 'gram'],
            ['barang' => 'Susu Bubuk', 'jumlah' => 150, 'unit' => 'gram'],
        ];

        $this->createKomposisi($paket1, $komposisi1, $barangs);

        // Paket 2: PMT Pemulihan 24-59 Bulan
        $paket2 = PaketPmt::create([
            'nama_paket_pmt' => 'PMT Pemulihan 24-59 Bulan',
            'deskripsi' => 'Paket makanan tambahan untuk balita usia 24-59 bulan yang mengalami gizi kurang',
            'min_age_target' => 24,
            'max_age_target' => 59,
            'unit_type' => 'paket',
            'status' => true,
        ]);

        $komposisi2 = [
            ['barang' => 'Ikan Kembung', 'jumlah' => 200, 'unit' => 'gram'],
            ['barang' => 'Beras', 'jumlah' => 250, 'unit' => 'gram'],
            ['barang' => 'Tempe', 'jumlah' => 150, 'unit' => 'gram'],
            ['barang' => 'Bayam', 'jumlah' => 100, 'unit' => 'gram'],
        ];

        $this->createKomposisi($paket2, $komposisi2, $barangs);

        // Paket 3: PMT Penyuluhan Bubur Kacang Hijau
        $paket3 = PaketPmt::create([
            'nama_paket_pmt' => 'PMT Penyuluhan Bubur Kacang Hijau',
            'deskripsi' => 'Paket PMT penyuluhan untuk posyandu',
            'min_age_target' => 6,
            'max_age_target' => 59,
            'unit_type' => 'porsi',
            'status' => true,
        ]);

        $komposisi3 = [
            ['barang' => 'Kacang Hijau', 'jumlah' => 50, 'unit' => 'gram'],
            ['barang' => 'Susu Bubuk', 'jumlah' => 30, 'unit' => 'gram'],
        ];

        $this->createKomposisi($paket3, $komposisi3, $barangs);

        // Paket 4: PMT Biskuit Balita (nonaktif)
        $paket4 = PaketPmt::create([
            'nama_paket_pmt' => 'PMT Biskuit Balita',
            'deskripsi' => 'Paket biskuit untuk balita (program lama, tidak aktif)',
            'min_age_target' => 12,
            'max_age_target' => 59,
            'unit_type' => 'paket',
            'status' => false, // Nonaktif
        ]);

        $komposisi4 = [
            ['barang' => 'Biskuit PMT', 'jumlah' => 10, 'unit' => 'pcs'],
            ['barang' => 'Susu Bubuk', 'jumlah' => 50, 'unit' => 'gram'],
        ];

        $this->createKomposisi($paket4, $komposisi4, $barangs);

        $this->command->info('PaketPmt berhasil di-seed: 4 paket dengan komposisi');
    }

    private function createKomposisi($paket, $komposisiData, $barangs)
    {
        $totalEnergi = 0;
        $totalProtein = 0;
        $totalLemak = 0;
        $totalKarbo = 0;

        foreach ($komposisiData as $item) {
            $barang = $barangs->get($item['barang']);
            if (!$barang) continue;

            KomposisiPaketPmt::create([
                'paket_id' => $paket->id,
                'barang_id' => $barang->id,
                'jumlah' => $item['jumlah'],
                'unit_type' => $item['unit'],
            ]);

            // Hitung total nilai gizi (nilai gizi barang per 100 gram/pcs)
            // Asumsi: nilai gizi barang sudah per 100 unit
            $faktor = $item['jumlah'] / 100;
            $totalEnergi += $barang->energi_kcal * $faktor;
            $totalProtein += $barang->protein * $faktor;
            $totalLemak += $barang->lemak * $faktor;
            $totalKarbo += $barang->karbohidrat * $faktor;
        }

        // Update paket dengan total nilai gizi
        $paket->update([
            'total_energi_kcal' => round($totalEnergi, 2),
            'total_protein' => round($totalProtein, 2),
            'total_lemak' => round($totalLemak, 2),
            'total_karbohidrat' => round($totalKarbo, 2),
        ]);
    }
}
