<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BalitaResource extends JsonResource
{
    /**
     * Menghitung usia balita dalam bulan penuh yang sudah berlalu dari tanggal lahir.
     *
     * Akurat terhadap pergantian bulan, tahun kabisat, dan tanggal akhir bulan,
     * serta konsisten menggunakan timezone aplikasi.
     */
    public static function calculateUsiaBulan(mixed $birthDate, ?CarbonInterface $referenceDate = null): ?int
    {
        if ($birthDate === null || $birthDate === '') {
            return null;
        }

        $timezone = config('app.timezone', 'UTC');

        $dateString = $birthDate instanceof DateTimeInterface
            ? $birthDate->format('Y-m-d')
            : substr(trim((string) $birthDate), 0, 10);

        $birth = Carbon::createFromFormat('Y-m-d', $dateString, $timezone)->startOfDay();

        $today = $referenceDate
            ? Carbon::parse($referenceDate->format('Y-m-d'), $timezone)->startOfDay()
            : Carbon::now($timezone)->startOfDay();

        if ($birth->gt($today)) {
            return 0;
        }

        $months = (($today->year - $birth->year) * 12) + ($today->month - $birth->month);
        $effectiveAnniversaryDay = min($birth->day, $today->daysInMonth);

        if ($today->day < $effectiveAnniversaryDay) {
            $months--;
        }

        return max(0, $months);
    }

    /**
     * Memformat usia balita ke dalam tampilan standar "X Bulan".
     */
    public static function formatUsia(mixed $birthDate, ?CarbonInterface $referenceDate = null): string
    {
        $months = self::calculateUsiaBulan($birthDate, $referenceDate);

        if ($months === null) {
            return '-';
        }

        return "{$months} Bulan";
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'orang_tua_id' => $this->orang_tua_id,
            'name' => $this->name,
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'usia_bulan' => self::calculateUsiaBulan($this->birth_date),
            'usia_label' => self::formatUsia($this->birth_date),
            'jenis_kelamin' => $this->jenis_kelamin,
            'qr_code' => $this->qr_code,
            'orang_tua' => $this->whenLoaded('orangTua', function () {
                if (!$this->orangTua) {
                    return null;
                }

                return [
                    'id' => $this->orangTua->id,
                    'nik' => $this->orangTua->nik,
                    'name' => $this->orangTua->name,
                    'alamat' => $this->orangTua->alamat,
                    'kontak' => $this->orangTua->kontak,
                    'pregnancy_status' => $this->orangTua->pregnancy_status,
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

