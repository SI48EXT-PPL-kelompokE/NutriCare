@extends('layouts.admin')

@section('title', 'Artikel & Panduan Edukasi')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <span>Edukasi & Layanan Warga</span>
                <span>/</span>
                <span class="text-slate-600 font-semibold">Artikel Edukasi</span>
            </nav>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Artikel & Panduan Edukasi</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola materi edukasi pencegahan stunting dan pola gizi balita untuk masyarakat.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('edukasi.artikel.create') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#008f7a] hover:bg-[#007a68] text-white text-xs font-semibold rounded-xl shadow-sm transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Tulis Artikel Baru</span>
            </a>
        </div>
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

    <!-- Filter & pencarian -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
        <form action="{{ route('edukasi.artikel.index') }}" method="GET" id="filterForm" class="flex flex-col md:flex-row md:items-center gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input 
                    type="text" 
                    name="q" 
                    id="searchInput"
                    value="{{ request('q') }}" 
                    placeholder="Cari judul materi edukasi..." 
                    class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#008f7a] focus:bg-white transition-all"
                >
            </div>

            <div class="w-full md:w-52">
                <select 
                    name="kategori" 
                    id="filterKategori"
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008f7a] focus:bg-white transition-all"
                >
                    <option value="">Semua Kategori</option>
                    @foreach ($kategoriList as $kat)
                        <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full md:w-44">
                <select 
                    name="status" 
                    id="filterStatus"
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008f7a] focus:bg-white transition-all"
                >
                    <option value="">Semua Status</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published (Terbit)</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft (Draf)</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" id="btnFilter" class="px-4 py-2.5 bg-[#008f7a] hover:bg-[#007a68] text-white text-xs font-semibold rounded-xl transition-colors shadow-sm flex items-center gap-2">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span>Filter</span>
                </button>
                @if(request('q') || request('kategori') || request('status'))
                    <a href="{{ route('edukasi.artikel.index') }}" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition-colors">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase font-semibold text-[11px] tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Judul Artikel & Cuplikan</th>
                        <th class="px-4 py-4">Kategori</th>
                        <th class="px-4 py-4">Penulis</th>
                        <th class="px-4 py-4">Status</th>
                        <th class="px-4 py-4">Tanggal Publikasi</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse ($artikels as $artikel)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 max-w-sm">
                                <a href="{{ route('edukasi.artikel.show', $artikel->id) }}" class="font-bold text-slate-800 hover:text-[#008f7a] line-clamp-1 transition-colors">
                                    {{ $artikel->judul }}
                                </a>
                                <p class="text-[11px] text-slate-400 line-clamp-1 mt-0.5">
                                    {{ strip_tags($artikel->konten) }}
                                </p>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700">
                                    {{ $artikel->kategori }}
                                </span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-slate-700">
                                {{ $artikel->penulis->name ?? 'Staf Kesehatan' }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                @if ($artikel->status === 'published')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Published
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        Draft
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-slate-500">
                                {{ $artikel->published_at ? $artikel->published_at->format('d M Y') : 'Belum Terbit' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('edukasi.artikel.show', $artikel->id) }}" 
                                       class="p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition-colors" 
                                       title="Lihat Detail">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </a>

                                    <a href="{{ route('edukasi.artikel.edit', $artikel->id) }}" 
                                       class="p-1.5 text-slate-500 hover:text-[#008f7a] hover:bg-emerald-50 rounded-lg transition-colors" 
                                       title="Edit Artikel">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"></path>
                                        </svg>
                                    </a>

                                    <form action="{{ route('edukasi.artikel.destroy', $artikel->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus artikel ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" 
                                                title="Hapus Artikel">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M3 6h18"></path>
                                                <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                                <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
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

        @if ($artikels->hasPages())
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
                {{ $artikels->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const filterForm = document.getElementById('filterForm') || document.querySelector('form[action="{{ route('edukasi.artikel.index') }}"]');
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

                if (searchInput.value) {
                    searchInput.focus();
                    const val = searchInput.value;
                    searchInput.value = '';
                    searchInput.value = val;
                }
            }
        }
    });
</script>
@endpush
