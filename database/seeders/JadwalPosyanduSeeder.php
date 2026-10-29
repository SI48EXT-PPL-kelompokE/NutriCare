<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JadwalPosyandu;
use App\Models\User;
use Carbon\Carbon;

class JadwalPosyanduSeeder extends Seeder
{
    public function run(): void
    {
        $nakesIds = User::whereHas('role', function($q) {
            $q->where('name_role', 'Nakes / Bidan');
        })->where('active_status', true)->pluck('id')->toArray();

        $lokasi = ['Posyandu Melati A', 'Posyandu Mawar B', 'Posyandu Kenanga C'];

        // 2 jadwal sudah lewat
        for ($i = 0; $i < 2; $i++) {
            JadwalPosyandu::create([
                'user_id' => $nakesIds[array_rand($nakesIds)],
                'judul' => 'Posyandu Rutin ' . $lokasi[$i],
                'date' => now()->subDays(rand(7, 30)),
                'hour' => '08:00:00',
                'location' => $lokasi[$i],
                'kuota_jadwal' => rand(25, 35),
            ]);
        }

        // 4 jadwal mendatang
        for ($i = 0; $i < 4; $i++) {
            JadwalPosyandu::create([
                'user_id' => $nakesIds[array_rand($nakesIds)],
                'judul' => 'Posyandu Rutin ' . $lokasi[$i % 3],
                'date' => now()->addDays(rand(3, 30)),
                'hour' => '0' . (8 + $i) . ':00:00',
                'location' => $lokasi[$i % 3],
                'kuota_jadwal' => rand(20, 40),
            ]);
        }

        $this->command->info('JadwalPosyandu berhasil di-seed: 6 jadwal');
    }
}
