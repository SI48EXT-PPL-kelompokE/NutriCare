<?php

namespace App\Services;

use App\Models\Balita;
use App\Models\JadwalPosyandu;
use App\Models\Reservasi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservasiService
{
    /**
     * Hitung batas waktu reschedule (maksimal H-1 kegiatan).
     * Dihitung tepat 24 jam sebelum waktu mulai jadwal posyandu.
     */
    public function calculateBatasReschedule(JadwalPosyandu $jadwal): Carbon
    {
        $dateStr = $jadwal->date instanceof Carbon ? $jadwal->date->format('Y-m-d') : (string) $jadwal->date;
        $jamMulai = $jadwal->jam_mulai ?? $jadwal->hour ?? '08:00:00';
        
        $startDateTime = Carbon::parse($dateStr . ' ' . $jamMulai, config('app.timezone', 'Asia/Jakarta'));
        return $startDateTime->subDay();
    }

    /**
     * Buat reservasi baru dengan proteksi kuota atomik dan cegah double booking.
     *
     * @throws ValidationException
     */
    public function createReservasi(array $data, User $actor): Reservasi
    {
        $balita = Balita::findOrFail($data['balita_id']);

        // Verifikasi kepemilikan orang tua untuk Warga
        if ($actor->role?->name_role === 'Warga / Orang Tua') {
            $orangTua = $actor->orangTua;
            if (!$orangTua) {
                throw ValidationException::withMessages([
                    'balita_id' => 'Akun pengguna belum terhubung dengan data Orang Tua warga.',
                ]);
            }

            if ($balita->orang_tua_id !== $orangTua->id) {
                throw ValidationException::withMessages([
                    'balita_id' => 'Anda hanya berhak membuat reservasi untuk balita dalam kartu keluarga Anda sendiri.',
                ]);
            }
            $orangTuaId = $orangTua->id;
        } else {
            $orangTuaId = $data['orang_tua_id'] ?? $balita->orang_tua_id;
        }

        return DB::transaction(function () use ($data, $balita, $orangTuaId) {
            // Lock jadwal untuk memastikan kuota tidak terlewati pada request bersamaan
            $jadwal = JadwalPosyandu::where('id', $data['jadwal_id'])->lockForUpdate()->first();
            if (!$jadwal) {
                throw ValidationException::withMessages([
                    'jadwal_id' => 'Jadwal posyandu tidak ditemukan.',
                ]);
            }

            if ($jadwal->status === 'dibatalkan') {
                throw ValidationException::withMessages([
                    'jadwal_id' => 'Jadwal posyandu ini telah dibatalkan.',
                ]);
            }

            if ($jadwal->date->isPast() && !$jadwal->date->isToday()) {
                throw ValidationException::withMessages([
                    'jadwal_id' => 'Tidak dapat melakukan reservasi pada jadwal yang telah lewat.',
                ]);
            }

            // Cek duplikasi reservasi aktif untuk balita yang sama pada jadwal ini
            $hasActiveReservation = Reservasi::where('jadwal_id', $jadwal->id)
                ->where('balita_id', $balita->id)
                ->where('status', '!=', 'dibatalkan')
                ->lockForUpdate()
                ->exists();

            if ($hasActiveReservation) {
                throw ValidationException::withMessages([
                    'balita_id' => "Balita {$balita->name} sudah memiliki reservasi aktif pada jadwal ini.",
                ]);
            }

            // Hitung kuota terpakai di bawah pessimistic lock
            $activeCount = Reservasi::where('jadwal_id', $jadwal->id)
                ->where('status', '!=', 'dibatalkan')
                ->lockForUpdate()
                ->count();

            if ($activeCount >= $jadwal->kuota_jadwal) {
                throw ValidationException::withMessages([
                    'jadwal_id' => "Kuota reservasi untuk jadwal '{$jadwal->judul}' sudah penuh ({$jadwal->kuota_jadwal} slot terisi).",
                ]);
            }

            return Reservasi::create([
                'jadwal_id' => $jadwal->id,
                'orang_tua_id' => $orangTuaId,
                'balita_id' => $balita->id,
                'status' => 'dikonfirmasi',
                'batas_reschedule' => $this->calculateBatasReschedule($jadwal),
            ]);
        });
    }

    /**
     * Reschedule reservasi ke jadwal lain secara atomik.
     * Jika gagal atau kuota tujuan penuh, jadwal asal tetap utuh.
     * Menggunakan urutan locking deterministik berdasarkan ID untuk mencegah deadlock.
     *
     * @throws ValidationException
     */
    public function reschedule(Reservasi $reservasi, int $newJadwalId): Reservasi
    {
        if (!$reservasi->canReschedule()) {
            throw ValidationException::withMessages([
                'batas_reschedule' => 'Batas waktu reschedule telah berakhir (maksimal H-1 kegiatan) atau status reservasi tidak dapat diubah.',
            ]);
        }

        if ($reservasi->jadwal_id === $newJadwalId) {
            throw ValidationException::withMessages([
                'new_jadwal_id' => 'Jadwal tujuan baru harus berbeda dari jadwal saat ini.',
            ]);
        }

        // Deterministic lock order (ID terkecil dahulu)
        $scheduleIds = [$reservasi->jadwal_id, $newJadwalId];
        sort($scheduleIds);

        return DB::transaction(function () use ($reservasi, $newJadwalId, $scheduleIds) {
            $lockedSchedules = JadwalPosyandu::whereIn('id', $scheduleIds)
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $newJadwal = $lockedSchedules->get($newJadwalId);
            if (!$newJadwal || $newJadwal->status === 'dibatalkan') {
                throw ValidationException::withMessages([
                    'new_jadwal_id' => 'Jadwal tujuan tidak valid atau telah dibatalkan.',
                ]);
            }

            if ($newJadwal->date->isPast() && !$newJadwal->date->isToday()) {
                throw ValidationException::withMessages([
                    'new_jadwal_id' => 'Tidak dapat memindahkan reservasi ke jadwal yang telah lewat.',
                ]);
            }

            // Cek apakah balita sudah memiliki reservasi aktif di jadwal tujuan
            $alreadyBookedOnNew = Reservasi::where('jadwal_id', $newJadwalId)
                ->where('balita_id', $reservasi->balita_id)
                ->where('status', '!=', 'dibatalkan')
                ->where('id', '!=', $reservasi->id)
                ->lockForUpdate()
                ->exists();

            if ($alreadyBookedOnNew) {
                throw ValidationException::withMessages([
                    'new_jadwal_id' => 'Balita sudah memiliki reservasi aktif pada jadwal tujuan.',
                ]);
            }

            // Cek kuota jadwal tujuan
            $newActiveCount = Reservasi::where('jadwal_id', $newJadwalId)
                ->where('status', '!=', 'dibatalkan')
                ->lockForUpdate()
                ->count();

            if ($newActiveCount >= $newJadwal->kuota_jadwal) {
                throw ValidationException::withMessages([
                    'new_jadwal_id' => "Kuota jadwal tujuan '{$newJadwal->judul}' sudah penuh.",
                ]);
            }

            // Pemindahan atomik
            $reservasi->update([
                'jadwal_id' => $newJadwalId,
                'batas_reschedule' => $this->calculateBatasReschedule($newJadwal),
            ]);

            return $reservasi->fresh(['jadwal', 'balita', 'orangTua']);
        });
    }

    /**
     * Batalkan reservasi (slot kuota kembali tersedia).
     *
     * @throws ValidationException
     */
    public function cancelReservasi(Reservasi $reservasi): Reservasi
    {
        return DB::transaction(function () use ($reservasi) {
            $locked = Reservasi::where('id', $reservasi->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === 'dibatalkan') {
                return $locked;
            }

            if ($locked->status === 'selesai') {
                throw ValidationException::withMessages([
                    'status' => 'Reservasi yang sudah selesai dihadiri tidak dapat dibatalkan.',
                ]);
            }

            $locked->update(['status' => 'dibatalkan']);
            return $locked->fresh(['jadwal', 'balita', 'orangTua']);
        });
    }
}
