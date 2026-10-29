<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SuratRujukan;
use App\Models\Balita;
use App\Models\User;
use Illuminate\Support\Str;

class SuratRujukanSeeder extends Seeder
{
    public function run(): void
    {
        $nakesIds = User::whereHas('role', function($q) {
            $q->where('name_role', 'Nakes / Bidan');
        })->pluck('id')->toArray();

        $balitaGiziBuruk = Balita::whereHas('pengukuranKms', function($q) {
            $q->whereIn('status_gizi', ['Gizi Buruk', 'Stunting'])
              ->whereRaw('id IN (SELECT MAX(id) FROM pengukuran_kms GROUP BY balita_id)');
        })->limit(5)->get();

        $faskesTujuan = ['RSUD Kota', 'RS Ibu dan Anak', 'Puskesmas Rujukan'];
        $status = ['menunggu', 'diterima', 'selesai'];

        $nomor = 1;
        foreach ($balitaGiziBuruk as $balita) {
            SuratRujukan::create([
                'balita_id' => $balita->id,
                'user_id' => $nakesIds[array_rand($nakesIds)],
                'nomor_surat' => sprintf('RJK/2026/%04d', $nomor++),
                'faskes_tujuan' => $faskesTujuan[array_rand($faskesTujuan)],
                'status_follow_up' => $status[array_rand($status)],
                'alasan' => 'Balita mengalami gizi buruk/stunting, perlu penanganan lanjutan di fasilitas kesehatan rujukan.',
                'qr_verification_code' => 'QR-' . strtoupper(Str::random(12)),
            ]);
        }

        $this->command->info('SuratRujukan berhasil di-seed: 5 surat');
    }
}
