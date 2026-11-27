@extends('layouts.app')

@section('title', 'Daftar Reservasi Kunjungan Posyandu')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumbs -->
    <div class="flex items-center gap-2 text-xs font-medium text-slate-400">
        <a href="{{ route('jadwal.index') }}" class="hover:text-slate-600">Jadwal & Rujukan</a>
        <span>/</span>
        <span class="text-slate-700 font-semibold">Reservasi Kunjungan</span>
    </div>

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-[#0B1C30] tracking-tight">Reservasi Kunjungan Posyandu</h1>
            <p class="text-xs sm:text-sm text-[#6D7A77] mt-1">
                {{ $isWarga ? 'Kelola pendaftaran kunjungan balita Anda pada agenda Posyandu mendatang.' : 'Kelola dan pantau seluruh pendaftaran kuota kunjungan warga.' }}
            </p>
        </div>

        <div>
            <a href="{{ route('reservasi.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#00685F] hover:bg-teal-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-md transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Buat Reservasi Baru</span>
            </a>
        </div>
    </div>

    <!-- Subnavigation Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 text-xs font-semibold text-slate-500 pb-px">
        <a href="{{ route('jadwal.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-t-xl hover:text-slate-700 hover:bg-slate-50 transition-colors">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span>Kalender & Agenda</span>
        </a>

        @can('viewAny', App\Models\Presensi::class)
        <a href="{{ route('presensi.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-t-xl hover:text-slate-700 hover:bg-slate-50 transition-colors">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Presensi & Antrean Hari Ini</span>
        </a>
        @endcan

        <a href="{{ route('reservasi.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-t-xl text-[#00685F] font-bold border-b-2 border-[#00685F] bg-white transition-colors">
            <svg class="w-4 h-4 text-[#00685F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span>Reservasi Kunjungan</span>
        </a>

        @can('viewAny', App\Models\SuratRujukan::class)
        <a href="{{ route('rujukan.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-t-xl hover:text-slate-700 hover:bg-slate-50 transition-colors">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span>Surat Rujukan</span>
        </a>
        @endcan
    </div>

    <!-- Flash Notifications -->
    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-xs sm:text-sm flex items-center justify-between">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-bold">&times;</button>
    </div>
    @endif

    <!-- Card Jadwal Mendatang & Filter -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-extrabold text-[#0B1C30]">Daftar Reservasi Kunjungan</h2>
                <p class="text-xs text-[#6D7A77] mt-0.5">Daftar reservasi slot kunjungan posyandu terkonfirmasi dan riwayat perubahan.</p>
            </div>

            <!-- Filter Form -->
            <form method="GET" action="{{ route('reservasi.index') }}" class="flex flex-wrap items-center gap-2">
                <select name="status" class="text-xs rounded-xl border-slate-200 bg-slate-50 text-slate-700 py-2 px-3">
                    <option value="">Semua Status</option>
                    <option value="dikonfirmasi" {{ request('status') === 'dikonfirmasi' ? 'selected' : '' }}>Dikonfirmasi</option>
                    <option value="menunggu" {{ request('status') === 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                    <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="dibatalkan" {{ request('status') === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                </select>

                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama balita..." class="text-xs rounded-xl border-slate-200 bg-slate-50 text-slate-800 py-2 px-3 w-44 focus:ring-teal-500 focus:border-teal-500">

                <button type="submit" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors">
                    Filter
                </button>
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/80 text-[11px] font-bold text-[#6D7A77] uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-6">Balita</th>
                        <th class="py-3.5 px-6">Jadwal Posyandu</th>
                        <th class="py-3.5 px-6">Lokasi</th>
                        <th class="py-3.5 px-6">Orang Tua / Wali</th>
                        <th class="py-3.5 px-6">Batas Reschedule</th>
                        <th class="py-3.5 px-6">Status</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($reservasiList as $res)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3.5 px-6">
                            <span class="font-bold text-[#0B1C30] block">{{ $res->balita?->name ?? '-' }}</span>
                            <span class="text-[10px] text-teal-700 font-mono">{{ $res->balita?->qr_code ?? '-' }}</span>
                        </td>
                        <td class="py-3.5 px-6">
                            <span class="font-bold text-slate-800 block">{{ $res->jadwal?->judul ?? '-' }}</span>
                            <span class="text-[10px] text-slate-500">{{ $res->jadwal?->date?->format('d M Y') }} • {{ $res->jadwal?->hour ? substr($res->jadwal->hour, 0, 5) : '-' }} WIB</span>
                        </td>
                        <td class="py-3.5 px-6 text-slate-600">
                            {{ $res->jadwal?->location ?? '-' }}
                        </td>
                        <td class="py-3.5 px-6">
                            <span class="font-semibold text-slate-800 block">{{ $res->orangTua?->name ?? '-' }}</span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $res->orangTua?->nik ?? '-' }}</span>
                        </td>
                        <td class="py-3.5 px-6 font-mono text-[11px]">
                            @if($res->batas_reschedule)
                                <span class="{{ now()->gt($res->batas_reschedule) ? 'text-rose-500 line-through' : 'text-slate-700' }}">
                                    {{ $res->batas_reschedule->format('d M Y H:i') }}
                                </span>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-6">
                            @if($res->status === 'dikonfirmasi')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Dikonfirmasi
                                </span>
                            @elseif($res->status === 'menunggu')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    Menunggu
                                </span>
                            @elseif($res->status === 'selesai')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                                    Selesai
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500">
                                    Dibatalkan
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-6 text-right space-x-2">
                            <a href="{{ route('reservasi.show', $res) }}" class="text-[11px] font-bold text-teal-700 hover:text-teal-900">
                                Detail
                            </a>

                            @if($res->canReschedule())
                            <a href="{{ route('reservasi.show', $res) }}#reschedule-box" class="text-[11px] font-bold text-amber-600 hover:text-amber-800">
                                Ubah Jadwal
                            </a>
                            @endif

                            @if($res->status !== 'dibatalkan' && $res->status !== 'selesai')
                            <form method="POST" action="{{ route('reservasi.cancel', $res) }}" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan reservasi ini?')">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-[11px] font-semibold text-rose-500 hover:text-rose-700">
                                    Batalkan
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            <p class="text-xs font-semibold">Belum ada reservasi kunjungan yang terdaftar.</p>
                            <a href="{{ route('reservasi.create') }}" class="text-xs text-teal-700 font-bold hover:underline mt-1 inline-block">
                                Buat reservasi baru sekarang →
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reservasiList->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $reservasiList->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
