<?php

namespace Tests\Feature;

use App\Models\Artikel;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ArtikelCrudTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Memastikan halaman daftar artikel edukasi memuat kode 200 dan teks antarmuka.
     */
    public function test_index_page_loads_successfully_and_displays_articles(): void
    {
        $response = $this->get(route('edukasi.artikel.index'));

        $response->assertStatus(200);
        $response->assertSee('Tulis Artikel Baru');
        $response->assertSee('Kelola materi edukasi pencegahan stunting');
    }

    /**
     * Memastikan form tulis artikel baru memuat aset Quill Rich Text Editor.
     */
    public function test_create_page_loads_with_quill_editor_assets(): void
    {
        $response = $this->get(route('edukasi.artikel.create'));

        $response->assertStatus(200);
        $response->assertSee('Formulir Artikel Baru');
        $response->assertSee('quill.snow.css');
        $response->assertSee('quill.js');
        $response->assertSee('editorContainer');
    }

    /**
     * Memastikan validasi form menolak input kosong untuk judul, kategori, status, dan konten.
     */
    public function test_store_validates_required_fields(): void
    {
        $response = $this->post(route('edukasi.artikel.store'), []);

        $response->assertSessionHasErrors(['judul', 'kategori', 'status', 'konten']);
    }

    /**
     * Memastikan artikel baru berhasil disimpan dan tag berbahaya XSS dibersihkan (KNF-08).
     */
    public function test_store_creates_new_article_and_sanitizes_xss_script(): void
    {
        $dirtyContent = '<p>Panduan gizi penting.</p><script>alert("Serangan XSS");</script><img src="x" onerror="alert(1)">';

        $postData = [
            'judul' => 'Pola Makan Seimbang Balita 1-2 Tahun',
            'kategori' => 'MPASI Sehat',
            'status' => 'published',
            'konten' => $dirtyContent,
        ];

        $response = $this->post(route('edukasi.artikel.store'), $postData);

        $response->assertRedirect(route('edukasi.artikel.index'));
        $response->assertSessionHas('success');

        $artikel = Artikel::where('judul', 'Pola Makan Seimbang Balita 1-2 Tahun')->first();
        $this->assertNotNull($artikel);
        $this->assertEquals('MPASI Sehat', $artikel->kategori);
        $this->assertEquals('published', $artikel->status);
        $this->assertNotNull($artikel->published_at);

        // KNF-08: Tag <script> dan atribut jahat onerror harus disanitasi
        $this->assertStringNotContainsString('<script>', $artikel->konten);
        $this->assertStringNotContainsString('onerror=', $artikel->konten);
        $this->assertStringContainsString('Panduan gizi penting.', $artikel->konten);
    }

    /**
     * Memastikan halaman detail artikel menampilkan isi konten dan metadata.
     */
    public function test_show_page_displays_article_content_and_metadata(): void
    {
        $user = User::first() ?? User::factory()->create();

        $artikel = Artikel::create([
            'user_id' => $user->id,
            'judul' => 'Nutrisi Penting Masa Pertumbuhan',
            'kategori' => 'Pencegahan Stunting',
            'status' => 'published',
            'url_slug' => 'nutrisi-penting-masa-pertumbuhan-test',
            'konten' => '<p>Kandungan protein hewani sangat krusial bagi balita.</p>',
            'published_at' => now(),
        ]);

        $response = $this->get(route('edukasi.artikel.show', $artikel->id));

        $response->assertStatus(200);
        $response->assertSee('Nutrisi Penting Masa Pertumbuhan');
        $response->assertSee('Pencegahan Stunting');
        $response->assertSee('Kandungan protein hewani sangat krusial');
    }

    /**
     * Memastikan form edit artikel memuat data lama untuk diubah.
     */
    public function test_edit_page_loads_existing_article_data(): void
    {
        $artikel = Artikel::first();
        $this->assertNotNull($artikel, 'Data artikel awal harus tersedia untuk diuji.');

        $response = $this->get(route('edukasi.artikel.edit', $artikel->id));

        $response->assertStatus(200);
        $response->assertSee('Edit Artikel Edukasi');
        $response->assertSee(htmlspecialchars($artikel->judul, ENT_QUOTES));
    }

    /**
     * Memastikan pembaruan artikel berhasil dan konten baru tetap disanitasi.
     */
    public function test_update_modifies_article_content_and_sanitizes_xss(): void
    {
        $artikel = Artikel::first();
        $this->assertNotNull($artikel);

        $updateData = [
            'judul' => 'Judul Artikel Telah Diperbarui',
            'kategori' => 'Gizi Balita',
            'status' => 'draft',
            'konten' => '<p>Konten hasil perbaikan materi.</p><script>alert("hacked");</script>',
        ];

        $response = $this->put(route('edukasi.artikel.update', $artikel->id), $updateData);

        $response->assertRedirect(route('edukasi.artikel.index'));
        $response->assertSessionHas('success');

        $artikel->refresh();
        $this->assertEquals('Judul Artikel Telah Diperbarui', $artikel->judul);
        $this->assertEquals('draft', $artikel->status);
        $this->assertStringNotContainsString('<script>', $artikel->konten);
        $this->assertStringContainsString('Konten hasil perbaikan materi.', $artikel->konten);
    }

    /**
     * Memastikan operasi penghapusan artikel berhasil menghapus rekaman dari database.
     */
    public function test_destroy_deletes_article_successfully(): void
    {
        $user = User::first() ?? User::factory()->create();

        $artikel = Artikel::create([
            'user_id' => $user->id,
            'judul' => 'Artikel Uji Hapus',
            'kategori' => 'Pencegahan Stunting',
            'status' => 'draft',
            'url_slug' => 'artikel-uji-hapus-temp',
            'konten' => '<p>Artikel sementara untuk dihapus.</p>',
            'published_at' => null,
        ]);

        $artikelId = $artikel->id;

        $response = $this->delete(route('edukasi.artikel.destroy', $artikelId));

        $response->assertRedirect(route('edukasi.artikel.index'));
        $response->assertSessionHas('success');

        $this->assertNull(Artikel::find($artikelId));
    }

    /**
     * SCRUM-171 [KF-46]: Memastikan slug otomatis unik ter-generate meskipun terdapat judul yang sama.
     */
    public function test_slug_is_automatically_generated_and_unique(): void
    {
        $user = User::first() ?? User::factory()->create();

        $artikel1 = Artikel::create([
            'user_id' => $user->id,
            'judul' => 'Gizi Seimbang Keluarga Posyandu',
            'kategori' => 'Gizi Balita',
            'status' => 'published',
            'konten' => '<p>Materi panduan gizi.</p>',
        ]);

        $artikel2 = Artikel::create([
            'user_id' => $user->id,
            'judul' => 'Gizi Seimbang Keluarga Posyandu',
            'kategori' => 'Gizi Balita',
            'status' => 'draft',
            'konten' => '<p>Materi panduan gizi duplikat judul.</p>',
        ]);

        $this->assertEquals('gizi-seimbang-keluarga-posyandu', $artikel1->url_slug);
        $this->assertEquals('gizi-seimbang-keluarga-posyandu-1', $artikel2->url_slug);
        $this->assertNotEquals($artikel1->url_slug, $artikel2->url_slug);
    }

    /**
     * SCRUM-171 [KF-46]: Memastikan filter kategori, status, dan pencarian kata kunci berfungsi.
     */
    public function test_filter_kategori_status_and_keyword_search(): void
    {
        $user = User::first() ?? User::factory()->create();

        Artikel::create([
            'user_id' => $user->id,
            'judul' => 'Resep MPASI Ayam Brokoli Lezat',
            'kategori' => 'MPASI Sehat',
            'status' => 'published',
            'konten' => '<p>Menu bergizi tinggi protein.</p>',
        ]);

        $responseKategori = $this->get(route('edukasi.artikel.index', ['kategori' => 'MPASI Sehat']));
        $responseKategori->assertStatus(200);
        $responseKategori->assertSee('Resep MPASI Ayam Brokoli Lezat');

        $responseSearch = $this->get(route('edukasi.artikel.index', ['q' => 'Brokoli']));
        $responseSearch->assertStatus(200);
        $responseSearch->assertSee('Resep MPASI Ayam Brokoli Lezat');

        $responseStatus = $this->get(route('edukasi.artikel.index', ['status' => 'published']));
        $responseStatus->assertStatus(200);
        $responseStatus->assertSee('Resep MPASI Ayam Brokoli Lezat');
    }

    /**
     * SCRUM-171 [KF-46]: Memastikan pengunjung dapat membaca artikel terbit melalui url slug.
     */
    public function test_pengunjung_dapat_membaca_artikel_terbit_melalui_slug(): void
    {
        $user = User::first() ?? User::factory()->create();

        $artikel = Artikel::create([
            'user_id' => $user->id,
            'judul' => 'Panduan Gizi Publik Terbit',
            'kategori' => 'Gizi Balita',
            'status' => 'published',
            'url_slug' => 'panduan-gizi-publik-terbit',
            'konten' => '<p>Konten artikel yang boleh dibaca masyarakat umum.</p>',
            'published_at' => now(),
        ]);

        $response = $this->get(route('edukasi.artikel.baca', $artikel->url_slug));

        $response->assertStatus(200);
        $response->assertSee('Panduan Gizi Publik Terbit');
        $response->assertSee('Konten artikel yang boleh dibaca masyarakat umum.');
    }

    /**
     * SCRUM-171 [KF-46]: Memastikan hanya artikel terbit yang tampil ke pengunjung (draft mengembalikan 404).
     */
    public function test_hanya_artikel_terbit_yang_tampil_ke_pengunjung_draft_mengembalikan_404(): void
    {
        $user = User::first() ?? User::factory()->create();

        $artikelDraft = Artikel::create([
            'user_id' => $user->id,
            'judul' => 'Draf Rahasia Posyandu Belum Terbit',
            'kategori' => 'Pola Asuh',
            'status' => 'draft',
            'url_slug' => 'draf-rahasia-posyandu-belum-terbit',
            'konten' => '<p>Materi masih dalam penyusunan kader internal.</p>',
            'published_at' => null,
        ]);

        $response = $this->get(route('edukasi.artikel.baca', $artikelDraft->url_slug));

        $response->assertStatus(404);
    }
}

