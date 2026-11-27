<?php

namespace App\Console\Commands;

use App\Models\PengukuranKms;
use App\Services\StatusGiziService;
use App\Services\ZScoreService;
use Illuminate\Console\Command;

class HitungZScoreCommand extends Command
{
    protected $signature = 'kms:hitung-zscore
                            {--apply : Terapkan perubahan ke database (default: dry-run)}
                            {--status : Hitung ulang status gizi dari z-score tersimpan}
                            {--balita= : Batasi pada satu balita (ID)}';

    protected $description = 'Hitung ulang z-score dan status gizi KMS';

    public function handle(ZScoreService $zScoreService): int
    {
        $apply = (bool) $this->option('apply');
        $balitaId = $this->option('balita');

        if ($this->option('status')) {
            return $this->hitungStatus($apply, $balitaId);
        }

        $this->info('===========================================');
        $this->info('  COMMAND: Hitung Ulang Z-Score KMS');
        $this->info('===========================================');
        $this->info($apply ? 'MODE: APPLY (akan menulis ke database)' : 'MODE: DRY-RUN (tidak mengubah database)');

        $query = PengukuranKms::with('balita');
        if ($balitaId) {
            $query->where('balita_id', $balitaId);
            $this->info("Filter: Balita ID = {$balitaId}");
        }

        $stats = ['total' => 0, 'bbu' => 0, 'bbu_null' => 0, 'tbu_null' => 0, 'bbtb_null' => 0];
        $query->chunkById(200, function ($pengukurans) use ($zScoreService, $apply, &$stats) {
            foreach ($pengukurans as $pengukuran) {
                $stats['total']++;
                $zScores = $zScoreService->untukPengukuran($pengukuran);

                if ($zScores['z_score_bbu'] !== null) {
                    $stats['bbu']++;
                } else {
                    $stats['bbu_null']++;
                }
                if ($zScores['z_score_tbu'] === null) {
                    $stats['tbu_null']++;
                }
                if ($zScores['z_score_bbtb'] === null) {
                    $stats['bbtb_null']++;
                }

                if ($apply) {
                    $pengukuran->update([
                        'z_score_bbu' => $zScores['z_score_bbu'],
                        'z_score_tbu' => $zScores['z_score_tbu'],
                        'z_score_bbtb' => $zScores['z_score_bbtb'],
                    ]);
                }
            }
        });

        $this->newLine();
        $this->info("Total pengukuran diproses: {$stats['total']}");
        $this->info("BB/U terhitung: {$stats['bbu']}");
        $this->info("BB/U null: {$stats['bbu_null']}");
        $this->info("TB/U null: {$stats['tbu_null']}");
        $this->info("BB/TB null: {$stats['bbtb_null']}");
        $this->comment($apply ? 'Perubahan berhasil diterapkan' : 'Gunakan --apply untuk menerapkan perubahan');

        return self::SUCCESS;
    }

    private function hitungStatus(bool $apply, mixed $balitaId): int
    {
        $this->info('===========================================');
        $this->info('  COMMAND: Hitung Ulang Status Gizi');
        $this->info('===========================================');
        $this->newLine();
        $this->info($apply ? 'MODE: APPLY (akan menulis ke database)' : 'MODE: DRY-RUN (tidak mengubah database)');

        $query = PengukuranKms::query();
        if ($balitaId) {
            $query->where('balita_id', $balitaId);
        }

        $total = (clone $query)->count();
        $countsBaru = [];
        $countsLama = [];
        $changed = 0;
        $nulls = 0;

        foreach ($query->cursor() as $pengukuran) {
            $hasil = StatusGiziService::untukPengukuran($pengukuran);
            $baru = $hasil['status']?->value;
            $lama = $pengukuran->getRawOriginal('status_gizi');

            $countsBaru[$baru ?? '(null)'] = ($countsBaru[$baru ?? '(null)'] ?? 0) + 1;
            $countsLama[$lama ?? '(null)'] = ($countsLama[$lama ?? '(null)'] ?? 0) + 1;
            $nulls += $baru === null ? 1 : 0;

            if ($lama !== $baru) {
                $changed++;
                if ($apply) {
                    $pengukuran->update(['status_gizi' => $baru]);
                }
            }
        }

        $this->newLine();
        $this->info("Total pengukuran: {$total}");
        $this->info("Baris berubah: {$changed}");
        $this->info("Status baru null: {$nulls}");
        $this->newLine();
        $this->table(['Status lama', 'Jumlah'], $this->ringkasCounts($countsLama));
        $this->table(['Status baru', 'Jumlah'], $this->ringkasCounts($countsBaru));
        $this->comment($apply ? 'Perubahan status gizi berhasil diterapkan' : 'Gunakan --apply untuk menerapkan perubahan');

        return self::SUCCESS;
    }

    private function ringkasCounts(array $counts): array
    {
        ksort($counts);
        $rows = [];
        foreach ($counts as $status => $jumlah) {
            $rows[] = [$status, $jumlah];
        }
        return $rows;
    }
}
