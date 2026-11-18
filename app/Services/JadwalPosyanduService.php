<?php

namespace App\Services;

use App\Contracts\Paket4\NakesLookup;
use App\Models\JadwalPosyandu;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JadwalPosyanduService
{
    public function __construct(
        protected NakesLookup $nakesLookup
    ) {}

    /**
     * Validasi urutan waktu: jam_selesai harus lebih besar dari jam_mulai.
     *
     * @throws ValidationException
     */
    public function validateInterval(string $jamMulai, string $jamSelesai): void
    {
        $start = Carbon::createFromFormat('H:i', substr($jamMulai, 0, 5));
        $end = Carbon::createFromFormat('H:i', substr($jamSelesai, 0, 5));

        if ($end->lessThanOrEqualTo($start)) {
            throw ValidationException::withMessages([
                'jam_selesai' => 'Jam selesai kegiatan harus lebih besar dari jam mulai.',
            ]);
        }
    }

    /**
     * Simpan jadwal baru dengan locking baris nakes atomik dan pencegahan bentrok.
     *
     * @param array{
     *     user_id: int,
     *     judul: string,
     *     date: string,
     *     jam_mulai: string,
     *     jam_selesai: string,
     *     location: string,
     *     kuota_jadwal: int
     * } $data
     * @return JadwalPosyandu
     * @throws ValidationException
     */
    public function createJadwal(array $data): JadwalPosyandu
    {
        $this->validateInterval($data['jam_mulai'], $data['jam_selesai']);

        return DB::transaction(function () use ($data) {
            // 1. Serialisasi konkurensi: Lock baris nakes stabil
            $nakes = User::where('id', $data['user_id'])->lockForUpdate()->first();

            if (!$nakes || !$nakes->active_status) {
                throw ValidationException::withMessages([
                    'user_id' => 'Tenaga kesehatan yang dipilih tidak ditemukan atau sedang tidak aktif.',
                ]);
            }

            // 2. Deteksi bentrok interval [mulai, selesai)
            $conflict = JadwalPosyandu::overlap(
                $data['user_id'],
                $data['date'],
                $data['jam_mulai'],
                $data['jam_selesai']
            )->first();

            if ($conflict) {
                throw ValidationException::withMessages([
                    'jam_mulai' => "Nakes {$nakes->name} telah memiliki jadwal lain ({$conflict->judul}) pada rentang waktu {$conflict->hour} s.d. {$conflict->jam_selesai}.",
                ]);
            }

            // 3. Simpan jadwal posyandu
            return JadwalPosyandu::create([
                'user_id' => $data['user_id'],
                'judul' => $data['judul'],
                'date' => $data['date'],
                'hour' => $data['jam_mulai'],
                'jam_selesai' => $data['jam_selesai'],
                'location' => $data['location'],
                'kuota_jadwal' => (int) $data['kuota_jadwal'],
                'status' => 'aktif',
            ]);
        });
    }

    /**
     * Perbarui jadwal posyandu dengan penguncian urutan konsisten dan proteksi kuota.
     *
     * @param JadwalPosyandu $jadwal
     * @param array{
     *     user_id?: int,
     *     judul?: string,
     *     date?: string,
     *     jam_mulai?: string,
     *     jam_selesai?: string,
     *     location?: string,
     *     kuota_jadwal?: int,
     *     status?: string
     * } $data
     * @return JadwalPosyandu
     * @throws ValidationException
     */
    public function updateJadwal(JadwalPosyandu $jadwal, array $data): JadwalPosyandu
    {
        $newUserId = (int) ($data['user_id'] ?? $jadwal->user_id);
        $newDate = $data['date'] ?? (is_string($jadwal->date) ? $jadwal->date : $jadwal->date->format('Y-m-d'));
        $newJamMulai = $data['jam_mulai'] ?? $jadwal->hour;
        $newJamSelesai = $data['jam_selesai'] ?? $jadwal->jam_selesai;

        if ($newJamMulai && $newJamSelesai) {
            $this->validateInterval($newJamMulai, $newJamSelesai);
        }

        return DB::transaction(function () use ($jadwal, $data, $newUserId, $newDate, $newJamMulai, $newJamSelesai) {
            // 1. Lock baris nakes lama & baru dalam urutan ID konsisten (mencegah deadlock)
            $nakesIds = array_unique([$jadwal->user_id, $newUserId]);
            sort($nakesIds);
            User::whereIn('id', $nakesIds)->lockForUpdate()->get();

            // 2. Lock baris jadwal yang sedang diedit
            /** @var JadwalPosyandu $lockedJadwal */
            $lockedJadwal = JadwalPosyandu::where('id', $jadwal->id)->lockForUpdate()->firstOrFail();

            // 3. Proteksi kuota: Penurunan kuota tidak boleh kurang dari jumlah reservasi aktif
            if (isset($data['kuota_jadwal'])) {
                $activeReservations = $lockedJadwal->reservasi()
                    ->whereIn('status', ['menunggu', 'disetujui'])
                    ->count();

                if ((int) $data['kuota_jadwal'] < $activeReservations) {
                    throw ValidationException::withMessages([
                        'kuota_jadwal' => "Kuota tidak dapat diturunkan menjadi {$data['kuota_jadwal']} karena sudah terdapat {$activeReservations} reservasi aktif.",
                    ]);
                }
            }

            // 4. Deteksi bentrok jika waktu/nakes/tanggal berubah
            if ($newJamMulai && $newJamSelesai) {
                $conflict = JadwalPosyandu::overlap(
                    $newUserId,
                    $newDate,
                    $newJamMulai,
                    $newJamSelesai,
                    $lockedJadwal->id
                )->first();

                if ($conflict) {
                    $nakes = User::find($newUserId);
                    $nakesName = $nakes?->name ?? 'Nakes';
                    throw ValidationException::withMessages([
                        'jam_mulai' => "Nakes {$nakesName} telah memiliki jadwal lain ({$conflict->judul}) pada rentang {$conflict->hour} s.d. {$conflict->jam_selesai}.",
                    ]);
                }
            }

            // 5. Update data
            $payload = [];
            if (isset($data['user_id'])) $payload['user_id'] = $data['user_id'];
            if (isset($data['judul'])) $payload['judul'] = $data['judul'];
            if (isset($data['date'])) $payload['date'] = $data['date'];
            if (isset($data['jam_mulai'])) $payload['hour'] = $data['jam_mulai'];
            if (isset($data['jam_selesai'])) $payload['jam_selesai'] = $data['jam_selesai'];
            if (isset($data['location'])) $payload['location'] = $data['location'];
            if (isset($data['kuota_jadwal'])) $payload['kuota_jadwal'] = (int) $data['kuota_jadwal'];
            if (isset($data['status'])) $payload['status'] = $data['status'];

            $lockedJadwal->update($payload);

            return $lockedJadwal->fresh();
        });
    }

    /**
     * Batalkan jadwal (soft status) tanpa merusak riwayat presensi/reservasi.
     */
    public function cancelJadwal(JadwalPosyandu $jadwal): JadwalPosyandu
    {
        return DB::transaction(function () use ($jadwal) {
            $locked = JadwalPosyandu::where('id', $jadwal->id)->lockForUpdate()->firstOrFail();
            $locked->update(['status' => 'dibatalkan']);
            return $locked;
        });
    }

    /**
     * Hapus jadwal fisik jika belum ada reservasi/presensi yang terikat.
     *
     * @throws ValidationException
     */
    public function deleteJadwal(JadwalPosyandu $jadwal): bool
    {
        return DB::transaction(function () use ($jadwal) {
            $locked = JadwalPosyandu::where('id', $jadwal->id)->lockForUpdate()->firstOrFail();

            $reservasiCount = $locked->reservasi()->count();
            $presensiCount = $locked->presensi()->count();

            if ($reservasiCount > 0 || $presensiCount > 0) {
                throw ValidationException::withMessages([
                    'jadwal' => "Jadwal tidak dapat dihapus karena sudah memiliki {$reservasiCount} data reservasi dan {$presensiCount} presensi. Gunakan opsi 'Batalkan Jadwal'.",
                ]);
            }

            return (bool) $locked->delete();
        });
    }

    /**
     * Feed data kalender FullCalendar 6 dengan filter rentang dan normalisasi ISO8601.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getCalendarFeed(string $startDate, string $endDate, ?int $nakesId = null): Collection
    {
        $query = JadwalPosyandu::with('penanggungJawab')
            ->whereBetween('date', [$startDate, $endDate]);

        if ($nakesId !== null) {
            $query->where('user_id', $nakesId);
        }

        return $query->get()->map(function (JadwalPosyandu $item) {
            $dateStr = $item->date instanceof Carbon ? $item->date->format('Y-m-d') : (string) $item->date;
            $startIso = $dateStr . 'T' . $item->hour;
            $endIso = $item->jam_selesai ? ($dateStr . 'T' . $item->jam_selesai) : null;

            $isBatal = $item->status === 'dibatalkan';

            return [
                'id' => (string) $item->id,
                'title' => ($isBatal ? '[BATAL] ' : '') . $item->judul,
                'start' => $startIso,
                'end' => $endIso,
                'allDay' => false,
                'backgroundColor' => $isBatal ? '#9CA3AF' : '#2563EB',
                'borderColor' => $isBatal ? '#6B7280' : '#1D4ED8',
                'extendedProps' => [
                    'nakes_id' => $item->user_id,
                    'nakes_nama' => $item->penanggungJawab?->name ?? 'Nakes',
                    'location' => $item->location,
                    'kuota' => $item->kuota_jadwal,
                    'status' => $item->status,
                ],
            ];
        });
    }
}

