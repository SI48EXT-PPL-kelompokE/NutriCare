<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DistribusiPmt;
use App\Models\DetailDistribusiStok;
use App\Models\Balita;
use App\Models\PaketPmt;
use App\Models\User;
use App\Models\StokGudang;
use App\Models\KomposisiPaketPmt;
use Carbon\Carbon;

class DistribusiPmtSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create('id_ID');
        
        $petugasIds = User::whereHas('role', function($q) {
            $q->whereIn('name_role', ['Nakes / Bidan', 'Kader Posyandu']);
        })->pluck('id')->toArray();

        // Ambil hanya 15 balita untuk distribusi cepat
        $balitas = Balita::inRandomOrder()->limit(15)->get();
        $pakets = PaketPmt::where('status', true)->get();
        $totalDistribusi = 0;

        foreach ($balitas as $balita) {
            if ($totalDistribusi >= 15) break;
            
            $umurBulan = Carbon::parse($balita->birth_date)->diffInMonths(now());
            
            // Pilih paket yang sesuai umur
            $paketSesuai = $pakets->first(function($p) use ($umurBulan) {
                return $umurBulan >= $p->min_age_target && $umurBulan <= $p->max_age_target;
            });

            if (!$paketSesuai) continue;

            $distribusi = DistribusiPmt::create([
                'balita_id' => $balita->id,
                'paket_id' => $paketSesuai->id,
                'user_id' => $faker->randomElement($petugasIds),
                'jumlah' => 1,
                'status' => 'selesai',
                'handover_date' => now()->subDays(rand(1, 60)),
            ]);

            // Sederhana: ambil 1 stok untuk tiap barang yang dibutuhkan
            $komposisiList = KomposisiPaketPmt::where('paket_id', $paketSesuai->id)->get();
            
            foreach ($komposisiList as $komposisi) {
                $stok = StokGudang::where('barang_id', $komposisi->barang_id)
                    ->where('stock_qty', '>', $komposisi->jumlah)
                    ->orderBy('exp_date', 'asc')
                    ->first();

                if (!$stok) continue;
                
                DetailDistribusiStok::create([
                    'distribusi_id' => $distribusi->id,
                    'stok_id' => $stok->id,
                    'jumlah_diambil' => $komposisi->jumlah,
                ]);

                $stok->decrement('stock_qty', $komposisi->jumlah);
            }

            $totalDistribusi++;
        }

        $this->command->info("DistribusiPmt berhasil di-seed: {$totalDistribusi} distribusi dengan logika FEFO");
    }
}
