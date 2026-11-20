<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use App\Models\OrangTua;
use App\Models\Pengaduan;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PengaduanController extends Controller
{
    private const KATEGORI_LIST = [
        'Distribusi PMT',
        'Layanan Posyandu',
        'Data KMS',
        'Antrean Posyandu',
    ];

    public function index(Request $request)
    {
        $user = $request->user();
        $isResident = $user->role?->name_role === Role::RESIDENT;

        $pengaduans = Pengaduan::with(['orangTua', 'adminPenanggap'])
            ->visibleTo($user)
            ->search($request->query('q'))
            ->kategori($request->query('kategori'))
            ->status($request->query('status'))
            ->latest('id')
            ->paginate(5)
            ->withQueryString();

        $totalArtikel = Artikel::query()->when($isResident, fn ($q) => $q->published())->count();
        $totalPengaduan = Pengaduan::visibleTo($user)->count();
        $kategoriList = self::KATEGORI_LIST;

        return view('edukasi.pengaduan.index', compact(
            'pengaduans',
            'kategoriList',
            'totalArtikel',
            'totalPengaduan',
            'isResident'
        ));
    }

    public function create(Request $request)
    {
        $isResident = $request->user()->role?->name_role === Role::RESIDENT;
        $kategoriList = self::KATEGORI_LIST;
        $orangTuasList = $isResident
            ? collect()
            : OrangTua::select('id', 'name', 'nik', 'alamat')->orderBy('name')->get();

        return view('edukasi.pengaduan.create', compact('kategoriList', 'orangTuasList', 'isResident'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kategori' => 'required|string',
            'judul' => 'required|string|max:255',
            'isi' => 'required|string',
            'orang_tua_id' => 'nullable|exists:orang_tua,id',
            'is_anonymous' => 'nullable|boolean',
            'foto_bukti' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto_bukti')) {
            $fotoPath = $request->file('foto_bukti')->store('pengaduan', 'public');
        }

        $user = $request->user();
        $isResident = $user->role?->name_role === Role::RESIDENT;
        $isAnonymous = $request->boolean('is_anonymous');
        $orangTuaId = $isAnonymous
            ? null
            : ($isResident ? $user->orangTua?->id : ($validated['orang_tua_id'] ?? null));

        Pengaduan::create([
            'kategori' => $validated['kategori'],
            'judul' => $validated['judul'],
            'isi' => $validated['isi'],
            'orang_tua_id' => $orangTuaId,
            'is_anonymous' => $isAnonymous,
            'foto_bukti_path' => $fotoPath,
            'status' => 'baru',
        ]);

        return redirect()
            ->route('edukasi.pengaduan.index')
            ->with('success', 'Pengaduan warga berhasil dicatat.');
    }

    public function show(Request $request, $id)
    {
        $pengaduan = Pengaduan::with(['orangTua', 'adminPenanggap'])->findOrFail($id);
        $user = $request->user();
        $isResident = $user->role?->name_role === Role::RESIDENT;

        abort_if(
            $isResident && !$pengaduan->is_anonymous && $pengaduan->orang_tua_id !== $user->orangTua?->id,
            403
        );

        return view('edukasi.pengaduan.show', compact('pengaduan', 'isResident'));
    }

    public function tanggapi(Request $request, $id)
    {
        $pengaduan = Pengaduan::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:baru,diproses,selesai',
            'catatan' => 'required|string',
        ]);

        $adminId = auth()->id() ?? User::whereHas('role', function ($q) {
            $q->where('name_role', 'like', '%Admin%');
        })->value('id') ?? User::first()?->id;

        $pengaduan->update([
            'status' => $validated['status'],
            'catatan' => $validated['catatan'],
            'user_id' => $adminId,
        ]);

        return redirect()
            ->route('edukasi.pengaduan.index')
            ->with('success', "Tanggapan untuk pengaduan {$pengaduan->nomor_tiket} berhasil disimpan.");
    }

    public function export(Request $request)
    {
        $fileName = 'laporan_pengaduan_warga_' . date('Ymd_His') . '.csv';

        $pengaduans = Pengaduan::with(['orangTua', 'adminPenanggap'])
            ->search($request->query('q'))
            ->kategori($request->query('kategori'))
            ->status($request->query('status'))
            ->latest('id')
            ->get();

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$fileName}",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($pengaduans) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['No Tiket', 'Tanggal', 'Kategori', 'Pengirim', 'Judul', 'Ringkasan Isi', 'Status', 'Catatan Petugas']);

            foreach ($pengaduans as $p) {
                $pengirim = $p->is_anonymous ? 'Anonim' : ($p->orangTua->name ?? 'Warga Terdaftar');
                fputcsv($file, [
                    $p->nomor_tiket,
                    $p->created_at->format('d M Y H:i'),
                    $p->kategori,
                    $pengirim,
                    $p->judul,
                    $p->isi,
                    ucfirst($p->status),
                    $p->catatan ?? '-',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

