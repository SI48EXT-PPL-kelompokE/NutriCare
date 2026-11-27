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
        $data = $this->validateAndResolvePayload($request);
        $data['status'] = 'baru';

        Pengaduan::create($data);

        return redirect()
            ->route('edukasi.pengaduan.index')
            ->with('success', 'Pengaduan warga berhasil dicatat.');
    }

    public function show(Request $request, $id)
    {
        $pengaduan = Pengaduan::with(['orangTua', 'adminPenanggap'])->findOrFail($id);
        $this->authorizeResidentAccess($request, $pengaduan);
        $isResident = $request->user()->role?->name_role === Role::RESIDENT;

        return view('edukasi.pengaduan.show', compact('pengaduan', 'isResident'));
    }

    public function edit(Request $request, $id)
    {
        $pengaduan = Pengaduan::findOrFail($id);
        $this->authorizeResidentAccess($request, $pengaduan);
        abort_if($pengaduan->status !== 'baru', 403, 'Pengaduan yang sudah ditindaklanjuti tidak dapat diubah.');

        $isResident = $request->user()->role?->name_role === Role::RESIDENT;
        $kategoriList = self::KATEGORI_LIST;
        $orangTuasList = $isResident
            ? collect()
            : OrangTua::select('id', 'name', 'nik', 'alamat')->orderBy('name')->get();

        return view('edukasi.pengaduan.edit', compact('pengaduan', 'kategoriList', 'orangTuasList', 'isResident'));
    }

    public function update(Request $request, $id)
    {
        $pengaduan = Pengaduan::findOrFail($id);
        $this->authorizeResidentAccess($request, $pengaduan);
        abort_if($pengaduan->status !== 'baru', 403, 'Pengaduan yang sudah ditindaklanjuti tidak dapat diubah.');

        $data = $this->validateAndResolvePayload($request, $pengaduan);
        $pengaduan->update($data);

        return redirect()
            ->route('edukasi.pengaduan.index')
            ->with('success', "Pengaduan {$pengaduan->nomor_tiket} berhasil diperbarui.");
    }

    public function destroy($id)
    {
        $pengaduan = Pengaduan::findOrFail($id);
        $tiket = $pengaduan->nomor_tiket;

        if ($pengaduan->foto_bukti_path) {
            Storage::disk('public')->delete($pengaduan->foto_bukti_path);
        }

        $pengaduan->delete();

        return redirect()
            ->route('edukasi.pengaduan.index')
            ->with('success', "Pengaduan {$tiket} berhasil dihapus.");
    }

    /**
     * Memvalidasi input form aduan serta menangani lampiran foto dan anonimitas.
     */
    private function validateAndResolvePayload(Request $request, ?Pengaduan $existing = null): array
    {
        $validated = $request->validate([
            'kategori' => 'required|string',
            'judul' => 'required|string|max:255',
            'isi' => 'required|string',
            'orang_tua_id' => 'nullable|exists:orang_tua,id',
            'is_anonymous' => 'nullable|boolean',
            'foto_bukti' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        $fotoPath = $existing?->foto_bukti_path;
        if ($request->hasFile('foto_bukti')) {
            if ($fotoPath) {
                Storage::disk('public')->delete($fotoPath);
            }
            $fotoPath = $request->file('foto_bukti')->store('pengaduan', 'public');
        }

        $user = $request->user();
        $isResident = $user->role?->name_role === Role::RESIDENT;
        $isAnonymous = $request->boolean('is_anonymous');
        $orangTuaId = $isAnonymous
            ? null
            : ($isResident ? $user->orangTua?->id : ($validated['orang_tua_id'] ?? null));

        return [
            'kategori' => $validated['kategori'],
            'judul' => $validated['judul'],
            'isi' => $validated['isi'],
            'orang_tua_id' => $orangTuaId,
            'is_anonymous' => $isAnonymous,
            'foto_bukti_path' => $fotoPath,
        ];
    }

    /**
     * Mencegah warga mengakses atau mengubah pengaduan non-anonim milik warga lain.
     */
    private function authorizeResidentAccess(Request $request, Pengaduan $pengaduan): void
    {
        $user = $request->user();
        $isResident = $user->role?->name_role === Role::RESIDENT;

        abort_if(
            $isResident && !$pengaduan->is_anonymous && $pengaduan->orang_tua_id !== $user->orangTua?->id,
            403
        );
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

