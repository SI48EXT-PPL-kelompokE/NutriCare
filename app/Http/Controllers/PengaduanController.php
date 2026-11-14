<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use App\Models\OrangTua;
use App\Models\Pengaduan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PengaduanController extends Controller
{
    public function index(Request $request)
    {
        $kategoriList = [
            'Distribusi PMT',
            'Layanan Posyandu',
            'Data KMS',
            'Antrean Posyandu',
        ];

        $pengaduans = Pengaduan::with(['orangTua', 'adminPenanggap'])
            ->search($request->query('q'))
            ->kategori($request->query('kategori'))
            ->status($request->query('status'))
            ->latest('id')
            ->paginate(5)
            ->withQueryString();

        $totalArtikel = Artikel::count();
        $totalPengaduan = Pengaduan::count();

        return view('edukasi.pengaduan.index', compact('pengaduans', 'kategoriList', 'totalArtikel', 'totalPengaduan'));
    }

    public function create()
    {
        $kategoriList = [
            'Distribusi PMT',
            'Layanan Posyandu',
            'Data KMS',
            'Antrean Posyandu',
        ];

        $orangTuasList = OrangTua::select('id', 'name', 'nik', 'alamat')->orderBy('name')->get();

        return view('edukasi.pengaduan.create', compact('kategoriList', 'orangTuasList'));
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

        $isAnonymous = $request->boolean('is_anonymous');

        Pengaduan::create([
            'kategori' => $validated['kategori'],
            'judul' => $validated['judul'],
            'isi' => $validated['isi'],
            'orang_tua_id' => $isAnonymous ? null : ($validated['orang_tua_id'] ?? null),
            'is_anonymous' => $isAnonymous,
            'foto_bukti_path' => $fotoPath,
            'status' => 'baru',
        ]);

        return redirect()
            ->route('edukasi.pengaduan.index')
            ->with('success', 'Pengaduan warga berhasil dicatat.');
    }

    public function show($id)
    {
        $pengaduan = Pengaduan::with(['orangTua', 'adminPenanggap'])->findOrFail($id);

        return view('edukasi.pengaduan.show', compact('pengaduan'));
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

