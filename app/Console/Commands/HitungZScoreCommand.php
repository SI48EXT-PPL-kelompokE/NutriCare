<?php

namespace App\Console\Commands;

use App\Models\PengukuranKms;
use App\Services\ZScoreService;
use Illuminate\Console\Command;

class HitungZScoreCommand extends Command
{
    /**
     * Nama dan signature command
     */
    protected $signature = 'kms:hitung-zscore
                            {--apply : Terapkan perubahan ke database (default: dry-run)}
                            {--balita= : Batasi pada satu balita (ID)}';

    /**
     * Deskripsi command
     */
    protected $description = 'Hitung ulang z-score untuk data pengukuran KMS';

    /**
     * Execute command
     */
    public function handle(ZScoreService $zScoreService): int
    {
        $apply = $this->option('apply');
        $balitaId = $this->option('balita');

        $this->info('===========================================');
        $this->info('  COMMAND: Hitung Ulang Z-Score KMS');
        $this->info('===========================================');
        $this->newLine();

        if ($apply) {
            $this->warn('⚠️  MODE: APPLY (akan menulis ke database)');
            
            if (!$this->option('no-interaction')) {
                $this->warn('PERINGATAN:');
                $this->warn('- Kolom z_score_bbu akan dihitung ulang dari data LMS WHO');
                $this->warn('- Kolom z_score_tbu dan z_score_bbtb akan dikosongkan (null)');
                $this->warn('  bila tabel LMS untuk TB_U, BB_PB, atau BB_TB belum dimuat');
                $this->newLine();
                
                if (!$this->confirm('Lanjutkan?', false)) {
                    $this->info('Command dibatalkan.');
                    return self::SUCCESS;
                }
            }
        } else {
            $this->info('📊 MODE: DRY-RUN (tidak mengubah database)');
            $this->info('    Gunakan --apply untuk menerapkan perubahan');
        }
        
        $this->newLine();

        // Muat semua data LMS ke memori sekali
        $this->info('Memuat tabel LMS ke memori...');
        $zScoreService->muatSemua();
        $this->info('✓ Tabel LMS dimuat');
        $this->newLine();

        // Query pengukuran
        $query = PengukuranKms::with('balita');
        
        if ($balitaId) {
            $query->where('balita_id', $balitaId);
            $this->info("Filter: Balita ID = {$balitaId}");
        }

        $totalPengukuran = $query->count();
        $this->info("Total pengukuran: {$totalPengukuran}");
        $this->newLine();

        // Statistik
        $stats = [
            'total' => 0,
            'bbu_terhitung' => 0,
            'bbu_null' => 0,
            'tbu_akan_null' => 0,
            'bbtb_akan_null' => 0,
            'selisih_bbu' => [],
            'selisih_besar' => 0,
        ];

        // Progress bar
        $bar = $this->output->createProgressBar($totalPengukuran);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $bar->setMessage('Memproses...');
        $bar->start();

        // Proses dengan chunk
        $query->chunkById(200, function ($pengukurans) use ($zScoreService, $apply, &$stats, $bar) {
            foreach ($pengukurans as $p) {
                $stats['total']++;

                // Hitung z-score baru
                $zScores = $zScoreService->untukPengukuran($p);

                // Statistik BB/U
                if ($zScores['z_score_bbu'] !== null) {
                    $stats['bbu_terhitung']++;
                    
                    // Hitung selisih dengan nilai lama
                    if ($p->z_score_bbu !== null) {
                        $selisih = abs($zScores['z_score_bbu'] - $p->z_score_bbu);
                        $stats['selisih_bbu'][] = $selisih;
                        
                        if ($selisih > 0.03) {
                            $stats['selisih_besar']++;
                        }
                    }
                } else {
                    $stats['bbu_null']++;
                }

                // Statistik TB/U dan BB/TB yang akan menjadi null
                if ($zScores['z_score_tbu'] === null && $p->z_score_tbu !== null) {
                    $stats['tbu_akan_null']++;
                }
                if ($zScores['z_score_bbtb'] === null && $p->z_score_bbtb !== null) {
                    $stats['bbtb_akan_null']++;
                }

                // Terapkan jika apply mode
                if ($apply) {
                    $p->update([
                        'z_score_bbu' => $zScores['z_score_bbu'],
                        'z_score_tbu' => $zScores['z_score_tbu'],
                        'z_score_bbtb' => $zScores['z_score_bbtb'],
                    ]);
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);

        // Tampilkan ringkasan
        $this->info('===========================================');
        $this->info('  RINGKASAN HASIL');
        $this->info('===========================================');
        $this->newLine();

        $this->info("Total pengukuran diproses: {$stats['total']}");
        $this->newLine();

        $this->info('Z-score BB/U (Berat Badan menurut Umur):');
        $this->info("  ✓ Terhitung: {$stats['bbu_terhitung']}");
        $this->info("  ✗ Null (data acuan tidak tersedia): {$stats['bbu_null']}");
        
        if (!empty($stats['selisih_bbu'])) {
            $maxSelisih = max($stats['selisih_bbu']);
            $avgSelisih = array_sum($stats['selisih_bbu']) / count($stats['selisih_bbu']);
            
            $this->newLine();
            $this->info('Selisih dengan nilai tersimpan:');
            $this->info(sprintf('  Maksimum: %.4f SD', $maxSelisih));
            $this->info(sprintf('  Rata-rata: %.4f SD', $avgSelisih));
            $this->info("  Selisih > 0,03: {$stats['selisih_besar']} pengukuran");
        }

        $this->newLine();
        $this->warn('Z-score TB/U dan BB/TB (tabel belum dimuat):');
        if ($stats['tbu_akan_null'] > 0) {
            $this->warn("  TB/U akan menjadi null: {$stats['tbu_akan_null']}");
        }
        if ($stats['bbtb_akan_null'] > 0) {
            $this->warn("  BB/TB akan menjadi null: {$stats['bbtb_akan_null']}");
        }
        if ($stats['tbu_akan_null'] === 0 && $stats['bbtb_akan_null'] === 0) {
            $this->info('  (tidak ada perubahan)');
        }

        $this->newLine();

        if ($apply) {
            $this->info('✓ Perubahan berhasil diterapkan ke database');
        } else {
            $this->comment('💡 Gunakan opsi --apply untuk menerapkan perubahan');
        }

        return self::SUCCESS;
    }
}
