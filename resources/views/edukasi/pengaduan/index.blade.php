@extends('layouts.admin')

@section('title', 'Pengaduan Warga - Edukasi & Layanan Warga')

@section('content')
<div class="space-y-6 w-full" x-data="{ modalOpen: false, activePengaduan: null, statusInput: 'diproses', catatanInput: '' }">

    <!-- Header & Navigasi (Gambar 15 Mockup Proposal) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <span>Edukasi & Layanan Warga</span>
                <span>/</span>
                <span class="text-slate-600 font-semibold">Pengaduan Warga</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Edukasi & Layanan Warga</h1>
            <p class="text-xs text-slate-500 mt-1">
                Kelola artikel edukasi gizi dan tindak lanjut pengaduan warga.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('edukasi.pengaduan.create') }}" 
               id="btnCatatPengaduan"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#005953] hover:bg-[#004742] text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Catat Pengaduan</span>
            </a>
        </div>
    </div>

    <!-- Subnavigation Tabs (Gambar 15 Mockup Proposal) -->
    <div class="flex items-center gap-8 border-b border-slate-200 text-xs font-semibold pb-px">
        <!-- Tab 1: Artikel Edukasi -->
        <a href="{{ route('edukasi.artikel.index') }}" 
           class="inline-flex items-center gap-2.5 pb-3 text-slate-400 font-semibold hover:text-slate-700 transition-colors">
            <span>Artikel Edukasi</span>
            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600">
                {{ $totalArtikel ?? 28 }}
            </span>
        </a>

        <!-- Tab 2: Pengaduan Warga (Aktif) -->
        <a href="{{ route('edukasi.pengaduan.index') }}" 
           class="inline-flex items-center gap-2.5 pb-3 text-[#005953] font-bold border-b-2 border-[#005953] transition-colors">
            <span>Pengaduan Warga</span>
            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-teal-50 text-teal-700 border border-teal-200">
                {{ $totalPengaduan ?? $pengaduans->total() }}
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

    <!-- Bilah Filter & Pencarian (Gambar 15 Mockup Proposal) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
        <form action="{{ route('edukasi.pengaduan.index') }}" method="GET" id="filterFormPengaduan" class="flex flex-wrap items-center gap-3 flex-1">
            <!-- Kolom Pencarian -->
            <div class="relative w-full sm:w-80">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </span>
                <input 
                    type="text" 
                    name="q" 
                    id="searchPengaduanInput"
                    value="{{ request('q') }}" 
                    placeholder="Cari pengaduan, nama warga, atau tiket..." 
                    class="w-full pl-9 pr-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#008f7a] transition-all shadow-2xs"
                >
            </div>

            <!-- Dropdown Semua Kategori Pengaduan -->
            <div class="w-full sm:w-56">
                <select 
                    name="kategori" 
                    id="filterKategoriPengaduan"
                    class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#008f7a] transition-all shadow-2xs"
                >
                    <option value="">Semua Kategori Pengaduan</option>
                    @foreach ($kategoriList as $kat)
                        <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Dropdown Semua Status -->
            <div class="w-full sm:w-40">
                <select 
                    name="status" 
                    id="filterStatusPengaduan"
                    class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#008f7a] transition-all shadow-2xs"
                >
                    <option value="">Semua Status</option>
                    <option value="baru" {{ request('status') == 'baru' ? 'selected' : '' }}>Baru</option>
                    <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>Diproses</option>
                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>

            @if(request('q') || request('kategori') || request('status'))
                <a href="{{ route('edukasi.pengaduan.index') }}" class="px-2.5 py-2 text-xs text-slate-400 hover:text-slate-600 transition-colors">
                    Reset
                </a>
            @endif
        </form>

        <!-- Tombol Ekspor Rekap (Mockup Proposal) -->
        <a href="{{ route('edukasi.pengaduan.export', request()->query()) }}" id="btnEksporRekap" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl shadow-2xs transition-colors shrink-0">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            <span>Ekspor Rekap</span>
        </a>
    </div>

    <!-- Tabel Data Pengaduan Warga (6 Kolom Sesuai Mockup Gambar 15) -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/70 border-b border-slate-200 text-slate-400 uppercase font-bold text-[11px] tracking-wider">
                    <tr>
                        <th class="px-6 py-4 w-2/12">TIKET & TANGGAL</th>
                        <th class="px-4 py-4 w-2/12">KATEGORI PENGADUAN</th>
                        <th class="px-4 py-4 w-2/12">PENGIRIM</th>
                        <th class="px-4 py-4 w-3/12">RINGKASAN PENGADUAN</th>
                        <th class="px-4 py-4 w-1/12">STATUS</th>
                        <th class="px-6 py-4 w-2/12 text-right">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse ($pengaduans as $item)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <!-- Kolom 1: Tiket & Tanggal -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-bold text-slate-900 block text-xs tracking-tight">
                                    #{{ $item->nomor_tiket }}
                                </span>
                                <span class="text-[11px] text-slate-400 block mt-0.5">
                                    {{ $item->created_at->format('d M Y') }}
                                </span>
                            </td>

                            <!-- Kolom 2: Kategori Pengaduan -->
                            <td class="px-4 py-4 text-slate-800 font-semibold">
                                {{ $item->kategori }}
                            </td>

                            <!-- Kolom 3: Pengirim -->
                            <td class="px-4 py-4">
                                @if ($item->is_anonymous)
                                    <span class="italic text-slate-400 font-normal">Anonim</span>
                                @else
                                    <span class="font-semibold text-slate-800 block text-xs">
                                        {{ $item->orangTua->name ?? 'Warga' }}
                                    </span>
                                    <span class="text-[11px] text-slate-400 block mt-0.5">
                                        (Ibu Balita - Posyandu {{ $item->orangTua->alamat ?? 'Mawar' }})
                                    </span>
                                @endif
                            </td>

                            <!-- Kolom 4: Ringkasan Pengaduan -->
                            <td class="px-4 py-4">
                                <p class="text-slate-700 text-xs line-clamp-2 leading-relaxed">
                                    {{ $item->isi }}
                                </p>
                            </td>

                            <!-- Kolom 5: Status -->
                            <td class="px-4 py-4 whitespace-nowrap">
                                @if ($item->status === 'baru')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-sky-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                                        <span>Baru</span>
                                    </span>
                                @elseif ($item->status === 'diproses')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        <span>Diproses</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-teal-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-teal-600"></span>
                                        <span>Selesai</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Kolom 6: Aksi -->
                            <td class="px-6 py-4 whitespace-nowrap text-right text-xs">
                                <div class="inline-flex items-center justify-end gap-3">
                                    @if ($item->status === 'selesai')
                                        <a href="{{ route('edukasi.pengaduan.show', $item->id) }}" 
                                           class="text-[#005953] hover:text-[#004742] font-semibold transition-colors">
                                            Lihat Detail
                                        </a>
                                    @else
                                        <a href="{{ route('edukasi.pengaduan.show', $item->id) }}" 
                                           class="text-[#005953] hover:text-[#004742] font-bold transition-colors">
                                            Tanggapi
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-10 h-10 text-slate-300 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                    </svg>
                                    <p class="text-sm font-semibold text-slate-600">Belum ada pengaduan warga</p>
                                    <p class="text-xs text-slate-400 mt-0.5">Pengaduan dan aspirasi dari masyarakat akan tampil di daftar ini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginasi Khas Proposal Mockup (Menampilkan x-y dari z pengaduan & Tombol Halaman) -->
        <div class="px-6 py-4 bg-white border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <div class="text-slate-500">
                Menampilkan {{ $pengaduans->firstItem() ?? 0 }}-{{ $pengaduans->lastItem() ?? 0 }} dari {{ $pengaduans->total() }} pengaduan
            </div>

            <div class="flex items-center gap-1.5">
                {{-- Tombol Previous (<) --}}
                @if ($pengaduans->onFirstPage())
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-300 border border-slate-200 cursor-not-allowed">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </span>
                @else
                    <a href="{{ $pengaduans->previousPageUrl() }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-600 border border-slate-200 hover:bg-slate-50 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                @endif

                {{-- Nomor Halaman (1, 2, 3...) --}}
                @foreach ($pengaduans->getUrlRange(1, $pengaduans->lastPage()) as $page => $url)
                    @if ($page == $pengaduans->currentPage())
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
                @if ($pengaduans->hasMorePages())
                    <a href="{{ $pengaduans->nextPageUrl() }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-600 border border-slate-200 hover:bg-slate-50 transition-colors">
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
        const filterForm = document.getElementById('filterFormPengaduan');
        const filterKategori = document.getElementById('filterKategoriPengaduan');
        const filterStatus = document.getElementById('filterStatusPengaduan');
        const searchInput = document.getElementById('searchPengaduanInput');

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

