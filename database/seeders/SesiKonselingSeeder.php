<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SesiKonseling;
use App\Models\OrangTua;
use App\Models\User;

class SesiKonselingSeeder extends Seeder
{
    public function run(): void
    {
        $nakesIds = User::whereHas('role', function($q) {
            $q->where('name_role', 'Nakes / Bidan');
        })->pluck('id')->toArray();

        $orangTuas = OrangTua::limit(8)->get();

        $topik = [
            'Konseling Gizi Balita',
            'MPASI dan Pola Makan Sehat',
            'Gizi Ibu Hamil',
            'Stimulasi Tumbuh Kembang',
        ];

        foreach ($orangTuas as $index => $orangTua) {
            $status = ['diajukan', 'dijadwalkan', 'selesai'][rand(0, 2)];
            
            SesiKonseling::create([
                'orang_tua_id' => $orangTua->id,
                'user_id' => in_array($status, ['dijadwalkan', 'selesai']) ? $nakesIds[array_rand($nakesIds)] : null,
                'topik' => $topik[array_rand($topik)],
                'jadwal' => in_array($status, ['dijadwalkan', 'selesai']) ? now()->addDays(rand(-7, 14)) : null,
                'status' => $status,
                'hasil' => $status == 'selesai' ? 'Konseling telah dilakukan, orang tua diberikan edukasi mengenai pola makan sehat dan variasi menu MPASI.' : null,
            ]);
        }

        $this->command->info('SesiKonseling berhasil di-seed: 8 sesi');
    }
}
