<?php

namespace Tests\Browser;

use App\Models\Artikel;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ArtikelManagementTest extends DuskTestCase
{
    /**
     * SCRUM-170 & SCRUM-173: Menguji navigasi ke halaman index artikel dan fungsi filter antarmuka.
     */
    public function test_user_can_view_article_index_and_use_search_filter(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/edukasi/artikel')
                ->assertSee('Artikel & Panduan Edukasi')
                ->assertPresent('#searchInput')
                ->assertPresent('#filterKategori')
                ->assertPresent('#filterStatus')
                ->type('#searchInput', 'Stunting')
                ->press('#btnFilter')
                ->assertQueryStringHas('q', 'Stunting');
        });
    }

    /**
     * SCRUM-170 [KF-45]: Menguji interaksi peramban pada form tulis artikel dengan Quill Editor.
     */
    public function test_user_can_open_create_form_and_interact_with_quill_editor(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/edukasi/artikel/create')
                ->assertSee('Formulir Artikel Baru')
                ->assertPresent('#judul')
                ->assertPresent('#kategori')
                ->assertPresent('#status')
                ->assertPresent('#editorContainer')
                ->type('#judul', 'Panduan Pemberian ASI Eksklusif 6 Bulan')
                ->select('#kategori', 'Pola Asuh')
                ->select('#status', 'published')
                ->assertPresent('#kontenInput');
        });
    }

    /**
     * SCRUM-173 [KF-46]: Menguji peramban saat pengunjung membaca artikel terbit melalui URL slug.
     */
    public function test_visitor_can_read_published_article_via_url_slug(): void
    {
        $user = User::first() ?? User::factory()->create();

        $artikel = Artikel::create([
            'user_id' => $user->id,
            'judul' => 'Nutrisi Tepat Mencegah Stunting Balita',
            'kategori' => 'Pencegahan Stunting',
            'status' => 'published',
            'url_slug' => 'nutrisi-tepat-mencegah-stunting-balita-dusk',
            'konten' => '<p>Simulasi Dusk peramban untuk membaca materi terbit.</p>',
            'published_at' => now(),
        ]);

        $this->browse(function (Browser $browser) use ($artikel) {
            $browser->visit('/edukasi/artikel/baca/' . $artikel->url_slug)
                ->assertSee('Nutrisi Tepat Mencegah Stunting Balita')
                ->assertSee('Pencegahan Stunting')
                ->assertSee('Simulasi Dusk peramban untuk membaca materi terbit.');
        });
    }
}

