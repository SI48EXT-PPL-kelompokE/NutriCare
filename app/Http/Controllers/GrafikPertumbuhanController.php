<?php

namespace App\Http\Controllers;

use App\Models\Balita;
use App\Services\GrowthChartService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class GrafikPertumbuhanController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly GrowthChartService $service)
    {
    }

    /**
     * Halaman grafik pertumbuhan satu balita.
     */
    public function show(Balita $balita)
    {
        $this->authorize('lihatPertumbuhan', $balita);

        $balita->loadMissing('orangTua');

        $grafik = $this->service->bangun($balita);

        return view('pertumbuhan.show', [
            'balita' => $balita,
            'grafik' => $grafik,
        ]);
    }

    /**
     * Daftar anak milik akun warga yang sedang login.
     */
    public function anakSaya(Request $request)
    {
        $orangTua = $request->user()->orangTua;

        $anak = $orangTua
            ? $orangTua->balita()->with('pengukuranKms')->orderBy('name')->get()
            : collect();

        // Jika hanya satu anak, langsung ke halaman grafiknya.
        if ($anak->count() === 1) {
            return redirect()->route('balita.pertumbuhan', $anak->first());
        }

        $kartu = $anak->map(function (Balita $item) {
            $terakhir = $item->pengukuranKms
                ->sortByDesc(fn ($p) => $p->date->toDateString() . '|' . str_pad((string) $p->id, 10, '0', STR_PAD_LEFT))
                ->first();

            $usiaBulan = $item->birth_date
                ? (int) floor(GrowthChartService::usiaDesimal($item->birth_date, Carbon::now()))
                : 0;

            return [
                'balita' => $item,
                'usia_label' => GrowthChartService::labelUsia($usiaBulan),
                'status' => $terakhir?->status_gizi,
                'lengkap' => $terakhir ? ($terakhir->hasil_status['lengkap'] ?? false) : false,
            ];
        });

        return view('pertumbuhan.anak-saya', ['kartu' => $kartu]);
    }
}
