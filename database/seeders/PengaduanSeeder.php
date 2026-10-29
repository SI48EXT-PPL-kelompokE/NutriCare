<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pengaduan;
use App\Models\OrangTua;
use App\Models\User;

class PengaduanSeeder extends Seeder
{
    public function run(): void
    {
        $adminIds = User::whereHas('role', function($q) {
            $q->where('name_role', 'Admin / Kepala Puskesmas');
        })->pluck('id')->toArray();

        $orangTuas = OrangTua::limit(10)->get();

        $kategori = ['Distribusi PMT', 'Layanan Posyandu', 'Data KMS', 'Antrean Posyandu'];
        
        $pengaduanData = [
            ['judul' => 'PMT belum diterima bulan ini', 'isi' => 'Anak saya sudah terdaftar untuk menerima PMT tetapi sampai akhir bulan belum ada pemberitahuan.', 'anonim' => false],
            ['judul' => 'Jadwal Posyandu tidak jelas', 'isi' => 'Informasi jadwal Posyandu sering berubah tanpa pemberitahuan, mohon diperbaiki sistem informasinya.', 'anonim' => false],
            ['judul' => 'Data berat badan anak tidak sesuai', 'isi' => 'Saat saya cek di aplikasi, data berat badan anak bulan lalu berbeda dengan catatan buku KMS saya.', 'anonim' => false],
            ['judul' => 'Antrean Posyandu terlalu lama', 'isi' => 'Kemarin saya menunggu hampir 2 jam di Posyandu, apakah bisa ada sistem antrean online?', 'anonim' => true],
            ['judul' => 'Kualitas PMT kurang baik', 'isi' => 'PMT yang diterima minggu lalu sudah hampir kadaluarsa, mohon perhatiannya.', 'anonim' => false],
            ['judul' => 'Pelayanan kader kurang ramah', 'isi' => 'Beberapa kader kurang ramah saat melayani, semoga bisa ditingkatkan.', 'anonim' => true],
            ['judul' => 'Permintaan tambahan lokasi Posyandu', 'isi' => 'Di wilayah kami belum ada Posyandu, mohon dipertimbangkan untuk dibuka.', 'anonim' => false],
            ['judul' => 'Saran untuk aplikasi', 'isi' => 'Aplikasi bagus, tapi tolong ditambahkan fitur notifikasi untuk jadwal imunisasi.', 'anonim' => false],
            ['judul' => 'PMT tidak sesuai usia anak', 'isi' => 'Anak saya usia 8 bulan tetapi menerima PMT untuk usia 2 tahun, mohon koreksi.', 'anonim' => false],
            ['judul' => 'Terima kasih untuk pelayanan', 'isi' => 'Saya sangat terbantu dengan adanya aplikasi dan pelayanan Posyandu yang baik.', 'anonim' => false],
        ];

        foreach ($pengaduanData as $index => $data) {
            $status = ['baru', 'diproses', 'selesai'][rand(0, 2)];
            $orangTua = $data['anonim'] && rand(1, 100) <= 50 ? null : $orangTuas[$index];
            
            Pengaduan::create([
                'user_id' => in_array($status, ['diproses', 'selesai']) ? $adminIds[array_rand($adminIds)] : null,
                'orang_tua_id' => $orangTua ? $orangTua->id : null,
                'kategori' => $kategori[array_rand($kategori)],
                'judul' => $data['judul'],
                'isi' => $data['isi'],
                'is_anonymous' => $data['anonim'],
                'foto_bukti_path' => null,
                'status' => $status,
                'catatan' => $status == 'selesai' ? 'Pengaduan telah ditindaklanjuti dan diselesaikan.' : null,
                'nomor_tiket' => 'PGD-' . (1001 + $index),
            ]);
        }

        $this->command->info('Pengaduan berhasil di-seed: 10 pengaduan');
    }
}
