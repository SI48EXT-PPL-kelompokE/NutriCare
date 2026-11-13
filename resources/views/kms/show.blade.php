@extends('layouts.app')

@section('title', 'Detail Pengukuran')

@section('content')
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-xs font-medium text-slate-400 mb-6">
        <a href="{{ route('kms.index') }}" class="hover:text-slate-600">KMS Digital</a>
        <span>/</span>
        <a href="{{ route('kms.index') }}" class="hover:text-slate-600">Pengukuran</a>
        <span>/</span>
        <span class="text-slate-700 font-semibold">Detail</span>
    </div>

    <!-- Header Section -->
    <div class="mb-6">
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Detail Pengukuran</h1>
        <p class="text-xs text-slate-500 mt-1">{{ $kms->balita->name }} - {{ $kms->date->isoFormat('D MMMM YYYY') }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kolom Kiri: Detail Pengukuran & Ringkasan Balita -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Kartu Detail Pengukuran -->
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="p-4 border-b border-slate-200 flex items-center gap-3">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <h2 class="text-sm font-bold text-slate-800">Data Pengukuran</h2>
                </div>

                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-xs text-slate-500 uppercase tracking-wide">Tanggal Ukur</span>
                            <div class="text-sm font-semibold text-slate-800 mt-1">
                                {{ $kms->date->isoFormat('dddd, D MMMM YYYY') }}
                            </div>
                        </div>
                        <div>
                            <span class="text-xs text-slate-500 uppercase tracking-wide">Usia saat Ukur</span>
                            <div class="text-sm font-semibold text-slate-800 mt-1">
                                {{ $kms->usia_label }}
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4 pt-2 border-t border-slate-100">
                        <div>
                            <span class="text-xs text-slate-500 uppercase tracking-wide">Berat Badan</span>
                            <div class="text-2xl font-bold text-teal-700 mt-1">
                                {{ str_replace('.', ',', number_format($kms->berat_badan, 1)) }}
                                <span class="text-sm text-slate-500 font-normal">kg</span>
                            </div>
                        </div>
                        <div>
                            <span class="text-xs text-slate-500 uppercase tracking-wide">Tinggi/Panjang Badan</span>
                            <div class="text-2xl font-bold text-teal-700 mt-1">
                                {{ str_replace('.', ',', number_format($kms->tinggi_badan, 1)) }}
                                <span class="text-sm text-slate-500 font-normal">cm</span>
                            </div>
                        </div>
                        <div>
                            <span class="text-xs text-slate-500 uppercase tracking-wide">LILA</span>
                            <div class="text-2xl font-bold text-teal-700 mt-1">
                                @if($kms->lingkar_lengan_atas)
                                    {{ str_replace('.', ',', number_format($kms->lingkar_lengan_atas, 1)) }}
                                    <span class="text-sm text-slate-500 font-normal">cm</span>
                                @else
                                    <span class="text-sm text-slate-400">-</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-xs text-slate-500 uppercase tracking-wide">Status Gizi</span>
                        <div class="mt-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold rounded-full bg-slate-100 text-slate-600">
                                <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                                Belum dihitung (KF-14/KF-15)
                            </span>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-xs text-slate-500 uppercase tracking-wide">Petugas</span>
                        <div class="text-sm font-semibold text-slate-800 mt-1">
                            {{ $kms->petugas->name ?? 'Tidak diketahui' }}
                        </div>
                    </div>
                </div>

                <!-- Aksi -->
                <div class="p-4 border-t border-slate-200 flex items-center gap-3">
                    <a href="{{ route('kms.edit', $kms) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Ubah Data
                    </a>
                    <form action="{{ route('kms.destroy', $kms) }}" method="POST" 
                          onsubmit="return confirm('Yakin ingin menghapus data pengukuran {{ $kms->balita->name }} tanggal {{ $kms->date->format('d/m/Y') }}?')"
                          class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 text-white text-sm font-semibold rounded-lg hover:bg-rose-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Hapus
                        </button>
                    </form>
                </div>
            </div>

            <!-- Riwayat Pengukuran Balita Ini -->
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="p-4 border-b border-slate-200 flex items-center gap-3">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <h2 class="text-sm font-bold text-slate-800">Riwayat Pengukuran {{ $kms->balita->name }}</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Usia</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">BB (kg)</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Δ BB</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">TB/PB (cm)</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Δ TB</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($riwayatDenganSelisih as $item)
                                <tr class="{{ $item['pengukuran']->id == $kms->id ? 'bg-teal-50' : 'hover:bg-slate-50' }} transition-colors">
                                    <td class="px-4 py-3 text-sm text-slate-700">
                                        {{ $item['pengukuran']->date->isoFormat('D MMM YYYY') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-700">
                                        {{ $item['pengukuran']->usia_label }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-800 font-semibold text-right">
                                        {{ str_replace('.', ',', number_format($item['pengukuran']->berat_badan, 1)) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right">
                                        @if($item['selisih_bb'] !== null)
                                            <span class="font-medium {{ $item['selisih_bb'] >= 0 ? 'text-teal-700' : 'text-rose-700' }}">
                                                {{ $item['selisih_bb'] >= 0 ? '+' : '' }}{{ str_replace('.', ',', number_format($item['selisih_bb'], 1)) }}
                                            </span>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-800 font-semibold text-right">
                                        {{ str_replace('.', ',', number_format($item['pengukuran']->tinggi_badan, 1)) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right">
                                        @if($item['selisih_tb'] !== null)
                                            <span class="font-medium {{ $item['selisih_tb'] >= 0 ? 'text-teal-700' : 'text-rose-700' }}">
                                                {{ $item['selisih_tb'] >= 0 ? '+' : '' }}{{ str_replace('.', ',', number_format($item['selisih_tb'], 1)) }}
                                            </span>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-slate-200 text-xs text-slate-500">
                    <strong>Catatan:</strong> Δ (delta) menunjukkan selisih dari pengukuran sebelumnya. 
                    Grafik pertumbuhan akan tersedia di fitur KF-16.
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Ringkasan Balita -->
        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="p-4 border-b border-slate-200 flex items-center gap-3">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <h2 class="text-sm font-bold text-slate-800">Ringkasan Balita</h2>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <span class="text-xs text-slate-500 uppercase tracking-wide">Nama</span>
                        <div class="text-lg font-bold text-slate-800 mt-1">{{ $kms->balita->name }}</div>
                    </div>

                    <div>
                        <span class="text-xs text-slate-500 uppercase tracking-wide">Jenis Kelamin</span>
                        <div class="text-sm text-slate-700 mt-1">
                            {{ $kms->balita->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}
                        </div>
                    </div>

                    <div>
                        <span class="text-xs text-slate-500 uppercase tracking-wide">Tanggal Lahir</span>
                        <div class="text-sm text-slate-700 mt-1">
                            {{ $kms->balita->birth_date->isoFormat('D MMMM YYYY') }}
                        </div>
                    </div>

                    <div>
                        <span class="text-xs text-slate-500 uppercase tracking-wide">Orang Tua</span>
                        <div class="text-sm text-slate-700 mt-1">
                            {{ $kms->balita->orangTua->name ?? 'Tidak diketahui' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
