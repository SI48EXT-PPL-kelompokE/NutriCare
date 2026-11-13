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
            <a href="{{ route('edukasi.artikel.create') }}" 
               id="btnTulisArtikel"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#005953] hover:bg-[#004742] text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>+ Tulis Artikel Baru</span>
            </a>
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

        <!-- Tab 2: Pengaduan Warga (Sprint 3) -->
        <a href="{{ Route::has('pengaduan.index') ? route('pengaduan.index') : '#' }}" 
           class="inline-flex items-center gap-2.5 pb-3 text-slate-400 font-semibold hover:text-slate-700 transition-colors">
            <span>Pengaduan Warga</span>
            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600">
                {{ $totalPengaduan ?? 14 }}
            </span>
        </a>
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
        <form action="{{ route('edukasi.artikel.index') }}" method="GET" id="filterForm" class="flex flex-wrap items-center gap-3 flex-1">
            <!-- Kolom Pencarian -->
            <div class="relative w-full sm:w-72">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </span>
                <input 
                    type="text" 
                    name="q" 
                    id="searchInput"
                    value="{{ request('q') }}" 
                    placeholder="Cari judul artikel..." 
                    class="w-full pl-9 pr-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#008f7a] transition-all shadow-2xs"
                >
            </div>

            <!-- Dropdown Semua Kategori -->
            <div class="w-full sm:w-48">
                <select 
                    name="kategori" 
                    id="filterKategori"
                    class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#008f7a] transition-all shadow-2xs"
                >
                    <option value="">Semua Kategori</option>
                    @foreach ($kategoriList as $kat)
                        <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Dropdown Semua Status -->
            <div class="w-full sm:w-40">
                <select 
                    name="status" 
                    id="filterStatus"
                    class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#008f7a] transition-all shadow-2xs"
                >
                    <option value="">Semua Status</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                </select>
            </div>

            <!-- Tombol Filter & Reset -->
            <button type="submit" id="btnFilter" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 bg-[#005953] hover:bg-[#004742] text-white text-xs font-semibold rounded-xl transition-colors shadow-2xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <span>Filter</span>
            </button>
            @if(request('q') || request('kategori') || request('status'))
                <a href="{{ route('edukasi.artikel.index') }}" class="px-2.5 py-2 text-xs text-slate-400 hover:text-slate-600 transition-colors">
                    Reset
                </a>
            @endif
        </form>

        <!-- Tombol Ekspor Laporan (Mockup Proposal) -->
        <a href="{{ route('edukasi.artikel.export', request()->query()) }}" id="btnEkspor" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl shadow-2xs transition-colors shrink-0">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            <span>Ekspor Laporan</span>
        </a>
    </div>

    <!-- Tabel Data Artikel Edukasi (5 Kolom Sesuai Mockup Gambar 14) -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/70 border-b border-slate-200 text-slate-400 uppercase font-bold text-[11px] tracking-wider">
                    <tr>
                        <th class="px-6 py-4 w-5/12">JUDUL ARTIKEL</th>
                        <th class="px-4 py-4 w-2/12">KATEGORI</th>
                        <th class="px-4 py-4 w-2/12">STATUS</th>
                        <th class="px-4 py-4 w-2/12">TANGGAL</th>
                        <th class="px-6 py-4 w-1/12 text-right">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
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
                                    <a href="{{ route('edukasi.artikel.edit', $artikel->id) }}" 
                                       class="text-teal-700 hover:text-teal-900 font-bold transition-colors">
                                        Edit
                                    </a>

                                    <form action="{{ route('edukasi.artikel.destroy', $artikel->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus artikel ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-slate-500 hover:text-rose-600 font-medium transition-colors">
                                            Hapus
                                        </button>
                                    </form>
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
                </tbody>
            </table>
        </div>

        <!-- Paginasi Khas Proposal Mockup (Menampilkan x-y dari z artikel & Tombol Halaman) -->
        <div class="px-6 py-4 bg-white border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <div class="text-slate-500">
                Menampilkan {{ $artikels->firstItem() ?? 0 }}-{{ $artikels->lastItem() ?? 0 }} dari {{ $artikels->total() }} artikel
            </div>

            <div class="flex items-center gap-1.5">
                {{-- Tombol Previous (<) --}}
                @if ($artikels->onFirstPage())
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-300 border border-slate-200 cursor-not-allowed">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </span>
                @else
                    <a href="{{ $artikels->previousPageUrl() }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-600 border border-slate-200 hover:bg-slate-50 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                @endif

                {{-- Nomor Halaman (1, 2, 3...) --}}
                @foreach ($artikels->getUrlRange(1, $artikels->lastPage()) as $page => $url)
                    @if ($page == $artikels->currentPage())
                        <span class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold bg-[#005953] text-white shadow-2xs">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-semibold text-slate-600 border border-slate-200 hover:bg-slate-50 transition-colors">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach

                {{-- Tombol Next (>) --}}
                @if ($artikels->hasMorePages())
                    <a href="{{ $artikels->nextPageUrl() }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-600 border border-slate-200 hover:bg-slate-50 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @else
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-300 border border-slate-200 cursor-not-allowed">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </span>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const filterForm = document.getElementById('filterForm');
        const filterKategori = document.getElementById('filterKategori');
        const filterStatus = document.getElementById('filterStatus');
        const searchInput = document.getElementById('searchInput');

        if (filterForm) {
            if (filterKategori) {
                filterKategori.addEventListener('change', () => filterForm.submit());
            }

            if (filterStatus) {
                filterStatus.addEventListener('change', () => filterForm.submit());
            }

            let debounceTimer;
            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => filterForm.submit(), 500);
                });
            }
        }
    });
</script>
@endpush
