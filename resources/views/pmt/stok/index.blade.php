@extends('layouts.app')

@section('title', 'Stok Gudang PMT - Logistik & FEFO')

@section('content')
<div class="space-y-6">

    <!-- ============================================================== -->
    <!-- 3 KARTU RINGKASAN KPI GUDANG (Sesuai Figma Gambar 1 / Gambar 10) -->
    <!-- ============================================================== -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <!-- Card 1: Total Stok PMT Tersedia (KF-29 Saldo Stok Otomatis) -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 flex items-start justify-between shadow-2xs">
            <div class="space-y-1.5">
                <span class="text-xs font-semibold text-slate-500 block">Total Stok PMT Tersedia</span>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-slate-900 tracking-tight">
                        {{ number_format($stats['total_stok_tersedia'], 0, ',', '.') }}
                    </span>
                    <span class="text-xs font-semibold text-slate-500">unit / paket</span>
                </div>
                <span class="text-[11px] text-slate-400 block">
                    Akumulasi otomatis dari <strong>{{ $stats['total_batch_aktif'] }}</strong> batch aktif
                </span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-teal-50 border border-teal-100 text-teal-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
        </div>

        <!-- Card 2: Mendekati Kedaluwarsa (< 30 Hari) & Stok Kritis (KF-30) -->
        <div class="bg-white rounded-2xl border-2 border-amber-300/90 p-5 flex items-start justify-between shadow-2xs">
            <div class="space-y-1.5">
                <span class="text-xs font-bold text-amber-800 block">Mendekati Kedaluwarsa (&lt; 30 Hari)</span>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-amber-700 tracking-tight">
                        {{ number_format($stats['mendekati_kedaluwarsa_qty'], 0, ',', '.') }}
                    </span>
                    <span class="text-xs font-semibold text-amber-700">unit / paket</span>
                </div>
                <div class="flex flex-wrap items-center gap-2 text-[11px]">
                    <span class="text-amber-700 font-semibold">
                        {{ $stats['mendekati_kedaluwarsa_batches'] }} batch hampir ED
                    </span>
                    @if ($stats['stok_kritis_batches'] > 0)
                        <span class="px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200 font-bold">
                            {{ $stats['stok_kritis_batches'] }} Batch Stok Kritis
                        </span>
                    @endif
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
        </div>

        <!-- Card 3: Tersalurkan Bulan Ini -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 flex items-start justify-between shadow-2xs">
            <div class="space-y-1.5">
                <span class="text-xs font-semibold text-slate-500 block">
                    Tersalurkan Bulan Ini ({{ now()->translatedFormat('M Y') }})
                </span>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-slate-900 tracking-tight">
                        {{ number_format($stats['tersalurkan_bulan_ini'], 0, ',', '.') }}
                    </span>
                    <span class="text-xs font-semibold text-slate-500">paket</span>
                </div>
                <span class="text-[11px] text-slate-400 block">
                    Distribusi keluar dengan prioritas FEFO
                </span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-sky-50 border border-sky-100 text-sky-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

    </div>

    <!-- ============================================================== -->
    <!-- TOOLBAR PENCARIAN, FILTER & TOMBOL AKSI (Sesuai Figma)          -->
    <!-- ============================================================== -->
    <div class="bg-white rounded-2xl border border-slate-200/90 p-4 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 shadow-2xs">

        <!-- Search & Filter Form -->
        <x-search-form results="stok-table-results" :action="route('pmt.stok.index')" class="flex flex-wrap items-center gap-2.5 flex-1">
            <x-search-input :value="request('search')" placeholder="Cari nomor batch, nama barang PMT..." wrapper-class="w-full sm:w-64 flex-1 min-w-[200px]" />

            <!-- Dropdown Kategori -->
            <div class="w-full sm:w-44">
                <select name="kategori" onchange="this.form.submit()" aria-label="Filter Kategori Barang"
                        class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-600">
                    <option value="all">Semua Kategori</option>
                    @foreach ($kategoris as $kat)
                        <option value="{{ $kat }}" {{ request('kategori') === $kat ? 'selected' : '' }}>
                            {{ $kat }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Dropdown Status (FEFO / ED / Kritis) -->
            <div class="w-full sm:w-48">
                <select name="status" onchange="this.form.submit()" aria-label="Filter Status Stok"
                        class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-600">
                    <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>Status (Semua)</option>
                    <option value="prioritas_fefo" {{ request('status') === 'prioritas_fefo' ? 'selected' : '' }}>Prioritas FEFO</option>
                    <option value="hampir_ed" {{ request('status') === 'hampir_ed' ? 'selected' : '' }}>Mendekati ED (&lt; 30 Hari)</option>
                    <option value="stok_kritis" {{ request('status') === 'stok_kritis' ? 'selected' : '' }}>Stok Kritis</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Sudah Kedaluwarsa</option>
                </select>
            </div>

            @if (request()->anyFilled(['search']) || (request('kategori') && request('kategori') !== 'all') || (request('status') && request('status') !== 'all'))
                <a href="{{ route('pmt.stok.index') }}" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition-colors">
                    Reset
                </a>
            @endif
        </x-search-form>

        <!-- Action Buttons Right -->
        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <!-- Tombol Scan Barcode Batch (KF-31 - SCRUM-126) -->
            <button type="button" onclick="openBarcodeModal()"
                    class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all"
                    title="Scan atau cari cepat barcode nomor batch (KF-31)">
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
                <span>Scan Batch</span>
            </button>

            <!-- Tombol + Tambah Barang (KF-27 - SCRUM-114) -->
            @can('access-module', ['stok_gudang', 'create'])
            <button type="button" onclick="openCreateStokModal()"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-800 text-xs font-bold rounded-xl shadow-2xs transition-all">
                <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah Barang</span>
            </button>
            @endcan

            <!-- Tombol Catat Distribusi / Pengeluaran FEFO (KF-28 - SCRUM-117) -->
            @can('access-module', ['stok_gudang', 'edit'])
            <button type="button" onclick="openFefoModal()"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#0f766e] hover:bg-[#0d6e60] text-white text-xs font-bold rounded-xl shadow-xs transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span>Catat Distribusi</span>
            </button>
            @endcan
        </div>

    </div>

    <!-- Validation Error Banner (Jika Ada Error Input Modal) -->
    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
            <div class="font-bold flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Terdapat kesalahan pada data transaksi stok:</span>
            </div>
            <ul class="list-disc list-inside pl-5 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- ============================================================== -->
    <!-- TABEL DAFTAR STOK GUDANG (Sesuai Figma Gambar 1 / Gambar 10)    -->
    <!-- ============================================================== -->
    <x-table-card id="stok-table-results" aria-live="polite">

        <!-- Table Header Bar -->
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                </svg>
                <h2 class="text-sm font-bold text-slate-800">Daftar Stok Gudang</h2>
            </div>
            <div class="flex items-center gap-2 text-[11px] font-medium text-slate-400">
                <span class="w-2 h-2 rounded-full bg-teal-500 inline-block"></span>
                <span>Terakhir diperbarui: {{ now()->translatedFormat('d M Y, H:i') }} WIB</span>
            </div>
        </div>

        <x-data-table>
            <x-slot:head>
                <tr>
                    <th scope="col" class="py-3.5 px-6">NAMA PAKET / BARANG PMT</th>
                    <th scope="col" class="py-3.5 px-4 text-center">NOMOR BATCH</th>
                    <th scope="col" class="py-3.5 px-4">TANGGAL MASUK</th>
                    <th scope="col" class="py-3.5 px-4">KEDALUWARSA (ED)</th>
                    <th scope="col" class="py-3.5 px-4 text-right">SISA STOK</th>
                    <th scope="col" class="py-3.5 px-4 text-center">STATUS FEFO</th>
                    <th scope="col" class="py-3.5 px-6 text-right">AKSI</th>
                </tr>
            </x-slot:head>

            @forelse ($stoks as $stok)
                @php
                    $barang = $stok->barang;
                    $barangQueue = $activeQueuesByBarang->get($stok->barang_id, collect());
                    $fefo = $stockService->determineFefoStatus($stok, $barangQueue);
                    $daysLeft = $stockService->getDaysUntilExpiry($stok);
                    $isNearExpiry = $stockService->isNearExpiry($stok);
                    $isExpired = $stockService->isExpired($stok);
                    $isCritical = $stockService->isCriticalStock($stok);
                    $totalSaldoBarang = round((float) $barangQueue->sum('stock_qty'), 2);
                @endphp
                <tr class="transition-colors {{ $fefo['code'] === 'prioritas_fefo' ? 'bg-amber-50/35 hover:bg-amber-50/60' : 'hover:bg-slate-50/80' }}">

                    <!-- Kolom 1: Nama Barang / Paket PMT -->
                    <td class="py-4 px-6">
                        <div class="space-y-1">
                            <span class="font-bold text-slate-900 text-xs sm:text-sm block">
                                BRG-{{ str_pad((string) $stok->barang_id, 3, '0', STR_PAD_LEFT) }} {{ $barang?->nama ?? 'Barang PMT' }}
                            </span>
                            <span class="text-[11px] text-slate-400 block">
                                Standar Kemenkes RI &middot; {{ $barang?->kategori ?? 'Logistik Gizi' }}
                            </span>
                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-teal-700 bg-teal-50/80 px-2 py-0.5 rounded-md border border-teal-100">
                                Saldo Total Barang: <strong>{{ number_format($totalSaldoBarang, 0, ',', '.') }} {{ $barang?->unit_type }}</strong>
                            </span>
                        </div>
                    </td>

                    <!-- Kolom 2: Nomor Batch -->
                    <td class="py-4 px-4 text-center">
                        <span class="inline-block px-2.5 py-1 rounded-lg bg-indigo-50/90 text-indigo-800 border border-indigo-200/70 font-mono text-[11px] font-bold tracking-tight">
                            {{ $stok->no_batch }}
                        </span>
                    </td>

                    <!-- Kolom 3: Tanggal Masuk -->
                    <td class="py-4 px-4 text-xs text-slate-600 font-medium">
                        {{ $stok->date_in?->translatedFormat('d M Y') ?? '-' }}
                    </td>

                    <!-- Kolom 4: Kedaluwarsa (ED) & Indikator Hampir ED (KF-30) -->
                    <td class="py-4 px-4">
                        <div class="space-y-0.5">
                            <span class="text-xs font-bold block {{ ($isNearExpiry || $isExpired) ? 'text-amber-700' : 'text-slate-800' }}">
                                {{ $stok->exp_date?->translatedFormat('d M Y') ?? '-' }}
                            </span>
                            @if ($isExpired)
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-rose-600">
                                    <span>&#9888; Lewat {{ abs($daysLeft) }} Hari</span>
                                </span>
                            @elseif ($isNearExpiry)
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-600">
                                    <span>&#9201; Sisa {{ $daysLeft }} Hari</span>
                                </span>
                            @else
                                <span class="text-[11px] text-slate-400 block">
                                    Sisa {{ $daysLeft }} Hari
                                </span>
                            @endif
                        </div>
                    </td>

                    <!-- Kolom 5: Sisa Stok & Penanda Stok Kritis (KF-29 & KF-30) -->
                    <td class="py-4 px-4 text-right">
                        <div class="space-y-0.5">
                            <span class="text-xs sm:text-sm font-extrabold block {{ $isCritical ? 'text-rose-600' : 'text-slate-900' }}">
                                {{ number_format((float) $stok->stock_qty, 0, ',', '.') }} {{ $barang?->unit_type }}
                            </span>
                            @if ($isCritical)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200"
                                      title="Di bawah batas stok minimum ({{ $barang?->min_batas_stok }} {{ $barang?->unit_type }})">
                                    &#9888; Stok Kritis
                                </span>
                            @else
                                <span class="text-[10px] text-slate-400 block">
                                    Min. {{ number_format((float) ($barang?->min_batas_stok ?? 0), 0, ',', '.') }} {{ $barang?->unit_type }}
                                </span>
                            @endif
                        </div>
                    </td>

                    <!-- Kolom 6: Status FEFO (KF-28) -->
                    <td class="py-4 px-4 text-center">
                        @if ($fefo['code'] === 'prioritas_fefo')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-amber-100/90 text-amber-800 border border-amber-300">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                <span>Prioritas FEFO</span>
                            </span>
                        @elseif ($fefo['code'] === 'antrean_berikutnya')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                <span class="w-1.5 h-1.5 rounded-xs bg-sky-500"></span>
                                <span>Antrean Berikutnya</span>
                            </span>
                        @elseif ($fefo['code'] === 'stok_aman')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span>Stok Aman</span>
                            </span>
                        @elseif ($fefo['code'] === 'expired')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                <span>Kedaluwarsa</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                <span>Stok Habis</span>
                            </span>
                        @endif
                    </td>

                    <!-- Kolom 7: Aksi (Sesuai Figma) -->
                    <td class="py-4 px-6 text-right">
                        <div class="inline-flex items-center gap-1.5">
                            @can('access-module', ['stok_gudang', 'edit'])
                            <button type="button"
                                    onclick="openFefoModal({{ $stok->barang_id }})"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-teal-200 bg-white hover:bg-teal-50 text-teal-800 font-bold text-[11px] transition-colors">
                                <svg class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                <span>Catat Distribusi</span>
                            </button>

                            <button type="button"
                                    data-id="{{ $stok->id }}"
                                    data-barang-id="{{ $stok->barang_id }}"
                                    data-no-batch="{{ $stok->no_batch }}"
                                    data-date-in="{{ $stok->date_in?->format('Y-m-d') }}"
                                    data-exp-date="{{ $stok->exp_date?->format('Y-m-d') }}"
                                    data-stock-qty="{{ (float) $stok->stock_qty }}"
                                    onclick="openEditStokModalFromBtn(this)"
                                    class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                    title="Edit Data Batch">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            @endcan

                            @can('access-module', ['stok_gudang', 'delete'])
                            <form method="POST" action="{{ route('pmt.stok.destroy', $stok) }}"
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus batch \'{{ $stok->no_batch }}\'?');"
                                  class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus Batch">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="7" class="py-12 text-center text-slate-400 text-xs">
                        Tidak ditemukan data batch stok gudang yang sesuai dengan filter pencarian.
                    </td>
                </tr>
            @endforelse
        </x-data-table>

        <x-pagination :paginator="$stoks" label="batch aktif di gudang" />

    </x-table-card>

</div>

<!-- ============================================================== -->
<!-- MODAL 1: TAMBAH / EDIT BATCH STOK GUDANG (KF-27 & KF-29)        -->
<!-- ============================================================== -->
<div id="stokModalBackdrop" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 hidden overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-lg w-full overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 id="stokModalTitle" class="text-base font-bold text-slate-900">Tambah Stok Barang Masuk</h2>
            <button type="button" onclick="closeStokModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="stokModalForm" method="POST" action="{{ route('pmt.stok.store') }}" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="_method" id="stokModalMethod" value="POST">

            <div>
                <label for="stokBarangId" class="block text-xs font-bold text-slate-700 mb-1.5">Pilih Barang / Paket PMT</label>
                <select id="stokBarangId" name="barang_id" required
                        class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-600">
                    <option value="">-- Pilih Barang PMT --</option>
                    @foreach ($barangs as $b)
                        <option value="{{ $b->id }}" data-unit="{{ $b->unit_type }}" data-min="{{ $b->min_batas_stok }}">
                            {{ $b->nama }} ({{ $b->kategori }} &middot; Satuan: {{ $b->unit_type }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="stokNoBatch" class="block text-xs font-bold text-slate-700 mb-1.5">Nomor Batch</label>
                    <input type="text" id="stokNoBatch" name="no_batch" required
                           placeholder="Contoh: BTC-20261125"
                           class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-mono uppercase focus:outline-none focus:ring-2 focus:ring-teal-600">
                </div>
                <div>
                    <label for="stokQty" class="block text-xs font-bold text-slate-700 mb-1.5">Kuantitas Stok</label>
                    <input type="number" step="0.01" min="0" id="stokQty" name="stock_qty" required
                           placeholder="Contoh: 150"
                           class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-600">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="stokDateIn" class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Masuk Gudang</label>
                    <input type="date" id="stokDateIn" name="date_in" required value="{{ now()->format('Y-m-d') }}"
                           class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-600">
                </div>
                <div>
                    <label for="stokExpDate" class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Kedaluwarsa (ED)</label>
                    <input type="date" id="stokExpDate" name="exp_date" required value="{{ now()->addMonths(6)->format('Y-m-d') }}"
                           class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-600">
                </div>
            </div>

            <div class="p-3 rounded-xl bg-teal-50/70 border border-teal-100 text-[11px] text-teal-800">
                <strong>Saldo Stok Otomatis (KF-29):</strong> Penambahan atau perubahan kuantitas batch akan langsung mengakumulasi ulang total saldo stok barang di gudang secara otomatis.
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeStokModal()"
                        class="px-4 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-semibold text-xs rounded-xl">
                    Batal
                </button>
                <button type="submit"
                        class="px-5 py-2 bg-[#0f766e] hover:bg-[#0d6e60] text-white font-bold text-xs rounded-xl shadow-xs">
                    Simpan Stok Batch
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL 2: PENGELUARAN STOK METODE FEFO (KF-28 & KF-29)           -->
<!-- ============================================================== -->
<div id="fefoModalBackdrop" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 hidden overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-lg w-full overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">Pengeluaran Stok Metode FEFO</h2>
                <p class="text-[11px] text-slate-500">First Expired, First Out &mdash; Prioritas batch dengan ED terdekat</p>
            </div>
            <button type="button" onclick="closeFefoModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('pmt.stok.keluarkan-fefo') }}" class="p-6 space-y-4">
            @csrf

            <div>
                <label for="fefoBarangId" class="block text-xs font-bold text-slate-700 mb-1.5">Barang PMT yang Dikeluarkan</label>
                <select id="fefoBarangId" name="barang_id" required onchange="triggerFefoPreview()"
                        class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-600">
                    <option value="">-- Pilih Barang PMT --</option>
                    @foreach ($barangs as $b)
                        <option value="{{ $b->id }}" data-saldo="{{ $b->saldo_otomatis }}" data-unit="{{ $b->unit_type }}" data-batch="{{ $b->prioritas_batch }}">
                            {{ $b->nama }} (Saldo Tersedia: {{ number_format($b->saldo_otomatis, 0, ',', '.') }} {{ $b->unit_type }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="fefoJumlahKeluar" class="block text-xs font-bold text-slate-700 mb-1.5">Jumlah Pengeluaran Stok</label>
                <input type="number" step="0.01" min="0.01" id="fefoJumlahKeluar" name="jumlah_keluar" required
                       placeholder="Masukkan jumlah stok yang akan dikeluarkan..."
                       oninput="triggerFefoPreview()"
                       class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-600">
            </div>

            <!-- Box Simulasi Alokasi Batch FEFO Otomatis -->
            <div id="fefoPreviewBox" class="rounded-xl border border-slate-200 bg-slate-50/70 p-4 space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-800">Rekomendasi Alokasi Batch (FEFO):</span>
                    <span id="fefoSaldoBadge" class="text-[11px] font-bold px-2 py-0.5 rounded bg-teal-100 text-teal-800">
                        Pilih barang &amp; jumlah
                    </span>
                </div>
                <div id="fefoAllocationList" class="text-xs text-slate-500 space-y-1.5">
                    Sistem akan otomatis mengambil stok dari batch dengan tanggal kedaluwarsa (ED) paling awal terlebih dahulu.
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeFefoModal()"
                        class="px-4 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-semibold text-xs rounded-xl">
                    Batal
                </button>
                <button type="submit"
                        class="px-5 py-2 bg-[#0f766e] hover:bg-[#0d6e60] text-white font-bold text-xs rounded-xl shadow-xs">
                    Proses Pengeluaran FEFO
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL 3: SCAN BARCODE BATCH (KF-31 - SCRUM-126)                 -->
<!-- ============================================================== -->
<div id="barcodeModalBackdrop" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 hidden overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-md w-full overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">Scan Barcode Nomor Batch</h2>
                <p class="text-[11px] text-slate-500">Gunakan scanner USB/kamera atau ketik kode batch (KF-31)</p>
            </div>
            <button type="button" onclick="closeBarcodeModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="p-6 space-y-4">
            <div class="flex items-center gap-2">
                <input type="text" id="barcodeInputCode"
                       placeholder="Scan atau masukkan nomor batch (misal: BATCH-...)"
                       onkeydown="if(event.key === 'Enter'){ event.preventDefault(); lookupBarcodeBatch(); }"
                       class="flex-1 px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-mono uppercase focus:outline-none focus:ring-2 focus:ring-teal-600">
                <button type="button" onclick="lookupBarcodeBatch()"
                        class="px-4 py-2.5 bg-[#0f766e] hover:bg-[#0d6e60] text-white text-xs font-bold rounded-xl">
                    Cari Batch
                </button>
            </div>

            <div id="barcodeScanResult" class="rounded-xl border border-dashed border-slate-200 p-4 text-center text-xs text-slate-400">
                Arahkan barcode scanner ke nomor batch kemasan PMT lalu tekan Enter.
            </div>

            <div class="flex justify-end pt-2">
                <button type="button" onclick="closeBarcodeModal()"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openCreateStokModal() {
        document.getElementById('stokModalTitle').textContent = 'Tambah Stok Barang Masuk';
        document.getElementById('stokModalForm').action = "{{ route('pmt.stok.store') }}";
        document.getElementById('stokModalMethod').value = 'POST';
        document.getElementById('stokBarangId').value = '';
        document.getElementById('stokNoBatch').value = 'BTC-' + new Date().toISOString().slice(0,10).replace(/-/g, '');
        document.getElementById('stokQty').value = '';
        document.getElementById('stokDateIn').value = "{{ now()->format('Y-m-d') }}";
        document.getElementById('stokExpDate').value = "{{ now()->addMonths(6)->format('Y-m-d') }}";
        document.getElementById('stokModalBackdrop').classList.remove('hidden');
    }

    function openEditStokModalFromBtn(btn) {
        openEditStokModal({
            id: btn.dataset.id,
            barang_id: btn.dataset.barangId,
            no_batch: btn.dataset.noBatch,
            date_in: btn.dataset.dateIn,
            exp_date: btn.dataset.expDate,
            stock_qty: btn.dataset.stockQty
        });
    }

    function openEditStokModal(stok) {
        document.getElementById('stokModalTitle').textContent = 'Edit Data Batch - ' + stok.no_batch;
        document.getElementById('stokModalForm').action = "/pmt/stok/" + stok.id;
        document.getElementById('stokModalMethod').value = 'PUT';
        document.getElementById('stokBarangId').value = stok.barang_id;
        document.getElementById('stokNoBatch').value = stok.no_batch;
        document.getElementById('stokQty').value = stok.stock_qty;
        document.getElementById('stokDateIn').value = stok.date_in;
        document.getElementById('stokExpDate').value = stok.exp_date;
        document.getElementById('stokModalBackdrop').classList.remove('hidden');
    }

    function closeStokModal() {
        document.getElementById('stokModalBackdrop').classList.add('hidden');
    }

    function openFefoModal(preselectedBarangId = null) {
        const select = document.getElementById('fefoBarangId');
        if (preselectedBarangId) {
            select.value = preselectedBarangId;
        }
        document.getElementById('fefoJumlahKeluar').value = '';
        triggerFefoPreview();
        document.getElementById('fefoModalBackdrop').classList.remove('hidden');
    }

    function closeFefoModal() {
        document.getElementById('fefoModalBackdrop').classList.add('hidden');
    }

    async function triggerFefoPreview() {
        const barangId = document.getElementById('fefoBarangId').value;
        const qty = parseFloat(document.getElementById('fefoJumlahKeluar').value) || 0;
        const listEl = document.getElementById('fefoAllocationList');
        const badgeEl = document.getElementById('fefoSaldoBadge');

        if (!barangId || qty <= 0) {
            badgeEl.textContent = 'Menunggu input';
            badgeEl.className = 'text-[11px] font-bold px-2 py-0.5 rounded bg-slate-200 text-slate-700';
            listEl.innerHTML = 'Pilih barang dan masukkan jumlah pengeluaran untuk melihat simulasi pengambilan batch FEFO.';
            return;
        }

        try {
            const response = await fetch("{{ route('pmt.stok.preview-fefo') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ barang_id: barangId, jumlah_keluar: qty })
            });

            const data = await response.json();

            if (!response.ok || !data.valid) {
                badgeEl.textContent = 'Stok Tidak Cukup';
                badgeEl.className = 'text-[11px] font-bold px-2 py-0.5 rounded bg-rose-100 text-rose-700';
                listEl.innerHTML = `<span class="text-rose-600 font-semibold">${data.message || 'Stok tidak mencukupi.'}</span>`;
                return;
            }

            badgeEl.textContent = 'Sisa Saldo: ' + data.saldo_setelah;
            badgeEl.className = 'text-[11px] font-bold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800';

            let html = '<div class="space-y-1.5">';
            data.allocations.forEach((a, index) => {
                html += `
                    <div class="flex items-center justify-between p-2 rounded-lg bg-white border border-slate-200 text-xs">
                        <div>
                            <span class="font-bold text-slate-800">#${index + 1} ${a.no_batch}</span>
                            <span class="text-[11px] text-slate-400 block">ED: ${a.exp_date}</span>
                        </div>
                        <div class="text-right">
                            <span class="font-extrabold text-amber-700">Diambil: ${a.allocated_qty}</span>
                            <span class="text-[10px] text-slate-400 block">Sisa batch: ${a.remaining_qty}</span>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            listEl.innerHTML = html;
        } catch (e) {
            listEl.innerHTML = 'Gagal memuat simulasi FEFO.';
        }
    }

    function openBarcodeModal() {
        const input = document.getElementById('barcodeInputCode');
        input.value = '';
        document.getElementById('barcodeScanResult').innerHTML = 'Arahkan barcode scanner ke nomor batch kemasan PMT lalu tekan Enter.';
        document.getElementById('barcodeModalBackdrop').classList.remove('hidden');
        setTimeout(() => input.focus(), 100);
    }

    function closeBarcodeModal() {
        document.getElementById('barcodeModalBackdrop').classList.add('hidden');
    }

    async function lookupBarcodeBatch() {
        const code = document.getElementById('barcodeInputCode').value.trim();
        const resultEl = document.getElementById('barcodeScanResult');

        if (!code) {
            resultEl.innerHTML = '<span class="text-rose-600 font-semibold">Masukkan kode barcode batch terlebih dahulu.</span>';
            return;
        }

        try {
            const url = new URL("{{ route('pmt.stok.scan-batch') }}", window.location.origin);
            url.searchParams.set('code', code);

            const response = await fetch(url.toString(), {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();

            if (!response.ok || !data.found) {
                resultEl.innerHTML = `<span class="text-rose-600 font-semibold">${data.message || 'Batch tidak ditemukan.'}</span>`;
                return;
            }

            const b = data.batch;
            resultEl.innerHTML = `
                <div class="text-left space-y-2 p-1">
                    <div class="flex items-center justify-between">
                        <span class="font-mono font-bold text-indigo-700 bg-indigo-50 px-2.5 py-0.5 rounded border border-indigo-200">${b.no_batch}</span>
                        <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-teal-50 text-teal-800 border border-teal-200">${b.fefo_status.label}</span>
                    </div>
                    <div>
                        <span class="font-bold text-slate-900 text-sm block">${b.barang_nama}</span>
                        <span class="text-[11px] text-slate-500">ED: <strong>${b.exp_date}</strong> (${b.days_until_expiry} hari) &middot; Stok Batch: <strong>${b.stock_qty} ${b.unit_type}</strong></span>
                    </div>
                    <div class="pt-2 flex items-center gap-2">
                        <a href="{{ route('pmt.stok.index') }}?search=${encodeURIComponent(b.no_batch)}"
                           class="px-3 py-1.5 bg-slate-800 text-white font-bold text-[11px] rounded-lg">
                            Tampilkan di Tabel
                        </a>
                        <button type="button" onclick="closeBarcodeModal(); openFefoModal(${b.barang_id});"
                                class="px-3 py-1.5 bg-teal-600 text-white font-bold text-[11px] rounded-lg">
                            Keluarkan Stok FEFO
                        </button>
                    </div>
                </div>
            `;
        } catch (e) {
            resultEl.innerHTML = '<span class="text-rose-600">Terjadi kesalahan saat memindai batch.</span>';
        }
    }
</script>
@endpush
@endsection
