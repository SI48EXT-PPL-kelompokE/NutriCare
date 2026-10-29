<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BarangPmt;

class BarangPmtSeeder extends Seeder
{
    public function run(): void
    {
        // CATATAN: Nilai gizi adalah pendekatan umum per 100 gram
        // Bukan acuan resmi, hanya untuk contoh sistem
        // Acuan resmi: Tabel Komposisi Pangan Indonesia (TKPI) Kemenkes
        
        $barang = [
            [
                'nama' => 'Telur Ayam',
                'kategori' => 'Protein Hewani',
                'unit_type' => 'pcs',
                'min_batas_stok' => 100,
                'energi_kcal' => 154.00, // per 100g (~2 butir)
                'protein' => 12.60,
                'lemak' => 10.80,
                'karbohidrat' => 0.70,
            ],
            [
                'nama' => 'Ikan Kembung',
                'kategori' => 'Protein Hewani',
                'unit_type' => 'gram',
                'min_batas_stok' => 5000,
                'energi_kcal' => 103.00,
                'protein' => 22.00,
                'lemak' => 1.00,
                'karbohidrat' => 0.00,
            ],
            [
                'nama' => 'Tempe',
                'kategori' => 'Protein Nabati',
                'unit_type' => 'gram',
                'min_batas_stok' => 3000,
                'energi_kcal' => 193.00,
                'protein' => 20.80,
                'lemak' => 8.80,
                'karbohidrat' => 9.90,
            ],
            [
                'nama' => 'Bayam',
                'kategori' => 'Sayuran',
                'unit_type' => 'gram',
                'min_batas_stok' => 2000,
                'energi_kcal' => 23.00,
                'protein' => 2.90,
                'lemak' => 0.40,
                'karbohidrat' => 3.60,
            ],
            [
                'nama' => 'Beras',
                'kategori' => 'Karbohidrat',
                'unit_type' => 'gram',
                'min_batas_stok' => 10000,
                'energi_kcal' => 360.00,
                'protein' => 7.00,
                'lemak' => 0.60,
                'karbohidrat' => 78.00,
            ],
            [
                'nama' => 'Susu Bubuk',
                'kategori' => 'Susu',
                'unit_type' => 'gram',
                'min_batas_stok' => 5000,
                'energi_kcal' => 500.00,
                'protein' => 26.00,
                'lemak' => 26.00,
                'karbohidrat' => 38.00,
            ],
            [
                'nama' => 'Kacang Hijau',
                'kategori' => 'Protein Nabati',
                'unit_type' => 'gram',
                'min_batas_stok' => 3000,
                'energi_kcal' => 345.00,
                'protein' => 22.20,
                'lemak' => 1.20,
                'karbohidrat' => 62.90,
            ],
            [
                'nama' => 'Biskuit PMT',
                'kategori' => 'Makanan Jadi',
                'unit_type' => 'pcs',
                'min_batas_stok' => 500,
                'energi_kcal' => 450.00, // per 100g
                'protein' => 8.00,
                'lemak' => 15.00,
                'karbohidrat' => 70.00,
            ],
        ];

        foreach ($barang as $item) {
            BarangPmt::create($item);
        }

        $this->command->info('BarangPmt berhasil di-seed: 8 barang');
    }
}
