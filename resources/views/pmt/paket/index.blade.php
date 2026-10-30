@extends('layouts.app')

@section('title', 'Master Paket PMT')

@section('content')
<div class="space-y-6">

    <!-- Top Summary Cards (Sesuai Mockup) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        
        <!-- Card 1: Total Paket -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500 block">Total Master Paket PMT</span>
                <div class="flex items-baseline gap-2 mt-2">
                    <span class="text-3xl font-extrabold text-slate-900">{{ $stats['total'] }}</span>
                    <span class="text-xs font-medium text-slate-500">paket terdaftar</span>
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-teal-50 border border-teal-100 flex items-center justify-center text-teal-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
        </div>

        <!-- Card 2: Paket Aktif -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-teal-700 block">Paket Aktif Distribusi</span>
                <div class="flex items-baseline gap-2 mt-2">
                    <span class="text-3xl font-extrabold text-teal-700">{{ $stats['active'] }}</span>
                    <span class="text-xs font-medium text-slate-500">paket siap salur</span>
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <!-- Card 3: Rata-rata Kalori -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500 block">Rata-rata Kalori Paket</span>
                <div class="flex items-baseline gap-2 mt-2">
                    <span class="text-3xl font-extrabold text-slate-900">{{ $stats['avg_calories'] }}</span>
                    <span class="text-xs font-medium text-slate-500">Kcal / paket</span>
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
        </div>

    </div>

    <!-- Filter & Action Bar (Sesuai Mockup) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        
        <!-- Filter Form Controls -->
        <form method="GET" action="{{ route('pmt.paket.index') }}" class="flex flex-wrap items-center gap-3 flex-1">
            
            <!-- Search Input -->
            <div class="relative flex-1 min-w-[220px]">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama paket, komposisi..." 
                       class="w-full pl-10 pr-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 transition-all">
            </div>

            <!-- Status Dropdown -->
            <div class="w-36">
                <select name="status" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 transition-all">
                    <option value="all">Status (Semua)</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            <!-- Target Age Dropdown -->
            <div class="w-40">
                <input type="number" min="6" max="59" name="age_target" value="{{ request('age_target') }}" placeholder="Usia Balita (bln)" 
                       class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 transition-all">
            </div>

            <button type="submit" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl transition-all">
                Filter
            </button>

            @if(request()->hasAny(['search', 'status', 'age_target']))
                <a href="{{ route('pmt.paket.index') }}" class="p-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold" title="Reset filter">
                    Reset
                </a>
            @endif

        </form>

        <!-- Right Action Button (Sesuai Tombol Mockup) -->
        <div class="flex items-center gap-3 shrink-0">
            <a href="{{ route('pmt.paket.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all hover:shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah Paket Baru</span>
            </a>
        </div>

    </div>

    <!-- Main Data Table Container (Sesuai Layout Mockup) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        
        <!-- Table Header Bar -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                </svg>
                <h2 class="text-sm font-bold text-slate-800">Daftar Master Paket PMT</h2>
            </div>
            <div class="flex items-center gap-2 text-[11px] font-medium text-slate-400">
                <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                <span>Terakhir diperbarui: {{ date('d M Y, H:i') }} WIB</span>
            </div>
        </div>

        <!-- Table Data -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase font-bold tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-6">NAMA PAKET PMT</th>
                        <th class="py-3 px-4 text-center">SASARAN USIA</th>
                        <th class="py-3 px-4 text-center">TOTAL NUTRISI (KF-25)</th>
                        <th class="py-3 px-4">KOMPOSISI BAHAN</th>
                        <th class="py-3 px-4 text-center">STATUS</th>
                        <th class="py-3 px-6 text-right">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($pakets as $paket)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            
                            <!-- Nama Paket & Info -->
                            <td class="py-4 px-6">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200">
                                            PKT-{{ str_pad($paket->id, 3, '0', STR_PAD_LEFT) }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 uppercase font-semibold">
                                            {{ $paket->unit_type }}
                                        </span>
                                    </div>
                                    <a href="{{ route('pmt.paket.show', $paket) }}" class="font-bold text-slate-900 hover:text-teal-700 transition-colors block text-sm">
                                        {{ $paket->nama_paket_pmt }}
                                    </a>
                                    <p class="text-[11px] text-slate-400 line-clamp-1 max-w-xs">
                                        {{ $paket->deskripsi ?: 'Tidak ada keterangan khusus.' }}
                                    </p>
                                </div>
                            </td>

                            <!-- Sasaran Usia (KF-26) -->
                            <td class="py-4 px-4 text-center">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-teal-50 text-teal-800 border border-teal-200">
                                    {{ $paket->min_age_target }} - {{ $paket->max_age_target }} Bln
                                </span>
                            </td>

                            <!-- Total Nutrisi (KF-25) -->
                            <td class="py-4 px-4 text-center">
                                <div class="space-y-1">
                                    <span class="text-sm font-extrabold text-amber-600 block">
                                        {{ $paket->total_energi_kcal }} <span class="text-[10px] font-normal text-slate-500">Kcal</span>
                                    </span>
                                    <span class="text-[10px] text-slate-500 block">
                                        P: <strong>{{ $paket->total_protein }}g</strong> &bull; L: <strong>{{ $paket->total_lemak }}g</strong> &bull; K: <strong>{{ $paket->total_karbohidrat }}g</strong>
                                    </span>
                                </div>
                            </td>

                            <!-- Komposisi Bahan -->
                            <td class="py-4 px-4">
                                <div class="space-y-1">
                                    <span class="font-bold text-slate-800 block">
                                        {{ $paket->komposisiPaketPmt->count() }} Bahan Penyusun
                                    </span>
                                    <span class="text-[10px] text-slate-400 block line-clamp-1 max-w-[200px]">
                                        {{ $paket->komposisiPaketPmt->map(fn($k) => $k->barang->nama ?? '')->filter()->join(', ') }}
                                    </span>
                                </div>
                            </td>

                            <!-- Status Pill (Sesuai Mockup) -->
                            <td class="py-4 px-4 text-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold {{ $paket->status ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500 border border-slate-200' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $paket->status ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                    {{ $paket->status ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>

                            <!-- Aksi (Sesuai Mockup) -->
                            <td class="py-4 px-6 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <!-- Tombol Detail / Edit -->
                                    <a href="{{ route('pmt.paket.show', $paket) }}" 
                                       class="px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs transition-colors" title="Lihat Rincian">
                                        Detail
                                    </a>

                                    <a href="{{ route('pmt.paket.edit', $paket) }}" 
                                       class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit Paket">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>

                                    <!-- Delete / Deactivate -->
                                    <form method="POST" action="{{ route('pmt.paket.destroy', $paket) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus paket \'{{ $paket->nama_paket_pmt }}\'?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus Paket">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-400">
                                Tidak ada paket PMT yang sesuai dengan kriteria filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Footer Pagination Bar (Sesuai Mockup) -->
        <div class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 text-xs text-slate-500">
            <div>
                Menampilkan <strong class="text-slate-700">{{ $pakets->firstItem() ?? 0 }}-{{ $pakets->lastItem() ?? 0 }}</strong> dari <strong class="text-slate-700">{{ $pakets->total() }}</strong> paket PMT
            </div>
            <div>
                {{ $pakets->links() }}
            </div>
        </div>

    </div>

</div>
@endsection
