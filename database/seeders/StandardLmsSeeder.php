<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StandardLms;

class StandardLmsSeeder extends Seeder
{
    public function run(): void
    {
        $csvFile = database_path('seeders/data/standard_lms_bb_u.csv');
        
        if (!file_exists($csvFile)) {
            $this->command->error('File CSV tidak ditemukan: ' . $csvFile);
            return;
        }

        $handle = fopen($csvFile, 'r');
        $header = fgetcsv($handle); // Skip header
        
        $data = [];
        while (($row = fgetcsv($handle)) !== false) {
            $data[] = [
                'indikator' => $row[0],
                'jenis_kelamin' => $row[1],
                'acuan_nilai' => $row[2], // umur_bulan dipetakan ke acuan_nilai
                'l' => $row[3],
                'm' => $row[4],
                's' => $row[5],
                'sd_min3' => $row[6],
                'sd_min2' => $row[7],
                'sd_min1' => $row[8],
                'sd_0' => $row[9],
                'sd_plus1' => $row[10],
                'sd_plus2' => $row[11],
                'sd_plus3' => $row[12],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        fclose($handle);

        // Insert batch dengan chunk untuk efisiensi
        foreach (array_chunk($data, 50) as $chunk) {
            StandardLms::insert($chunk);
        }

        $this->command->info('Standard LMS berhasil di-seed: ' . count($data) . ' baris');
    }
}
