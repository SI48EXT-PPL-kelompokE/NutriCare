@extends('layouts.app')

@section('title', 'Detail Reservasi Kunjungan')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumbs -->
    <div class="flex items-center gap-2 text-xs font-medium text-slate-400">
        <a href="{{ route('jadwal.index') }}" class="hover:text-slate-600">Jadwal & Rujukan</a>
        <span>/</span>
        <a href="{{ route('reservasi.index') }}" class="hover:text-slate-600">Reservasi</a>
        <span>/</span>
        <span class="text-slate-700 font-semibold">Detail #{{ $reservasi->id }}</span>
    </div>

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-bold text-teal-700 uppercase tracking-wider">Tiket Reservasi</span>
                <span>•</span>
                <span class="font-mono text-xs text-slate-400">#RES-{{ str_pad($reservasi->id, 5, '0', STR_PAD_LEFT) }}</span>
            </div>
            <h1 class="text-2xl font-extrabold text-[#0B1C30] tracking-tight">Detail Reservasi Posyandu</h1>
        </div>

        <div>
            @if($reservasi->status === 'dikonfirmasi')
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Terkonfirmasi
                </span>
            @elseif($reservasi->status === 'selesai')
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-teal-50 text-teal-700 border border-teal-200">
                    <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                    Selesai Dihadiri
                </span>
            @elseif($reservasi->status === 'dibatalkan')
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-slate-100 text-slate-500">
                    Dibatalkan
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                    Menunggu
                </span>
            @endif
        </div>
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

    @if($errors->any())
    <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs sm:text-sm">
        <div class="font-bold mb-1">Gagal melakukan perubahan:</div>
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- 2 Columns Grid: Balita & Jadwal -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Balita & Orang Tua Card -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
            <h2 class="text-xs font-bold uppercase tracking-wider text-[#6D7A77] border-b border-slate-100 pb-2">
                Informasi Balita & Keluarga
            </h2>

            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-slate-400 block text-[11px]">Nama Balita</span>
                    <span class="font-extrabold text-sm text-[#0B1C30]">{{ $reservasi->balita?->name ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">Identifier QR</span>
                    <span class="font-mono font-bold text-teal-800">{{ $reservasi->balita?->qr_code ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">Tanggal Lahir / Jenis Kelamin</span>
                    <span class="font-semibold text-slate-700">
                        {{ $reservasi->balita?->birth_date?->format('d M Y') ?? '-' }} • {{ $reservasi->balita?->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                    </span>
                </div>
                <div class="pt-2 border-t border-slate-100">
                    <span class="text-slate-400 block text-[11px]">Orang Tua / Wali</span>
                    <span class="font-bold text-slate-800">{{ $reservasi->orangTua?->name ?? '-' }} (NIK: {{ $reservasi->orangTua?->nik ?? '-' }})</span>
                </div>
            </div>
        </div>

        <!-- Jadwal Kunjungan Card -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
            <h2 class="text-xs font-bold uppercase tracking-wider text-[#6D7A77] border-b border-slate-100 pb-2">
                Jadwal Kunjungan Terdaftar
            </h2>

            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-slate-400 block text-[11px]">Agenda Posyandu</span>
                    <span class="font-extrabold text-sm text-[#0B1C30]">{{ $reservasi->jadwal?->judul ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">Waktu Pelaksanaan</span>
                    <span class="font-semibold text-slate-700">
                        📅 {{ $reservasi->jadwal?->date?->format('l, d M Y') ?? '-' }} • ⏰ {{ $reservasi->jadwal?->hour ? substr($reservasi->jadwal->hour, 0, 5) : '-' }} WIB
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">Lokasi Kegiatan</span>
                    <span class="font-semibold text-slate-700">📍 {{ $reservasi->jadwal?->location ?? '-' }}</span>
                </div>
                <div class="pt-2 border-t border-slate-100">
                    <span class="text-slate-400 block text-[11px]">Batas Waktu Reschedule</span>
                    <span class="font-mono font-bold {{ $reservasi->canReschedule() ? 'text-emerald-700' : 'text-rose-500' }}">
                        {{ $reservasi->batas_reschedule?->format('d M Y H:i') ?? '-' }} WIB
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Reschedule (Ubah Jadwal) -->
    <div id="reschedule-box" class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-xs space-y-4">
        <h2 class="text-base font-extrabold text-[#0B1C30] border-b border-slate-100 pb-3">
            Ubah Jadwal Kunjungan (Reschedule)
        </h2>

        @if($reservasi->canReschedule())
            <form method="POST" action="{{ route('reservasi.reschedule', $reservasi) }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div class="space-y-1.5">
                    <label for="new_jadwal_id" class="text-xs font-bold text-slate-700 block">
                        Pilih Jadwal Alternatif <span class="text-rose-500">*</span>
                    </label>
                    <select name="new_jadwal_id" id="new_jadwal_id" required class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 font-semibold text-slate-800 py-3 px-3.5 focus:ring-teal-500 focus:border-teal-500">
                        <option value="">-- Pilih Jadwal Baru --</option>
                        @foreach($availableJadwal as $aj)
                            <option value="{{ $aj->id }}" {{ $aj->isFull() ? 'disabled' : '' }}>
                                {{ $aj->judul }} • {{ $aj->date->format('d M Y') }} ({{ substr($aj->hour, 0, 5) }} WIB) - Sisa Kuota: {{ $aj->sisa_kuota }} slot {{ $aj->isFull() ? '[PENUH]' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-between pt-3">
                    <p class="text-[11px] text-[#6D7A77]">
                        Pemindahan slot bersifat atomik. Jika jadwal tujuan penuh, slot asal Anda tetap aman.
                    </p>
                    <button type="submit" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-md transition-all">
                        Konfirmasi Pindah Jadwal
                    </button>
                </div>
            </form>
        @else
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-500 space-y-1">
                <p class="font-bold text-slate-700">Reschedule Tidak Tersedia</p>
                <p>
                    @if($reservasi->status === 'selesai')
                        Reservasi ini telah selesai dihadiri.
                    @elseif($reservasi->status === 'dibatalkan')
                        Reservasi ini telah dibatalkan.
                    @else
                        Batas waktu reschedule telah berakhir (maksimal H-1 sebelum kegiatan posyandu). Silakan hubungi petugas kader posyandu jika ada kendala mendesak.
                    @endif
                </p>
            </div>
        @endif
    </div>

    <!-- Actions Bar -->
    <div class="flex items-center justify-between pt-2">
        <a href="{{ route('reservasi.index') }}" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800">
            ← Kembali ke Daftar Reservasi
        </a>

        @if($reservasi->status !== 'dibatalkan' && $reservasi->status !== 'selesai')
        <form method="POST" action="{{ route('reservasi.cancel', $reservasi) }}" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan reservasi ini? Slot kuota akan dilepaskan.')">
            @csrf
            @method('PATCH')
            <button type="submit" class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs rounded-xl border border-rose-200 transition-colors">
                Batalkan Reservasi
            </button>
        </form>
        @endif
    </div>
</div>
@endsection
