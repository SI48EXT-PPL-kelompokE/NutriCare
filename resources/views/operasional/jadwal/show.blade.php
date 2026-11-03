@extends('layouts.app')

@section('title', 'Detail Jadwal Posyandu')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumb & Title -->
    <div>
        <nav class="flex items-center gap-2 text-xs text-slate-400 font-semibold mb-2">
            <a href="{{ route('jadwal.index') }}" class="hover:text-teal-600 transition-colors">Jadwal Posyandu</a>
            <span>/</span>
            <span class="text-slate-700">Detail Jadwal</span>
        </nav>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-800 tracking-tight">{{ $jadwal->judul }}</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Informasi lengkap operasional posyandu, daftar balita reservasi, dan presensi.</p>
            </div>
            <div class="flex items-center gap-3">
                @can('update', $jadwal)
                <a href="{{ route('jadwal.edit', $jadwal) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-xs transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    <span>Ubah Jadwal</span>
                </a>
                @endcan
            </div>
        </div>
    </div>

    <!-- Summary Details Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <!-- Tanggal -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Tanggal Kegiatan</span>
            <div class="text-base font-extrabold text-slate-800">{{ $jadwal->date ? $jadwal->date->format('d F Y') : '-' }}</div>
            <div class="text-xs text-teal-600 font-semibold mt-0.5">{{ substr($jadwal->hour, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }} WIB</div>
        </div>

        <!-- Lokasi -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Lokasi Pelaksanaan</span>
            <div class="text-sm font-bold text-slate-800 truncate">{{ $jadwal->location }}</div>
            <div class="text-xs text-slate-400 font-medium mt-0.5">Posyandu Wilayah</div>
        </div>

        <!-- Nakes -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Nakes Penanggung Jawab</span>
            <div class="text-sm font-bold text-slate-800 truncate">{{ $jadwal->penanggungJawab?->name ?? 'Belum ditentukan' }}</div>
            <div class="text-xs text-slate-400 font-medium mt-0.5">Bidan / Petugas Medis</div>
        </div>

        <!-- Kuota & Status -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Kapasitas & Status</span>
            <div class="flex items-center justify-between">
                <span class="text-base font-extrabold text-slate-800">{{ $jadwal->kuota_jadwal }} <span class="text-xs font-normal text-slate-400">kuota</span></span>
                @if($jadwal->status === 'aktif')
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Aktif</span>
                @else
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700">Dibatalkan</span>
                @endif
            </div>
            <div class="text-xs text-slate-400 font-medium mt-0.5">{{ $jadwal->reservasi->count() }} reservasi terdaftar</div>
        </div>
    </div>

    <!-- Tab Reservasi & Presensi -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="w-3 h-3 rounded-full bg-teal-600"></span>
                <h3 class="text-sm sm:text-base font-bold text-slate-800">Balita Terdaftar & Kehadiran</h3>
            </div>
            <div class="text-xs text-slate-400">
                Total Presensi: <strong class="text-slate-700">{{ $jadwal->presensi->count() }}</strong> balita
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-200">
                        <th class="py-3 px-6">Nama Balita</th>
                        <th class="py-3 px-6">Orang Tua / Wali</th>
                        <th class="py-3 px-6">Status Reservasi</th>
                        <th class="py-3 px-6">Batas Reschedule</th>
                        <th class="py-3 px-6 text-center">Kehadiran (Presensi)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($jadwal->reservasi as $res)
                    @php
                        $presensi = $jadwal->presensi->firstWhere('balita_id', $res->balita_id);
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-4 px-6 font-bold text-slate-800">
                            {{ $res->balita?->name ?? 'Balita #' . $res->balita_id }}
                        </td>
                        <td class="py-4 px-6 text-slate-600">
                            {{ $res->orangTua?->nama_ibu ?? $res->orangTua?->user?->name ?? 'Orang Tua' }}
                        </td>
                        <td class="py-4 px-6">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700">
                                {{ ucfirst($res->status) }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-slate-400 text-[11px]">
                            {{ $res->batas_reschedule ? $res->batas_reschedule->format('d M Y H:i') : '-' }}
                        </td>
                        <td class="py-4 px-6 text-center">
                            @if($presensi)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    {{ $presensi->presence_status }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700">
                                    Belum Hadir
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-400 text-xs">
                            Belum ada reservasi atau kunjungan balita pada jadwal ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

