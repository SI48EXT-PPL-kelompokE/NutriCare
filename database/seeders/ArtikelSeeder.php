<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Artikel;
use App\Models\User;
use Illuminate\Support\Str;

class ArtikelSeeder extends Seeder
{
    public function run(): void
    {
        $penulisIds = User::whereHas('role', function($q) {
            $q->whereIn('name_role', ['Admin / Kepala Puskesmas', 'Nakes / Bidan', 'Kader Posyandu']);
        })->pluck('id')->toArray();

        $artikelData = [
            [
                'judul' => 'Pentingnya 1000 Hari Pertama Kehidupan untuk Cegah Stunting',
                'kategori' => 'Pencegahan Stunting',
                'status' => 'published',
                'konten' => '<p>Periode 1000 Hari Pertama Kehidupan (HPK) dimulai dari masa kehamilan hingga anak berusia 2 tahun. Masa ini sangat krusial karena pertumbuhan dan perkembangan otak anak berlangsung sangat pesat.</p><p>Pada masa ini, kecukupan gizi ibu hamil dan balita menentukan kualitas tumbuh kembang anak di masa depan. Kekurangan gizi pada periode ini dapat berdampak permanen dan sulit diperbaiki.</p><p>Oleh karena itu, pastikan ibu hamil mengonsumsi makanan bergizi seimbang, tablet tambah darah, dan memeriksakan kehamilan secara rutin ke fasilitas kesehatan.</p>',
            ],
            [
                'judul' => 'Resep MPASI Sehat untuk Bayi 6-12 Bulan',
                'kategori' => 'MPASI Sehat',
                'status' => 'published',
                'konten' => '<p>MPASI (Makanan Pendamping ASI) mulai diberikan saat bayi berusia 6 bulan. Berikut beberapa resep sederhana yang bisa Bunda coba:</p><p><strong>Bubur Kentang Ayam:</strong> Haluskan kentang rebus dengan kaldu ayam, tambahkan ayam cincang dan sedikit wortel parut. Tekstur disesuaikan dengan usia bayi.</p><p><strong>Bubur Kacang Hijau:</strong> Kacang hijau yang sudah dimasak hingga lunak dicampur dengan ASI atau susu formula untuk menambah nilai gizi.</p><p>Pastikan MPASI mengandung karbohidrat, protein, lemak, vitamin, dan mineral yang cukup untuk mendukung pertumbuhan optimal.</p>',
            ],
            [
                'judul' => 'Gizi Seimbang untuk Ibu Hamil',
                'kategori' => 'Gizi Ibu Hamil',
                'status' => 'published',
                'konten' => '<p>Ibu hamil membutuhkan asupan gizi yang lebih tinggi untuk mendukung pertumbuhan janin. Berikut panduan gizi seimbang:</p><p>Konsumsi makanan sumber protein seperti telur, ikan, daging, tempe, dan tahu. Perbanyak sayuran hijau yang kaya zat besi dan asam folat.</p><p>Minum tablet tambah darah (TTD) secara rutin sesuai anjuran bidan untuk mencegah anemia. Hindari makanan mentah atau setengah matang yang berisiko infeksi.</p>',
            ],
            [
                'judul' => 'Tips Pola Asuh Positif untuk Balita',
                'kategori' => 'Pola Asuh',
                'status' => 'draft',
                'konten' => '<p>Pola asuh yang baik tidak hanya soal memberi makan, tetapi juga stimulasi dan kasih sayang. Luangkan waktu untuk bermain bersama anak, membacakan buku, dan mengajak bicara.</p><p>Berikan pujian saat anak melakukan hal baik, dan hindari hukuman fisik. Ciptakan lingkungan yang aman dan nyaman untuk anak bereksplorasi.</p>',
            ],
            [
                'judul' => 'Jadwal Imunisasi Lengkap untuk Balita',
                'kategori' => 'Layanan Kesehatan',
                'status' => 'published',
                'konten' => '<p>Imunisasi adalah hak setiap anak dan sangat penting untuk melindungi dari penyakit berbahaya. Berikut jadwal imunisasi dasar lengkap:</p><p>0 bulan: Hepatitis B-0, 1 bulan: BCG dan Polio 1, 2 bulan: DPT-HB-Hib 1 dan Polio 2, 3 bulan: DPT-HB-Hib 2 dan Polio 3, 4 bulan: DPT-HB-Hib 3 dan Polio 4 serta IPV, 9 bulan: Campak/MR.</p><p>Pastikan anak mendapatkan imunisasi sesuai jadwal di Posyandu atau Puskesmas terdekat.</p>',
            ],
            [
                'judul' => 'Mengenal Tanda-Tanda Gizi Buruk pada Balita',
                'kategori' => 'Pencegahan Stunting',
                'status' => 'published',
                'konten' => '<p>Gizi buruk dapat dikenali dari beberapa tanda fisik: berat badan sangat rendah, tampak sangat kurus, perut buncit, wajah seperti orang tua, rambut tipis dan mudah rontok, serta anak tampak lesu dan tidak aktif.</p><p>Jika menemukan tanda-tanda tersebut, segera bawa anak ke Puskesmas atau fasilitas kesehatan untuk mendapat penanganan.</p>',
            ],
            [
                'judul' => 'Peran Ayah dalam Pengasuhan dan Gizi Anak',
                'kategori' => 'Pola Asuh',
                'status' => 'published',
                'konten' => '<p>Ayah memiliki peran penting dalam pengasuhan anak. Dukungan ayah membantu mengurangi beban ibu dan menciptakan lingkungan keluarga yang harmonis.</p><p>Ayah dapat terlibat dalam membantu menyiapkan makanan, menemani anak bermain, dan mengajak anak ke Posyandu untuk pemeriksaan kesehatan.</p>',
            ],
            [
                'judul' => 'Manfaat Posyandu untuk Tumbuh Kembang Anak',
                'kategori' => 'Layanan Kesehatan',
                'status' => 'draft',
                'konten' => '<p>Posyandu adalah layanan kesehatan dasar yang sangat bermanfaat untuk memantau tumbuh kembang anak. Di Posyandu, anak akan ditimbang, diukur tingginya, dan diperiksa status gizinya.</p><p>Selain itu, orang tua bisa berkonsultasi dengan kader atau tenaga kesehatan mengenai masalah kesehatan dan gizi anak.</p>',
            ],
        ];

        foreach ($artikelData as $data) {
            Artikel::create([
                'user_id' => $penulisIds[array_rand($penulisIds)],
                'judul' => $data['judul'],
                'kategori' => $data['kategori'],
                'status' => $data['status'],
                'url_slug' => Str::slug($data['judul']),
                'konten' => $data['konten'],
                'published_at' => $data['status'] == 'published' ? now()->subDays(rand(1, 30)) : null,
            ]);
        }

        $this->command->info('Artikel berhasil di-seed: 8 artikel');
    }
}
