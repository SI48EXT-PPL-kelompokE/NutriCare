<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reservasi;
use App\Models\JadwalPosyandu;
use App\Models\OrangTua;

class ReservasiSeeder extends Seeder
{
    public function run(): void
    {
        $jadwalMendatang = JadwalPosyandu::where('date', '>', now())->get();
        $orangTuas = OrangTua::with('balita')->limit(15)->get();

        $totalReservasi = 0;
        foreach ($jadwalMendatang as $jadwal) {
            $jumlahReservasi = rand(5, min(15, $jadwal->kuota_jadwal));
            
            foreach ($orangTuas->random($jumlahReservasi) as $orangTua) {
                $balita = $orangTua->balita->first();
                
                Reservasi::create([
                    'jadwal_id' => $jadwal->id,
                    'orang_tua_id' => $orangTua->id,
                    'balita_id' => $balita ? $balita->id : null,
                    'status' => collect(['dikonfirmasi', 'menunggu'])->random(),
                    'batas_reschedule' => $jadwal->date->copy()->subDay(),
                ]);
                $totalReservasi++;
            }
        }

        $this->command->info("Reservasi berhasil di-seed: {$totalReservasi} reservasi");
    }
}
