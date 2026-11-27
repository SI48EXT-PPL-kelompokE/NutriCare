<?php

namespace App\Services;

use App\Models\Balita;
use App\Models\JadwalPosyandu;
use App\Models\Presensi;
use App\Models\Reservasi;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PresensiService
{
    /**
     * Catat kehadiran balita via scan QR kartu identitas balita.
     * Mencegah duplikasi kehadiran dan menangani scan berulang secara idempotent.
     *
     * @throws ValidationException
     */
    public function checkInViaQr(int $jadwalId, string $qrCode): array
    {
        $jadwal = JadwalPosyandu::find($jadwalId);
        if (!$jadwal) {
            throw ValidationException::withMessages([
                'jadwal_id' => 'Jadwal posyandu tidak ditemukan.',
            ]);
        }

        if ($jadwal->status === 'dibatalkan') {
            throw ValidationException::withMessages([
                'jadwal_id' => 'Jadwal posyandu telah dibatalkan.',
            ]);
        }

        $cleanQr = trim($qrCode);
        $balita = Balita::with('orangTua')->where('qr_code', $cleanQr)->first();

        if (!$balita) {
            throw ValidationException::withMessages([
                'qr_code' => "QR Code '{$cleanQr}' tidak valid atau tidak terdaftar dalam sistem.",
            ]);
        }

        return DB::transaction(function () use ($jadwal, $balita) {
            // Pessimistic lock untuk deteksi presensi dan perlindungan race condition
            $existing = Presensi::where('jadwal_id', $jadwal->id)
                ->where('balita_id', $balita->id)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return [
                    'status' => 'already_checked_in',
                    'is_new' => false,
                    'message' => "Balita {$balita->name} sudah terpresensi sebelumnya pada pukul " . $existing->created_at->format('H:i') . ' WIB.',
                    'presensi' => $existing,
                    'balita' => $balita,
                ];
            }

            $presensi = Presensi::create([
                'jadwal_id' => $jadwal->id,
                'balita_id' => $balita->id,
                'presence_status' => 'hadir',
            ]);

            // Jika balita memiliki reservasi aktif pada jadwal ini, update statusnya menjadi selesai
            Reservasi::where('jadwal_id', $jadwal->id)
                ->where('balita_id', $balita->id)
                ->whereIn('status', ['menunggu', 'dikonfirmasi'])
                ->update(['status' => 'selesai']);

            return [
                'status' => 'success',
                'is_new' => true,
                'message' => "Check-in berhasil! Kehadiran balita {$balita->name} telah dicatat.",
                'presensi' => $presensi,
                'balita' => $balita,
            ];
        });
    }

    /**
     * Hitung rekap kehadiran untuk jadwal tertentu.
     */
    public function getRekapPresensi(int $jadwalId): array
    {
        $jadwal = JadwalPosyandu::findOrFail($jadwalId);
        $hadir = Presensi::where('jadwal_id', $jadwalId)->where('presence_status', 'hadir')->count();
        $tidakHadir = Presensi::where('jadwal_id', $jadwalId)->where('presence_status', 'tidak_hadir')->count();
        $totalPresensi = $hadir + $tidakHadir;
        $totalReservasi = Reservasi::where('jadwal_id', $jadwalId)->where('status', '!=', 'dibatalkan')->count();

        return [
            'jadwal' => $jadwal,
            'hadir' => $hadir,
            'tidak_hadir' => $tidakHadir,
            'total_presensi' => $totalPresensi,
            'total_reservasi' => $totalReservasi,
            'kuota' => $jadwal->kuota_jadwal,
            'persentase_kehadiran' => $totalReservasi > 0 ? round(($hadir / $totalReservasi) * 100, 1) : 0,
        ];
    }

    /**
     * Koreksi status presensi.
     */
    public function updateStatus(Presensi $presensi, string $status): Presensi
    {
        if (!in_array($status, ['hadir', 'tidak_hadir'], true)) {
            throw ValidationException::withMessages([
                'presence_status' => 'Status kehadiran tidak valid. Pilih hadir atau tidak_hadir.',
            ]);
        }

        $presensi->update([
            'presence_status' => $status,
        ]);

        return $presensi->fresh();
    }

    /**
     * Hapus catatan presensi.
     */
    public function deletePresensi(Presensi $presensi): bool
    {
        return (bool) $presensi->delete();
    }
}
