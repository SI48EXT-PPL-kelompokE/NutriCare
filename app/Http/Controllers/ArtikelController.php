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
            ->paginate(10)
            ->withQueryString();

        return view('edukasi.artikel.index', compact('artikels', 'kategoriList'));
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
