@extends('layouts.app')

@section('title', $paket->nama_paket_pmt)

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">

    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between">
        <a href="{{ route('pmt.paket.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-teal-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali ke Master Paket PMT</span>
        </a>

        <div class="flex items-center gap-2">
            <a href="{{ route('pmt.paket.edit', $paket) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-blue-50 text-blue-700 hover:bg-blue-100 text-xs font-bold rounded-xl border border-blue-200 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <span>Edit Paket</span>
            </a>
            
            <form method="POST" action="{{ route('pmt.paket.destroy', $paket) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus paket ini?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-bold rounded-xl border border-rose-200 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <span>Hapus</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Main Header Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $paket->status ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500 border border-slate-200' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $paket->status ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                        {{ $paket->status ? 'Status: Aktif' : 'Status: Nonaktif' }}
                    </span>
                    <span class="text-xs font-medium text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full">
                        Satuan Penyaluran: <strong class="text-slate-700">{{ $paket->unit_type }}</strong>
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ $paket->nama_paket_pmt }}</h1>
                <p class="text-sm text-slate-600 mt-1 max-w-2xl leading-relaxed">
                    {{ $paket->deskripsi ?: 'Tidak ada deskripsi rinci untuk paket ini.' }}
                </p>
            </div>
            
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 flex flex-col items-center justify-center text-center min-w-[140px]">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Kalori</span>
                <span class="text-3xl font-black text-amber-600 mt-0.5">{{ $paket->total_energi_kcal }}</span>
                <span class="text-[11px] font-semibold text-slate-400">Kilokalori (Kcal)</span>
            </div>
        </div>

        <!-- Target Usia & Validasi Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-slate-100">
            <div class="flex items-center gap-3 p-3.5 rounded-xl bg-emerald-50/60 border border-emerald-100 text-emerald-900">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-700 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-medium text-emerald-700 block">Rentang Sasaran Usia Balita:</span>
                    <span class="text-base font-bold text-emerald-900">{{ $paket->min_age_target }} s.d. {{ $paket->max_age_target }} Bulan</span>
                    <span class="text-[11px] text-emerald-600 block">Sesuai pedoman PMT intervensi stunting (6 - 59 bulan)</span>
                </div>
            </div>

            <div class="flex items-center gap-3 p-3.5 rounded-xl bg-slate-50 border border-slate-100 text-slate-700">
                <div class="w-10 h-10 rounded-lg bg-slate-200/80 flex items-center justify-center text-slate-600 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-medium text-slate-500 block">Riwayat Distribusi:</span>
                    <span class="text-base font-bold text-slate-800">{{ $distribusiCount }} kali disalurkan</span>
                    <span class="text-[11px] text-slate-500 block">Dihubungkan ke logistik penyaluran PMT balita</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Macronutrient Breakdown -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
        <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <span>Distribusi Makronutrien Paket</span>
        </h2>

        <!-- Visual Stacked Bar -->
        <div class="h-4 w-full bg-slate-100 rounded-full overflow-hidden flex shadow-inner">
            <div style="width: {{ $persenProtein }}%" class="bg-blue-500 h-full transition-all" title="Protein: {{ $paket->total_protein }}g ({{ $persenProtein }}%)"></div>
            <div style="width: {{ $persenLemak }}%" class="bg-rose-500 h-full transition-all" title="Lemak: {{ $paket->total_lemak }}g ({{ $persenLemak }}%)"></div>
            <div style="width: {{ $persenKarbo }}%" class="bg-violet-500 h-full transition-all" title="Karbohidrat: {{ $paket->total_karbohidrat }}g ({{ $persenKarbo }}%)"></div>
        </div>

        <!-- Legend & Values -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
            <div class="p-3 rounded-xl bg-blue-50/70 border border-blue-100 flex items-center gap-3">
                <div class="w-3.5 h-3.5 rounded-full bg-blue-500 shrink-0"></div>
                <div>
                    <span class="text-xs font-semibold text-blue-900 block">Protein</span>
                    <span class="text-lg font-extrabold text-blue-950">{{ $paket->total_protein }} <span class="text-xs font-normal">gram</span></span>
                    <span class="text-[11px] text-blue-700 font-medium ml-1">({{ $persenProtein }}%)</span>
                </div>
            </div>

            <div class="p-3 rounded-xl bg-rose-50/70 border border-rose-100 flex items-center gap-3">
                <div class="w-3.5 h-3.5 rounded-full bg-rose-500 shrink-0"></div>
                <div>
                    <span class="text-xs font-semibold text-rose-900 block">Lemak</span>
                    <span class="text-lg font-extrabold text-rose-950">{{ $paket->total_lemak }} <span class="text-xs font-normal">gram</span></span>
                    <span class="text-[11px] text-rose-700 font-medium ml-1">({{ $persenLemak }}%)</span>
                </div>
            </div>

            <div class="p-3 rounded-xl bg-violet-50/70 border border-violet-100 flex items-center gap-3">
                <div class="w-3.5 h-3.5 rounded-full bg-violet-500 shrink-0"></div>
                <div>
                    <span class="text-xs font-semibold text-violet-900 block">Karbohidrat</span>
                    <span class="text-lg font-extrabold text-violet-950">{{ $paket->total_karbohidrat }} <span class="text-xs font-normal">gram</span></span>
                    <span class="text-[11px] text-violet-700 font-medium ml-1">({{ $persenKarbo }}%)</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Composition Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-800">Komposisi Bahan Makanan</h2>
                <p class="text-xs text-slate-500">Rincian takaran dan kontribusi nilai gizi dari setiap bahan makanan penyusun.</p>
            </div>
            <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200">
                {{ $paket->komposisiPaketPmt->count() }} Bahan Terdaftar
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50/80 text-slate-600 text-xs font-semibold uppercase tracking-wider border-b border-slate-200/80">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Nama Bahan</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4 text-center">Takaran</th>
                        <th class="py-3 px-4 text-right">Energi (Kcal)</th>
                        <th class="py-3 px-4 text-right">Protein (g)</th>
                        <th class="py-3 px-4 text-right">Lemak (g)</th>
                        <th class="py-3 px-4 text-right">Karbohidrat (g)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($paket->komposisiPaketPmt as $index => $item)
                        @php
                            $barang = $item->barang;
                            $faktor = $item->jumlah / 100;
                            $energiItem = round(($barang->energi_kcal ?? 0) * $faktor, 2);
                            $proteinItem = round(($barang->protein ?? 0) * $faktor, 2);
                            $lemakItem = round(($barang->lemak ?? 0) * $faktor, 2);
                            $karboItem = round(($barang->karbohidrat ?? 0) * $faktor, 2);
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4 text-center text-xs text-slate-400 font-medium">{{ $index + 1 }}</td>
                            <td class="py-3.5 px-4 font-semibold text-slate-900">
                                {{ $barang->nama ?? 'Bahan tidak ditemukan' }}
                            </td>
                            <td class="py-3.5 px-4 text-xs">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-medium">
                                    {{ $barang->kategori ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-slate-800">
                                {{ $item->jumlah }} <span class="text-xs font-normal text-slate-500">{{ $item->unit_type }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-medium text-amber-700">
                                {{ $energiItem }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-medium text-blue-700">
                                {{ $proteinItem }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-medium text-rose-700">
                                {{ $lemakItem }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-medium text-violet-700">
                                {{ $karboItem }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 px-4 text-center text-slate-400 text-sm">
                                Belum ada komposisi bahan makanan dalam paket ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-50 font-bold text-slate-900 border-t-2 border-slate-200">
                    <tr>
                        <td colspan="4" class="py-3.5 px-4 text-right uppercase text-xs tracking-wider">
                            Total Akumulasi Nutrisi:
                        </td>
                        <td class="py-3.5 px-4 text-right text-amber-700 text-base">
                            {{ $paket->total_energi_kcal }} <span class="text-xs">Kcal</span>
                        </td>
                        <td class="py-3.5 px-4 text-right text-blue-700 text-base">
                            {{ $paket->total_protein }} <span class="text-xs">g</span>
                        </td>
                        <td class="py-3.5 px-4 text-right text-rose-700 text-base">
                            {{ $paket->total_lemak }} <span class="text-xs">g</span>
                        </td>
                        <td class="py-3.5 px-4 text-right text-violet-700 text-base">
                            {{ $paket->total_karbohidrat }} <span class="text-xs">g</span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Metadata & Audit Log -->
    <div class="text-xs text-slate-400 flex flex-col sm:flex-row items-center justify-between gap-2 px-2">
        <span>ID Paket: #{{ $paket->id }}</span>
        <span>Dibuat: {{ $paket->created_at?->translatedFormat('d F Y, H:i') ?? '-' }} &bull; Terakhir Diubah: {{ $paket->updated_at?->translatedFormat('d F Y, H:i') ?? '-' }}</span>
    </div>

</div>
@endsection
