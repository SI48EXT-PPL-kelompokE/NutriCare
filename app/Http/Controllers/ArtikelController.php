<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArtikelController extends Controller
{
    public function index(Request $request)
    {
        $kategoriList = [
            'Pencegahan Stunting',
            'MPASI Sehat',
            'Gizi Ibu Hamil',
            'Pola Asuh',
            'Layanan Kesehatan',
        ];

        $artikels = Artikel::with('penulis')
            ->search($request->query('q'))
            ->kategori($request->query('kategori'))
            ->when($request->query('status'), function ($query, $status) {
                if ($status === 'published') {
                    $query->published();
                } elseif ($status === 'draft') {
                    $query->draft();
                }
            })
            ->latest('id')
            ->paginate(7)
            ->withQueryString();

        $totalArtikel = Artikel::count();
        $totalPengaduan = class_exists(\App\Models\Pengaduan::class) ? \App\Models\Pengaduan::count() : 14;

        return view('edukasi.artikel.index', compact('artikels', 'kategoriList', 'totalArtikel', 'totalPengaduan'));
    }

    public function create()
    {
        $kategoriList = [
            'Pencegahan Stunting',
            'MPASI Sehat',
            'Gizi Ibu Hamil',
            'Pola Asuh',
            'Layanan Kesehatan',
        ];

        return view('edukasi.artikel.create', compact('kategoriList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'kategori' => 'required|string|max:100',
            'status' => 'required|in:draft,published',
            'konten' => 'required|string',
        ]);

        $sanitizedContent = $this->sanitizeHtml($validated['konten']);

        $fallbackUser = User::first();
        $authorId = auth()->id() ?? ($fallbackUser ? $fallbackUser->id : 1);

        Artikel::create([
            'user_id' => $authorId,
            'judul' => $validated['judul'],
            'kategori' => $validated['kategori'],
            'status' => $validated['status'],
            'url_slug' => Artikel::generateUniqueSlug($validated['judul']),
            'konten' => $sanitizedContent,
            'published_at' => $validated['status'] === 'published' ? now() : null,
        ]);

        return redirect()
            ->route('edukasi.artikel.index')
            ->with('success', 'Artikel edukasi berhasil dibuat.');
    }

    /**
     * Menampilkan artikel untuk pengunjung publik berdasarkan slug (hanya yang berstatus published).
     */
    public function baca($slug)
    {
        $artikel = Artikel::with('penulis')
            ->published()
            ->where('url_slug', $slug)
            ->firstOrFail();

        return view('edukasi.artikel.show', compact('artikel'));
    }

    public function show($id)
    {
        $artikel = Artikel::with('penulis')->findOrFail($id);

        return view('edukasi.artikel.show', compact('artikel'));
    }

    public function edit($id)
    {
        $artikel = Artikel::findOrFail($id);

        $kategoriList = [
            'Pencegahan Stunting',
            'MPASI Sehat',
            'Gizi Ibu Hamil',
            'Pola Asuh',
            'Layanan Kesehatan',
        ];

        return view('edukasi.artikel.edit', compact('artikel', 'kategoriList'));
    }

    public function update(Request $request, $id)
    {
        $artikel = Artikel::findOrFail($id);

        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'kategori' => 'required|string|max:100',
            'status' => 'required|in:draft,published',
            'konten' => 'required|string',
        ]);

        $sanitizedContent = $this->sanitizeHtml($validated['konten']);

        $artikel->update([
            'judul' => $validated['judul'],
            'kategori' => $validated['kategori'],
            'status' => $validated['status'],
            'url_slug' => Artikel::generateUniqueSlug($validated['judul'], $artikel->id),
            'konten' => $sanitizedContent,
            'published_at' => $validated['status'] === 'published' ? ($artikel->published_at ?? now()) : null,
        ]);

        return redirect()
            ->route('edukasi.artikel.index')
            ->with('success', 'Artikel edukasi berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $artikel = Artikel::findOrFail($id);
        $artikel->delete();

        return redirect()
            ->route('edukasi.artikel.index')
            ->with('success', 'Artikel edukasi berhasil dihapus.');
    }

    public function export(Request $request)
    {
        $fileName = 'laporan_artikel_edukasi_' . date('Ymd_His') . '.csv';

        $artikels = Artikel::with('penulis')
            ->search($request->query('q'))
            ->kategori($request->query('kategori'))
            ->when($request->query('status'), function ($query, $status) {
                if ($status === 'published') {
                    $query->published();
                } elseif ($status === 'draft') {
                    $query->draft();
                }
            })
            ->latest('id')
            ->get();

        $headers = [
            'Content-type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$fileName}",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($artikels) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['ID', 'Judul Artikel', 'Kategori', 'Status', 'Penulis', 'Tanggal Publikasi', 'Dibuat Pada']);

            foreach ($artikels as $artikel) {
                fputcsv($file, [
                    $artikel->id,
                    $artikel->judul,
                    $artikel->kategori,
                    ucfirst($artikel->status),
                    $artikel->penulis->name ?? 'Admin NutriCare',
                    $artikel->published_at ? $artikel->published_at->format('d M Y H:i') : '-',
                    $artikel->created_at->format('d M Y H:i'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function sanitizeHtml(string $html): string
    {
        if (class_exists(\HTMLPurifier::class)) {
            $config = \HTMLPurifier_Config::createDefault();
            $config->set('HTML.Allowed', 'p,b,strong,i,em,u,h1,h2,h3,h4,h5,h6,ul,ol,li,blockquote,pre,a[href|title],img[src|alt|width|height],table,thead,tbody,tr,th,td,span');
            $config->set('HTML.SafeIframe', false);
            $purifier = new \HTMLPurifier($config);

            return $purifier->purify($html);
        }

        return strip_tags($html, '<p><b><strong><i><em><u><h1><h2><h3><h4><h5><h6><ul><ol><li><blockquote><pre><a><img><table><thead><tbody><tr><th><td><span>');
    }
}
