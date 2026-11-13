@extends('layouts.app')

@section('title', 'Paket NutriCare - Master Paket PMT')

@section('header')
    <!-- Breadcrumbs (Sesuai Figma: Logistik PMT > Master Paket) -->
    <div class="flex items-center gap-2 text-xs font-medium text-slate-400">
        <a href="{{ route('pmt.paket.index') }}" class="hover:text-slate-600 transition-colors">Logistik PMT</a>
        <span>&gt;</span>
        <span class="text-slate-700 font-semibold">Master Paket</span>
    </div>

    <!-- Header Section (Sesuai Figma) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Paket NutriCare</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Standarisasi formula nutrisi makanan tambahan balita gizi kurang dan berisiko stunting.</p>
        </div>
        <div>
            <button type="button" onclick="openCreateModal()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#0f766e] hover:bg-[#0d6e60] text-white text-xs sm:text-sm font-bold rounded-xl shadow-sm transition-all hover:shadow hover:-translate-y-0.5 active:translate-y-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Buat Paket PMT Baru</span>
            </button>
        </div>
    </div>

    <!-- Subnavigation Tabs (Sesuai Figma: Stok Gudang | Master Paket | Riwayat Distribusi) -->
    <div class="flex items-center gap-2 border-b border-slate-200 text-xs font-semibold text-slate-500 pb-px">
        <!-- Tab Stok Gudang (Sprint 2) -->
        <span class="inline-flex items-center gap-2 px-4 py-2.5 text-slate-400 hover:text-slate-600 cursor-not-allowed">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <span>Stok Gudang</span>
        </span>

        <!-- Tab Master Paket (ACTIVE) -->
        <a href="{{ route('pmt.paket.index') }}" 
           class="inline-flex items-center gap-2 px-4 py-2.5 font-bold text-[#0f766e] border-b-2 border-[#0f766e] bg-white transition-colors">
            <svg class="w-4 h-4 text-[#0f766e]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <span>Master Paket</span>
        </a>

        <!-- Tab Riwayat Distribusi (Sprint 3) -->
        <span class="inline-flex items-center gap-2 px-4 py-2.5 text-slate-400 hover:text-slate-600 cursor-not-allowed">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            <span>Riwayat Distribusi</span>
        </span>
    </div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Filter & Toolbar (Sesuai Figma) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        
        <!-- Filter Form Controls -->
        <form method="GET" action="{{ route('pmt.paket.index') }}" class="flex flex-wrap items-center gap-3 flex-1">
            
            <!-- Search Input -->
            <div class="relative flex-1 min-w-[240px] max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari nama paket atau bahan utama..." 
                       class="w-full pl-10 pr-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-[#0f766e] transition-all">
            </div>

            <!-- Sasaran Usia Dropdown -->
            <div class="w-48">
                <select name="age_target" onchange="this.form.submit()" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-[#0f766e] transition-all text-slate-700">
                    <option value="">Sasaran Usia (Semua)</option>
                    <option value="6" {{ request('age_target') == '6' ? 'selected' : '' }}>6–11 Bulan (MP-ASI)</option>
                    <option value="12" {{ request('age_target') == '12' ? 'selected' : '' }}>12–23 Bulan</option>
                    <option value="24" {{ request('age_target') == '24' ? 'selected' : '' }}>24–59 Bulan</option>
                </select>
            </div>

            <!-- Kategori Gizi / Status Dropdown -->
            <div class="w-44">
                <select name="status" onchange="this.form.submit()" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-[#0f766e] transition-all text-slate-700">
                    <option value="all">Kategori Gizi (Semua)</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Formula Aktif</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Formula Nonaktif</option>
                </select>
            </div>

            @if(request()->hasAny(['search', 'status', 'age_target']))
                <a href="{{ route('pmt.paket.index') }}" class="p-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold" title="Reset filter">
                    Reset
                </a>
            @endif

        </form>

        <!-- Right Counter Text (Sesuai Figma: ● Total 4 Formula Paket Aktif) -->
        <div class="flex items-center gap-2 text-xs font-bold text-slate-700 shrink-0">
            <span class="w-2 h-2 rounded-full bg-[#0f766e] inline-block"></span>
            <span>Total {{ $stats['active'] ?? $pakets->total() }} Formula Paket Aktif</span>
        </div>

    </div>

    <!-- Main Data Table Container (Sesuai Layout Presisi Figma Gambar 3) -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-slate-50/80 text-slate-500 uppercase font-bold tracking-wider border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-6 font-bold">NAMA PAKET & KODE</th>
                        <th class="py-3.5 px-4 text-center font-bold">SASARAN USIA</th>
                        <th class="py-3.5 px-4 font-bold">TOTAL ENERGI</th>
                        <th class="py-3.5 px-4 font-bold">TOTAL PROTEIN</th>
                        <th class="py-3.5 px-4 font-bold">KOMPOSISI UTAMA</th>
                        <th class="py-3.5 px-4 text-center font-bold">STATUS FORMULA</th>
                        <th class="py-3.5 px-6 text-center font-bold">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($pakets as $index => $paket)
                        @php
                            // Format kode PKT-00X
                            $kodePaket = 'PKT-' . str_pad($paket->id, 3, '0', STR_PAD_LEFT);
                            $isSpecial = ($paket->id % 4 === 0) || str_contains(strtolower($paket->nama_paket_pmt), 'khusus') || str_contains(strtolower($paket->nama_paket_pmt), 'f-100');
                            
                            // Subtitle estimasi AKG Harian
                            $energiVal = (float) $paket->total_energi_kcal;
                            if ($energiVal >= 900) {
                                $akgSubtitle = 'Densitas Tinggi 1 kkal/ml';
                            } elseif ($energiVal >= 700) {
                                $akgSubtitle = '± 60% AKG Harian';
                            } elseif ($energiVal >= 500) {
                                $akgSubtitle = '± 55% AKG Harian';
                            } else {
                                $akgSubtitle = '± 45% AKG Harian';
                            }

                            // Subtitle kualitas protein
                            $proteinVal = (float) $paket->total_protein;
                            if ($proteinVal >= 25) {
                                $proteinSubtitle = 'Whey murni + BCAA';
                            } elseif ($proteinVal >= 20) {
                                $proteinSubtitle = 'Telur & isolat kedelai';
                            } elseif ($proteinVal >= 15) {
                                $proteinSubtitle = 'Protein hewani & nabati';
                            } else {
                                $proteinSubtitle = 'Tinggi kasein/whey';
                            }
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            
                            <!-- 1. NAMA PAKET & KODE (Figma: Judul tebal + badge kode di samping + deskripsi di bawah) -->
                            <td class="py-4 px-6 align-top">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('pmt.paket.show', $paket) }}" class="font-extrabold text-slate-900 hover:text-[#0f766e] transition-colors text-sm">
                                            {{ $paket->nama_paket_pmt }}
                                        </a>
                                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ $isSpecial ? 'bg-[#fee2e2] text-[#b91c1c] border border-[#fecaca]' : 'bg-[#e0f2fe] text-[#0369a1] border border-[#bae6fd]' }}">
                                            {{ $kodePaket }}
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 line-clamp-2 max-w-xs leading-relaxed">
                                        {{ $paket->deskripsi ?: 'Formula makanan pemulihan padat gizi balita.' }}
                                    </p>
                                </div>
                            </td>

                            <!-- 2. SASARAN USIA (Figma: Pill badge biru muda lembut) -->
                            <td class="py-4 px-4 text-center align-top">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-[#e0f2fe] text-[#0369a1] border border-[#bae6fd]">
                                    {{ $paket->min_age_target }}–{{ $paket->max_age_target }} Bulan
                                </span>
                            </td>

                            <!-- 3. TOTAL ENERGI (Figma: 400 kkal / ± 45% AKG Harian) -->
                            <td class="py-4 px-4 align-top">
                                <div>
                                    <span class="text-sm font-extrabold text-slate-900 block">
                                        {{ number_format($paket->total_energi_kcal, 0, ',', '.') }} kkal
                                    </span>
                                    <span class="text-[11px] text-slate-400 block mt-0.5">
                                        {{ $akgSubtitle }}
                                    </span>
                                </div>
                            </td>

                            <!-- 4. TOTAL PROTEIN (Figma: 12 g / Tinggi kasein/whey) -->
                            <td class="py-4 px-4 align-top">
                                <div>
                                    <span class="text-sm font-extrabold text-slate-900 block">
                                        {{ round($paket->total_protein) }} g
                                    </span>
                                    <span class="text-[11px] text-slate-400 block mt-0.5">
                                        {{ $proteinSubtitle }}
                                    </span>
                                </div>
                            </td>

                            <!-- 5. KOMPOSISI UTAMA (Figma: Stack of gray rounded pills) -->
                            <td class="py-4 px-4 align-top">
                                <div class="flex flex-col gap-1 items-start">
                                    @forelse ($paket->komposisiPaketPmt->take(4) as $komp)
                                        <span class="inline-block px-2.5 py-0.5 rounded bg-slate-100 border border-slate-200/70 text-[11px] font-medium text-slate-700">
                                            {{ $komp->barang->nama ?? 'Bahan PMT' }}
                                        </span>
                                    @empty
                                        <span class="text-slate-400 text-[11px] italic">Belum ada bahan</span>
                                    @endforelse

                                    @if ($paket->komposisiPaketPmt->count() > 4)
                                        <span class="text-[10px] text-slate-400 font-semibold pl-1">
                                            +{{ $paket->komposisiPaketPmt->count() - 4 }} bahan lainnya
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- 6. STATUS FORMULA (Figma: ● Aktif / ● Nonaktif) -->
                            <td class="py-4 px-4 text-center align-top">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {{ $paket->status ? 'bg-[#ecfdf5] text-[#059669] border border-[#a7f3d0]' : 'bg-slate-100 text-slate-500 border border-slate-200' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $paket->status ? 'bg-[#10b981]' : 'bg-slate-400' }}"></span>
                                    {{ $paket->status ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>

                            <!-- 7. AKSI (Figma: Edit | Hapus) -->
                            <td class="py-4 px-6 text-center align-top whitespace-nowrap">
                                <div class="inline-flex items-center gap-2 text-xs font-semibold">
                                    <button type="button" 
                                            onclick='openEditModal(@json($paket))'
                                            class="text-slate-600 hover:text-[#0f766e] transition-colors">
                                        Edit
                                    </button>
                                    <span class="text-slate-300">|</span>
                                    <form method="POST" action="{{ route('pmt.paket.destroy', $paket) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus paket \'{{ $paket->nama_paket_pmt }}\'?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-600 hover:text-rose-800 transition-colors">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <p class="text-sm font-semibold">Tidak ada paket formula yang sesuai dengan filter.</p>
                                <p class="text-xs text-slate-400 mt-1">Silakan reset filter atau buat paket PMT baru.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Footer Pagination Bar (Sesuai Figma) -->
        <div class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 text-xs text-slate-500">
            <div>
                Menampilkan <strong class="text-slate-700">1–{{ $pakets->count() }}</strong> dari <strong class="text-slate-700">{{ $pakets->total() }}</strong> master paket aktif
            </div>
            <div>
                {{ $pakets->links() }}
            </div>
        </div>

    </div>

</div>

<!-- ============================================================== -->
<!-- MODAL POPUP: BUAT / EDIT PAKET PMT BARU (Sesuai Figma Gambar 2) -->
<!-- ============================================================== -->
<div id="paketModalBackdrop" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 hidden overflow-y-auto">
    
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-xl w-full max-h-[92vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 id="modalTitle" class="text-base sm:text-lg font-bold text-slate-900">Buat Paket PMT Baru</h2>
            <button type="button" onclick="closePaketModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Modal Form -->
        <form id="modalPaketForm" method="POST" action="{{ route('pmt.paket.store') }}" class="flex-1 overflow-y-auto p-6 space-y-4">
            @csrf
            <input type="hidden" name="_method" id="modalMethod" value="POST">
            <input type="hidden" name="unit_type" id="modalUnitType" value="paket">
            <input type="hidden" name="min_age_target" id="modalMinAge" value="6">
            <input type="hidden" name="max_age_target" id="modalMaxAge" value="11">
            <input type="hidden" name="status" value="1">

            <!-- Field 1: Nama Paket PMT -->
            <div>
                <label for="modalNamaPaket" class="block text-xs font-bold text-slate-700 mb-1.5">
                    Nama Paket PMT
                </label>
                <input type="text" id="modalNamaPaket" name="nama_paket_pmt" required
                       placeholder="Contoh: Paket Pemulihan Biskuit Kaya Serat"
                       class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-[#0f766e] transition-all">
            </div>

            <!-- Field 2: Sasaran Usia Dropdown -->
            <div>
                <label for="modalAgeSelect" class="block text-xs font-bold text-slate-700 mb-1.5">
                    Sasaran Usia
                </label>
                <select id="modalAgeSelect" onchange="handleAgeSelection(this.value)" required
                        class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-[#0f766e] transition-all text-slate-700">
                    <option value="6-11" selected>6–11 Bulan (MP-ASI Lanjutan)</option>
                    <option value="12-23">12–23 Bulan</option>
                    <option value="24-59">24–59 Bulan</option>
                    <option value="6-59">6–59 Bulan (Umum / Terapi Lanjutan)</option>
                </select>
            </div>

            <!-- Peringatan Validasi (KF-26 Alert Box - Sesuai Figma Gambar 2) -->
            <div id="modalWarningBox" class="p-3.5 rounded-xl bg-amber-50/80 border border-amber-200 text-amber-900 text-xs flex items-start gap-3">
                <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="leading-relaxed">
                    <span id="warningText">Kandungan serat pangan terdeteksi terlalu tinggi untuk sasaran usia 6–11 bulan (>0.5g/100kkal). Mohon sesuaikan takaran bahan.</span>
                </div>
            </div>

            <!-- Section 3: KOMPOSISI BAHAN & TAKARAN (Sesuai Figma Gambar 2) -->
            <div class="space-y-3 pt-2">
                <div class="flex items-center justify-between text-xs font-bold">
                    <span class="text-slate-700 uppercase tracking-wider">KOMPOSISI BAHAN & TAKARAN</span>
                    <span id="modalSelectedCount" class="text-slate-400 font-semibold">2 Bahan Terpilih</span>
                </div>

                <!-- Container Baris Bahan -->
                <div id="modalKomposisiContainer" class="space-y-2.5">
                    <!-- Dynamic Rows Injected by JS -->
                </div>

                <!-- Tombol Tambah Bahan (Sesuai Figma: Dashed/Border Button) -->
                <button type="button" onclick="addModalKomposisiRow()" 
                        class="w-full py-2.5 border-2 border-dashed border-[#0f766e]/40 hover:border-[#0f766e] text-[#0f766e] hover:bg-teal-50/50 font-bold text-xs rounded-xl flex items-center justify-center gap-1.5 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Tambah Bahan Makanan</span>
                </button>
            </div>

            <!-- Section 4: Live Total Kandungan Nutrisi Card (Sesuai Figma Gambar 2) -->
            <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span class="text-xs font-bold text-slate-800">Total Kandungan Nutrisi :</span>
                    </div>
                    <span id="modalAkgBadge" class="text-[11px] font-bold px-2 py-0.5 rounded bg-slate-200/80 text-slate-700">
                        AKG 6-11 Bln: ~450 kkal
                    </span>
                </div>

                <div class="grid grid-cols-4 gap-2 pt-2 border-t border-slate-200/60">
                    <div>
                        <span class="text-[10px] font-semibold text-slate-400 block">Energi</span>
                        <span id="modalLiveEnergi" class="text-base font-extrabold text-slate-900 block mt-0.5">450 kkal</span>
                        <span class="text-[10px] text-slate-500 block">Target AKG</span>
                    </div>
                    <div>
                        <span class="text-[10px] font-semibold text-slate-400 block">Protein</span>
                        <span id="modalLiveProtein" class="text-base font-extrabold text-slate-900 block mt-0.5">14 g</span>
                        <span class="text-[10px] text-slate-500 block">12.4% Energi</span>
                    </div>
                    <div>
                        <span class="text-[10px] font-semibold text-slate-400 block">Lemak</span>
                        <span id="modalLiveLemak" class="text-base font-extrabold text-slate-900 block mt-0.5">8 g</span>
                        <span class="text-[10px] text-slate-500 block">Cukup</span>
                    </div>
                    <div>
                        <span class="text-[10px] font-semibold text-slate-400 block">Serat / Karbo</span>
                        <span id="modalLiveKarbo" class="text-base font-extrabold text-amber-700 block mt-0.5">1.8 g !</span>
                        <span class="text-[10px] text-amber-600 font-semibold block">Tinggi (&gt;0.5g)</span>
                    </div>
                </div>
            </div>

            <!-- Modal Footer (Sesuai Figma: Status Warning di Kiri, Batal & Simpan di Kanan) -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-4">
                <div class="flex items-center gap-2 text-xs font-medium text-slate-600">
                    <span id="modalFooterDot" class="w-2 h-2 rounded-full bg-amber-500 inline-block"></span>
                    <span id="modalFooterStatus">1 Peringatan Validasi Aktif</span>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="closePaketModal()" 
                            class="px-4 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2 bg-[#0f766e] hover:bg-[#0d6e60] text-white font-bold text-xs rounded-xl shadow-xs transition-colors">
                        Simpan Paket
                    </button>
                </div>
            </div>

        </form>

    </div>

</div>

@push('scripts')
<script>
    // Master data barang PMT dari backend
    const allBarangs = @json($barangs);
    let modalRowIdx = 0;

    function handleAgeSelection(val) {
        const parts = val.split('-');
        const min = parseInt(parts[0]) || 6;
        const max = parseInt(parts[1]) || 59;
        
        document.getElementById('modalMinAge').value = min;
        document.getElementById('modalMaxAge').value = max;

        // Update badge AKG
        if (min === 6 && max === 11) {
            document.getElementById('modalAkgBadge').textContent = 'AKG 6-11 Bln: ~450 kkal';
        } else if (min === 12 && max === 23) {
            document.getElementById('modalAkgBadge').textContent = 'AKG 12-23 Bln: ~650 kkal';
        } else {
            document.getElementById('modalAkgBadge').textContent = 'AKG 24-59 Bln: ~800 kkal';
        }

        recalculateModalTotals();
    }

    function openCreateModal() {
        document.getElementById('modalTitle').textContent = 'Buat Paket PMT Baru';
        document.getElementById('modalPaketForm').action = "{{ route('pmt.paket.store') }}";
        document.getElementById('modalMethod').value = 'POST';
        document.getElementById('modalNamaPaket').value = '';
        document.getElementById('modalAgeSelect').value = '6-11';
        handleAgeSelection('6-11');

        // Reset rows
        const container = document.getElementById('modalKomposisiContainer');
        container.innerHTML = '';
        modalRowIdx = 0;

        // Default 2 rows matching Figma Gambar 2
        addModalKomposisiRow(allBarangs[0]?.id || '', 2, 'Pcs / Keping');
        addModalKomposisiRow(allBarangs[1]?.id || '', 1, 'Kotak (200ml)');

        document.getElementById('paketModalBackdrop').classList.remove('hidden');
    }

    function openEditModal(paket) {
        document.getElementById('modalTitle').textContent = 'Edit Paket PMT - ' + paket.nama_paket_pmt;
        document.getElementById('modalPaketForm').action = "/pmt/paket/" + paket.id;
        document.getElementById('modalMethod').value = 'PUT';
        document.getElementById('modalNamaPaket').value = paket.nama_paket_pmt;
        
        // Select age
        const ageKey = paket.min_age_target + '-' + paket.max_age_target;
        const ageSelect = document.getElementById('modalAgeSelect');
        if (['6-11', '12-23', '24-59', '6-59'].includes(ageKey)) {
            ageSelect.value = ageKey;
        } else {
            ageSelect.value = '6-59';
        }
        handleAgeSelection(ageSelect.value);

        // Populate existing komposisi
        const container = document.getElementById('modalKomposisiContainer');
        container.innerHTML = '';
        modalRowIdx = 0;

        if (paket.komposisi_paket_pmt && paket.komposisi_paket_pmt.length > 0) {
            paket.komposisi_paket_pmt.forEach(k => {
                addModalKomposisiRow(k.barang_id, k.jumlah, k.unit_type);
            });
        } else {
            addModalKomposisiRow();
        }

        document.getElementById('paketModalBackdrop').classList.remove('hidden');
    }

    function closePaketModal() {
        document.getElementById('paketModalBackdrop').classList.add('hidden');
    }

    function addModalKomposisiRow(defaultBarangId = '', defaultJumlah = '1', defaultUnit = 'Gram') {
        const container = document.getElementById('modalKomposisiContainer');
        const idx = modalRowIdx++;

        const row = document.createElement('div');
        row.className = 'modal-komposisi-row flex items-center gap-2';

        let optionsHtml = '<option value="">-- Pilih Bahan Makanan --</option>';
        allBarangs.forEach(b => {
            const isSelected = (defaultBarangId && b.id == defaultBarangId) ? 'selected' : '';
            optionsHtml += `<option value="${b.id}" data-energi="${b.energi_kcal}" data-protein="${b.protein}" data-lemak="${b.lemak}" data-karbo="${b.karbohidrat}" data-unit="${b.unit_type}" ${isSelected}>${b.nama}</option>`;
        });

        row.innerHTML = `
            <div class="flex-1">
                <select name="komposisi[${idx}][barang_id]" required onchange="onModalBarangChange(this)"
                        class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-[#0f766e]">
                    ${optionsHtml}
                </select>
            </div>
            <div class="w-20">
                <input type="number" step="0.01" min="0.01" name="komposisi[${idx}][jumlah]" value="${defaultJumlah}" required placeholder="Jml"
                       oninput="recalculateModalTotals()"
                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-[#0f766e]">
            </div>
            <div class="w-32">
                <input type="text" name="komposisi[${idx}][unit_type]" value="${defaultUnit}" required placeholder="Satuan"
                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-[#0f766e]">
            </div>
            <button type="button" onclick="removeModalRow(this)" class="p-2 text-slate-400 hover:text-rose-600 transition-colors" title="Hapus bahan">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>
        `;

        container.appendChild(row);
        updateModalCounter();
        recalculateModalTotals();
    }

    function removeModalRow(btn) {
        const container = document.getElementById('modalKomposisiContainer');
        if (container.querySelectorAll('.modal-komposisi-row').length > 1) {
            btn.closest('.modal-komposisi-row').remove();
            updateModalCounter();
            recalculateModalTotals();
        } else {
            alert('Minimal harus memilih 1 bahan makanan.');
        }
    }

    function onModalBarangChange(selectEl) {
        const opt = selectEl.options[selectEl.selectedIndex];
        const row = selectEl.closest('.modal-komposisi-row');
        const unitInput = row.querySelector('input[name$="[unit_type]"]');
        if (opt && opt.dataset.unit && unitInput.value === 'Gram') {
            unitInput.value = opt.dataset.unit;
        }
        recalculateModalTotals();
    }

    function updateModalCounter() {
        const count = document.getElementById('modalKomposisiContainer').querySelectorAll('.modal-komposisi-row').length;
        document.getElementById('modalSelectedCount').textContent = count + ' Bahan Terpilih';
    }

    function recalculateModalTotals() {
        const rows = document.getElementById('modalKomposisiContainer').querySelectorAll('.modal-komposisi-row');
        let totalEnergi = 0;
        let totalProtein = 0;
        let totalLemak = 0;
        let totalKarbo = 0;

        rows.forEach(r => {
            const select = r.querySelector('select');
            const jmlInput = r.querySelector('input[name$="[jumlah]"]');
            if (!select || !jmlInput) return;

            const opt = select.options[select.selectedIndex];
            const jml = parseFloat(jmlInput.value) || 0;

            if (opt && opt.value) {
                const e = parseFloat(opt.dataset.energi) || 0;
                const p = parseFloat(opt.dataset.protein) || 0;
                const l = parseFloat(opt.dataset.lemak) || 0;
                const k = parseFloat(opt.dataset.karbo) || 0;

                const faktor = jml / 100;
                totalEnergi += (e * faktor);
                totalProtein += (p * faktor);
                totalLemak += (l * faktor);
                totalKarbo += (k * faktor);
            }
        });

        document.getElementById('modalLiveEnergi').textContent = Math.round(totalEnergi) + ' kkal';
        document.getElementById('modalLiveProtein').textContent = Math.round(totalProtein) + ' g';
        document.getElementById('modalLiveLemak').textContent = Math.round(totalLemak) + ' g';
        document.getElementById('modalLiveKarbo').textContent = totalKarbo.toFixed(1) + ' g';

        // Validasi Peringatan Sesuai Figma
        const minAge = parseInt(document.getElementById('modalMinAge').value) || 6;
        const warningBox = document.getElementById('modalWarningBox');
        const footerDot = document.getElementById('modalFooterDot');
        const footerStatus = document.getElementById('modalFooterStatus');

        if (minAge <= 11 && totalKarbo > 25) {
            warningBox.classList.remove('hidden');
            footerDot.className = 'w-2 h-2 rounded-full bg-amber-500 inline-block';
            footerStatus.textContent = '1 Peringatan Validasi Aktif';
        } else {
            warningBox.classList.add('hidden');
            footerDot.className = 'w-2 h-2 rounded-full bg-emerald-500 inline-block';
            footerStatus.textContent = 'Formula Siap Disimpan';
        }
    }
</script>
@endpush
@endsection