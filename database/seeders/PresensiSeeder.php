<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Presensi;
use App\Models\JadwalPosyandu;
use App\Models\Balita;

class PresensiSeeder extends Seeder
{
    public function run(): void
    {
        $jadwalLewat = JadwalPosyandu::where('date', '<', now())->get();
        $balitas = Balita::limit(20)->get();

        $totalPresensi = 0;
        foreach ($jadwalLewat as $jadwal) {
            foreach ($balitas->random(rand(10, 15)) as $balita) {
                try {
                    Presensi::create([
                        'jadwal_id' => $jadwal->id,
                        'balita_id' => $balita->id,
                        'presence_status' => collect(['hadir', 'tidak_hadir'])->random(),
                    ]);
                    $totalPresensi++;
                } catch (\Exception $e) {
                    // Skip jika duplikat
                }
            }
        }

        $this->command->info("Presensi berhasil di-seed: {$totalPresensi} presensi");
    }
}
