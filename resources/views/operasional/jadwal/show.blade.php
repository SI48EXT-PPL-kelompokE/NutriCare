@extends('layouts.app')

@section('title', 'Detail Jadwal Posyandu - ' . $jadwal->judul)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumbs -->
    <div class="flex items-center gap-2 text-xs font-medium text-slate-400">
        <a href="{{ route('jadwal.index') }}" class="hover:text-slate-600">Jadwal & Rujukan</a>
        <span>/</span>
        <a href="{{ route('jadwal.index') }}" class="hover:text-slate-600">Kalender & Agenda</a>
        <span>/</span>
        <span class="text-slate-700 font-semibold">{{ $jadwal->judul }}</span>
    </div>

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="font-bold text-xs px-2.5 py-1 rounded-lg bg-teal-50 text-teal-800 border border-teal-200">ID: #{{ $jadwal->id }}</span>
                @if($jadwal->status === 'aktif')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Aktif</span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-200 text-slate-700">Dibatalkan</span>
                @endif
            </div>
            <h1 class="text-2xl font-extrabold text-[#0B1C30] tracking-tight mt-2">{{ $jadwal->judul }}</h1>
            <p class="text-xs text-[#6D7A77]">Informasi operasional posyandu, alokasi penanggung jawab medis, dan daftar kehadiran pengunjung.</p>
        </div>

        <div class="flex items-center gap-2">
            @can('viewAny', App\Models\Presensi::class)
            <a href="{{ route('presensi.index', ['jadwal_id' => $jadwal->id]) }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-teal-50 text-[#00685F] text-xs font-bold hover:bg-teal-100 border border-teal-200 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Buka Presensi QR</span>
            </a>
            @endcan

            @can('update', $jadwal)
            <a href="{{ route('jadwal.edit', $jadwal) }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-[#00685F] text-white text-xs font-bold hover:bg-[#00524B] shadow-xs transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <span>Ubah Jadwal</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Summary Details Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <!-- Tanggal & Waktu -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-1">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Tanggal & Waktu</span>
            <div class="text-sm font-extrabold text-[#0B1C30]">{{ $jadwal->date ? $jadwal->date->format('d F Y') : '-' }}</div>
            <div class="text-xs text-[#00685F] font-semibold">{{ substr($jadwal->hour, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }} WIB</div>
        </div>

        <!-- Lokasi -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-1">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Lokasi Pelaksanaan</span>
            <div class="text-sm font-bold text-[#0B1C30] truncate">{{ $jadwal->location }}</div>
            <div class="text-xs text-slate-400 font-medium">Posyandu Wilayah</div>
        </div>

        <!-- Nakes -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-1">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Nakes Penanggung Jawab</span>
            <div class="text-sm font-bold text-[#0B1C30] truncate">{{ $jadwal->penanggungJawab?->name ?? 'Belum ditentukan' }}</div>
            <div class="text-xs text-slate-400 font-medium">{{ $jadwal->penanggungJawab?->nip ? 'NIP: ' . $jadwal->penanggungJawab->nip : 'Tenaga Medis' }}</div>
        </div>

        <!-- Kuota & Reservasi -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-1">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Kapasitas Kuota</span>
            <div class="text-sm font-extrabold text-[#0B1C30]">{{ $jadwal->kuota_jadwal }} <span class="text-xs font-normal text-slate-400">kuota balita</span></div>
            <div class="text-xs text-slate-400 font-medium">{{ $jadwal->reservasi->count() }} reservasi terdaftar</div>
        </div>
    </div>

    <!-- Table Balita Terdaftar & Kehadiran -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 gap-2">
            <div>
                <h2 class="text-base font-bold text-[#0B1C30]">Balita Terdaftar & Kehadiran</h2>
                <p class="text-xs text-slate-500 mt-0.5">Daftar balita yang telah reservasi dan status presensi pada jadwal ini.</p>
            </div>
            <div class="text-xs text-slate-500">
                Total Presensi: <strong class="text-slate-800">{{ $jadwal->presensi->count() }}</strong> balita
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                        <th class="py-3 px-3">Nama Balita</th>
                        <th class="py-3 px-3">Orang Tua / Wali</th>
                        <th class="py-3 px-3">Status Reservasi</th>
                        <th class="py-3 px-3">Batas Reschedule</th>
                        <th class="py-3 px-3 text-center">Kehadiran (Presensi)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($jadwal->reservasi as $res)
                    @php
                        $presensi = $jadwal->presensi->firstWhere('balita_id', $res->balita_id);
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3 px-3 font-bold text-slate-800">
                            {{ $res->balita?->name ?? 'Balita #' . $res->balita_id }}
                        </td>
                        <td class="py-3 px-3 text-slate-600">
                            {{ $res->orangTua?->name ?? $res->orangTua?->nama_ibu ?? '-' }}
                        </td>
                        <td class="py-3 px-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700">
                                {{ ucfirst($res->status) }}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-slate-400 text-[11px]">
                            {{ $res->batas_reschedule ? $res->batas_reschedule->format('d M Y H:i') : '-' }}
                        </td>
                        <td class="py-3 px-3 text-center">
                            @if($presensi)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    {{ ucfirst($presensi->presence_status) }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
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
