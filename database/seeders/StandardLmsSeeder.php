<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StandardLms;

class StandardLmsSeeder extends Seeder
{
    /**
     * Seed tabel standard_lms dari semua file CSV di folder data
     *
     * File CSV harus bernama: standard_lms_*.csv
     * Header CSV: indikator, jenis_kelamin, umur_bulan ATAU acuan_nilai, l, m, s, sd_min3..sd_plus3
     * Kedua kolom umur_bulan dan acuan_nilai diterima dan dipetakan ke kolom acuan_nilai
     */
    public function run(): void
    {
        $dataPath = database_path('seeders/data');
        $csvFiles = glob($dataPath . '/standard_lms_*.csv');

        if (empty($csvFiles)) {
            $this->command->error('Tidak ada file standard_lms_*.csv di ' . $dataPath);
            return;
        }

        // Truncate untuk idempoten
        StandardLms::truncate();
        
        $totalData = 0;
        $errors = [];

        foreach ($csvFiles as $csvFile) {
            $fileName = basename($csvFile);
            $this->command->info("Memproses: {$fileName}");

            if (!file_exists($csvFile)) {
                $errors[] = "File tidak ditemukan: {$fileName}";
                continue;
            }

            $handle = fopen($csvFile, 'r');
            $header = fgetcsv($handle);
            
            // Deteksi posisi kolom (umur_bulan atau acuan_nilai)
            $acuanIndex = array_search('acuan_nilai', $header);
            if ($acuanIndex === false) {
                $acuanIndex = array_search('umur_bulan', $header);
            }
            
            if ($acuanIndex === false) {
                $errors[] = "{$fileName}: Header tidak memiliki kolom 'umur_bulan' atau 'acuan_nilai'";
                fclose($handle);
                continue;
            }

            $data = [];
            $lineNumber = 1; // Header = baris 1
            
            while (($row = fgetcsv($handle)) !== false) {
                $lineNumber++;
                
                // Validasi baris
                if (count($row) < 13) {
                    $errors[] = "{$fileName} baris {$lineNumber}: Kolom tidak lengkap (minimal 13 kolom)";
                    continue;
                }

                $jenisKelamin = strtoupper(trim($row[1]));
                if (!in_array($jenisKelamin, ['L', 'P'])) {
                    $errors[] = "{$fileName} baris {$lineNumber}: jenis_kelamin harus 'L' atau 'P', dapat: '{$jenisKelamin}'";
                    continue;
                }

                // Validasi angka untuk kolom numerik
                $numerikValid = true;
                for ($i = 2; $i <= 12; $i++) {
                    if (!is_numeric($row[$i])) {
                        $errors[] = "{$fileName} baris {$lineNumber} kolom {$i}: Nilai tidak numerik '{$row[$i]}'";
                        $numerikValid = false;
                        break;
                    }
                }

                if (!$numerikValid) {
                    continue;
                }

                $data[] = [
                    'indikator' => trim($row[0]),
                    'jenis_kelamin' => $jenisKelamin,
                    'acuan_nilai' => $row[$acuanIndex], // Bisa dari umur_bulan atau acuan_nilai
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
            if (!empty($data)) {
                foreach (array_chunk($data, 50) as $chunk) {
                    StandardLms::insert($chunk);
                }
                $this->command->info("  ✓ {$fileName}: " . count($data) . ' baris berhasil');
                $totalData += count($data);
            }
        }

        // Tampilkan error jika ada
        if (!empty($errors)) {
            $this->command->warn("\nDitemukan " . count($errors) . " baris bermasalah:");
            foreach (array_slice($errors, 0, 10) as $error) {
                $this->command->warn("  - {$error}");
            }
            if (count($errors) > 10) {
                $this->command->warn("  ... dan " . (count($errors) - 10) . " error lainnya");
            }
        }

        $this->command->info("\nTotal Standard LMS berhasil di-seed: {$totalData} baris dari " . count($csvFiles) . " file");
    }
}
