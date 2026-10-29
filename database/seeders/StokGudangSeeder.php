<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StokGudang;
use App\Models\BarangPmt;
use Carbon\Carbon;

class StokGudangSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create('id_ID');
        $barangs = BarangPmt::all();

        foreach ($barangs as $barang) {
            $jumlahBatch = rand(2, 3);
            
            for ($i = 1; $i <= $jumlahBatch; $i++) {
                $dateIn = now()->subMonths(rand(1, 6));
                
                // Variasi exp_date: ada yang hampir kadaluarsa, ada yang masih lama
                if ($i == 1 && rand(1, 100) <= 40) {
                    // 40% batch pertama hampir kedaluwarsa (2 minggu)
                    $expDate = now()->addDays(rand(10, 20));
                } else {
                    // Sisanya normal (3-12 bulan)
                    $expDate = now()->addMonths(rand(3, 12));
                }

                // Variasi stok: ada yang di bawah min_batas_stok (kritis)
                $stockQty = rand(1, 100) <= 20 
                    ? rand(10, $barang->min_batas_stok - 10) // 20% kritis
                    : rand($barang->min_batas_stok * 2, $barang->min_batas_stok * 5); // Normal lebih besar

                StokGudang::create([
                    'barang_id' => $barang->id,
                    'no_batch' => 'BATCH-' . strtoupper($faker->bothify('???-####')),
                    'date_in' => $dateIn,
                    'exp_date' => $expDate,
                    'stock_qty' => $stockQty,
                ]);
            }
        }

        $this->command->info('StokGudang berhasil di-seed dengan kondisi bervariasi (ada yang kritis dan hampir kadaluarsa)');
    }
}
