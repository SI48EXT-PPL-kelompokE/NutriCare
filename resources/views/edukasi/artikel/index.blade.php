@extends('layouts.admin')

@section('title', 'Artikel & Panduan Edukasi - Edukasi & Layanan Warga')

@section('content')
<div class="space-y-6 w-full">

    <!-- Header & navigasi (Gambar 14 Mockup Proposal) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <span>Edukasi & Layanan Warga</span>
                <span>/</span>
                <span class="text-slate-600 font-semibold">Artikel & Panduan Edukasi</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Edukasi & Layanan Warga</h1>
            <p class="text-xs text-slate-500 mt-1">
                Kelola artikel edukasi gizi dan tindak lanjut pengaduan warga. Kelola materi edukasi pencegahan stunting dan pola gizi balita untuk masyarakat.
            </p>
        </div>

        <div class="flex items-center gap-3">
            @can('access-module', ['artikel', 'create'])
            <a href="{{ route('edukasi.artikel.create') }}" 
               id="btnTulisArtikel"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#005953] hover:bg-[#004742] text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>+ Tulis Artikel Baru</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Subnavigation Tabs (Gambar 14 Mockup Proposal) -->
    <div class="flex items-center gap-8 border-b border-slate-200 text-xs font-semibold pb-px">
        <!-- Tab 1: Artikel Edukasi (Aktif) -->
        <a href="{{ route('edukasi.artikel.index') }}" 
           class="inline-flex items-center gap-2.5 pb-3 text-[#005953] font-bold border-b-2 border-[#005953] transition-colors">
            <span>Artikel Edukasi</span>
            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-teal-50 text-teal-700 border border-teal-200">
                {{ $totalArtikel ?? $artikels->total() }}
            </span>
        </a>

        @unless($isResident)
        <!-- Tab 2: Pengaduan Warga (Sprint 3) -->
        <a href="{{ Route::has('pengaduan.index') ? route('pengaduan.index') : '#' }}" 
           class="inline-flex items-center gap-2.5 pb-3 text-slate-400 font-semibold hover:text-slate-700 transition-colors">
            <span>Pengaduan Warga</span>
            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600">
                {{ $totalPengaduan ?? 14 }}
            </span>
        </a>
        @endunless
    </div>

    @if (session('success'))
        <div class="p-4 rounded-xl bg-teal-50 border border-teal-200 text-teal-800 text-xs font-semibold flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 text-teal-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- Bilah Filter & Pencarian (Gambar 14 Mockup Proposal) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
        <x-search-form results="search-results" :action="route('edukasi.artikel.index')" id="filterForm" class="flex flex-wrap items-center gap-3 flex-1">
            <x-search-input name="q" id="searchInput" :value="request('q')" placeholder="Cari judul artikel..." wrapper-class="w-full min-w-0 sm:min-w-56 sm:flex-1" />

            <!-- Dropdown Semua Kategori -->
            <div class="w-full sm:w-48">
                <select 
                    name="kategori" 
                    id="filterKategori"
                    data-search-filter
                    class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#008f7a] transition-all shadow-2xs"
                >
                    <option value="">Semua Kategori</option>
                    @foreach ($kategoriList as $kat)
                        <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                    @endforeach
                </select>
            </div>

            @unless($isResident)
            <!-- Dropdown Semua Status -->
            <div class="w-full sm:w-40">
                <select 
                    name="status" 
                    id="filterStatus"
                    data-search-filter
                    class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#008f7a] transition-all shadow-2xs"
                >
                    <option value="">Semua Status</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                </select>
            </div>
            @endunless

            <!-- Tombol Filter & Reset -->
            <button type="submit" id="btnFilter" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 bg-[#005953] hover:bg-[#004742] text-white text-xs font-semibold rounded-xl transition-colors shadow-2xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <span>Filter</span>
            </button>
            <a href="{{ route('edukasi.artikel.index') }}" id="reset-article-search" data-search-sync
                @if (!request()->anyFilled(['q', 'kategori', 'status'])) hidden @endif
                class="px-2.5 py-2 text-xs text-slate-400 hover:text-slate-600 transition-colors">
                Reset
            </a>
        </x-search-form>

        <!-- Tombol Ekspor Laporan (Mockup Proposal) -->
        @can('access-module', ['laporan', 'create'])
        <a href="{{ route('edukasi.artikel.export', request()->query()) }}" id="btnEkspor" data-search-sync class="inline-flex items-center gap-2 px-4 py-2.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl shadow-2xs transition-colors shrink-0">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            <span>Ekspor Laporan</span>
        </a>
        @endcan
    </div>

    <!-- Tabel Data Artikel Edukasi (5 Kolom Sesuai Mockup Gambar 14) -->
    <x-table-card id="search-results" aria-live="polite">
        <x-data-table>
            <x-slot:head>
                <tr>
                    <th scope="col" class="px-6 py-4 w-5/12">JUDUL ARTIKEL</th>
                    <th scope="col" class="px-4 py-4 w-2/12">KATEGORI</th>
                    <th scope="col" class="px-4 py-4 w-2/12">STATUS</th>
                    <th scope="col" class="px-4 py-4 w-2/12">TANGGAL</th>
                    <th scope="col" class="px-6 py-4 w-1/12 text-right">AKSI</th>
                </tr>
            </x-slot:head>

            @forelse ($artikels as $artikel)
                <tr class="hover:bg-slate-50/70 transition-colors">
                    <!-- Judul & Cuplikan Teks -->
                    <td class="px-6 py-4">
                        <a href="{{ route('edukasi.artikel.show', $artikel->id) }}"
                           class="font-bold text-slate-900 text-xs hover:text-[#005953] transition-colors block line-clamp-1">
                            {{ $artikel->judul }}
                        </a>
                        <p class="text-[11px] text-slate-400 line-clamp-1 mt-0.5">
                            {{ strip_tags($artikel->konten) }}
                        </p>
                    </td>

                    <!-- Kategori -->
                    <td class="px-4 py-4 whitespace-nowrap text-slate-700 font-medium">
                        {{ $artikel->kategori }}
                    </td>

                    <!-- Status (Dot + Label) -->
                    <td class="px-4 py-4 whitespace-nowrap">
                        @if ($artikel->status === 'published')
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-teal-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-teal-600"></span>
                                <span>Published</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                <span>Draft</span>
                            </span>
                        @endif
                    </td>

                    <!-- Tanggal Publikasi -->
                    <td class="px-4 py-4 whitespace-nowrap text-slate-600">
                        {{ $artikel->published_at ? $artikel->published_at->format('d M Y') : $artikel->created_at->format('d M Y') }}
                    </td>

                    <!-- Kolom Aksi (Edit & Hapus) -->
                    <td class="px-6 py-4 whitespace-nowrap text-right text-xs">
                        <div class="inline-flex items-center justify-end gap-3">
                            @can('access-module', ['artikel', 'edit'])
                            <a href="{{ route('edukasi.artikel.edit', $artikel->id) }}"
                               class="text-teal-700 hover:text-teal-900 font-bold transition-colors">
                                Edit
                            </a>
                            @endcan

                            @can('access-module', ['artikel', 'delete'])
                            <form action="{{ route('edukasi.artikel.destroy', $artikel->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus artikel ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="text-slate-500 hover:text-rose-600 font-medium transition-colors">
                                    Hapus
                                </button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="w-10 h-10 text-slate-300 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                            </svg>
                            <p class="text-sm font-semibold text-slate-600">Belum ada artikel edukasi</p>
                            <p class="text-xs text-slate-400 mt-0.5">Mulai publikasikan informasi panduan kesehatan balita melalui tombol di atas.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </x-data-table>

        <x-pagination :paginator="$artikels" label="artikel" />
    </x-table-card>

</div>
@endsection
