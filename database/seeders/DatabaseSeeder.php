<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Set seed faker untuk konsistensi data di semua komputer
        fake()->seed(20260101);

        $this->command->info('🌱 Mulai seeding database NutriCare...');

        // Urutan seeder menghormati foreign key dependencies
        $this->call([
            // 1. Data referensi (tidak ada FK)
            StandardLmsSeeder::class,
            
            // 2. Role (tidak ada FK)
            RoleSeeder::class,
            
            // 3. User dan Permission (depends on Role)
            UserSeeder::class,
            PermissionSeeder::class,
            
            // 4. OrangTua (depends on User)
            OrangTuaSeeder::class,
            
            // 5. Balita (depends on OrangTua)
            BalitaSeeder::class,
            
            // 6. Data klinis balita (depends on Balita, User)
            PengukuranKmsSeeder::class,
            ImunisasiSeeder::class,
            PemberianVitaminSeeder::class,
            
            // 7. PMT: Barang -> Paket -> Stok -> Distribusi
            BarangPmtSeeder::class,
            PaketPmtSeeder::class, // Juga membuat KomposisiPaketPmt
            StokGudangSeeder::class,
            DistribusiPmtSeeder::class, // Juga membuat DetailDistribusiStok
            
            // 8. Jadwal -> Reservasi/Presensi (depends on User, OrangTua, Balita)
            JadwalPosyanduSeeder::class,
            ReservasiSeeder::class,
            PresensiSeeder::class,
            
            // 9. Surat Rujukan (depends on Balita, User)
            SuratRujukanSeeder::class,
            
            // 10. Artikel, Konseling, Pengaduan
            ArtikelSeeder::class,
            SesiKonselingSeeder::class,
            PengaduanSeeder::class,
        ]);

        $this->command->info('✅ Seeding selesai!');
        $this->command->info('');
        $this->command->info('📊 Akun Dummy untuk Testing:');
        $this->command->table(
            ['Username', 'Password', 'Role'],
            [
                ['admin', 'password', 'Admin / Kepala Puskesmas'],
                ['bidan1', 'password', 'Nakes / Bidan'],
                ['bidan2', 'password', 'Nakes / Bidan (Nonaktif)'],
                ['bidan3', 'password', 'Nakes / Bidan'],
                ['kader1', 'password', 'Kader Posyandu'],
                ['kader2', 'password', 'Kader Posyandu'],
                ['kader3', 'password', 'Kader Posyandu'],
                ['warga1-warga10', 'password', 'Warga / Orang Tua'],
            ]
        );
    }
}
