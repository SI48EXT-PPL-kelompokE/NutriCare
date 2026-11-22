@extends('layouts.app')

@section('title', 'Buat Reservasi Kunjungan Posyandu')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Breadcrumbs -->
    <div class="flex items-center gap-2 text-xs font-medium text-slate-400">
        <a href="{{ route('jadwal.index') }}" class="hover:text-slate-600">Jadwal & Rujukan</a>
        <span>/</span>
        <a href="{{ route('reservasi.index') }}" class="hover:text-slate-600">Reservasi</a>
        <span>/</span>
        <span class="text-slate-700 font-semibold">Buat Baru</span>
    </div>

    <!-- Header Section -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <h1 class="text-2xl font-extrabold text-[#0B1C30] tracking-tight">Pendaftaran Reservasi Posyandu</h1>
        <p class="text-xs text-[#6D7A77] mt-1">Pilih balita sasaran dan jadwal posyandu mendatang untuk mendapatkan slot kunjungan.</p>
    </div>

    <!-- Validation Errors -->
    @if($errors->any())
    <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs sm:text-sm">
        <div class="font-bold mb-1">Pendaftaran tidak dapat diproses:</div>
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Form Card -->
    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-xs">
        <form method="POST" action="{{ route('reservasi.store') }}" class="space-y-6">
            @csrf

            <!-- Pilih Balita -->
            <div class="space-y-1.5">
                <label for="balita_id" class="text-xs font-bold text-slate-700 block">
                    Pilih Balita <span class="text-rose-500">*</span>
                </label>
                <select name="balita_id" id="balita_id" required class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 font-semibold text-slate-800 py-3 px-3.5 focus:ring-teal-500 focus:border-teal-500">
                    <option value="">-- Pilih Balita --</option>
                    @foreach($balitaList as $b)
                        <option value="{{ $b->id }}" {{ old('balita_id') == $b->id ? 'selected' : '' }}>
                            {{ $b->name }} ({{ $b->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}, QR: {{ $b->qr_code }})
                            @if(!$isWarga && $b->orangTua)
                                - Orang Tua: {{ $b->orangTua->name }}
                            @endif
                        </option>
                    @endforeach
                </select>
                @error('balita_id')
                    <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Pilih Jadwal Posyandu -->
            <div class="space-y-1.5">
                <label for="jadwal_id" class="text-xs font-bold text-slate-700 block">
                    Pilih Jadwal Posyandu <span class="text-rose-500">*</span>
                </label>
                <div class="space-y-2">
                    @forelse($jadwalList as $j)
                        @php $penuh = $j->isFull(); @endphp
                        <label class="flex items-start justify-between p-4 rounded-xl border {{ $penuh ? 'bg-slate-100/70 border-slate-200 opacity-60 cursor-not-allowed' : 'bg-slate-50 hover:bg-teal-50/50 border-slate-200 hover:border-teal-500 cursor-pointer' }} transition-all">
                            <div class="flex items-start gap-3">
                                <input type="radio" name="jadwal_id" value="{{ $j->id }}" {{ old('jadwal_id') == $j->id ? 'checked' : '' }} {{ $penuh ? 'disabled' : 'required' }} class="mt-1 text-teal-600 focus:ring-teal-500">
                                <div>
                                    <span class="text-xs font-bold text-slate-800 block">{{ $j->judul }}</span>
                                    <span class="text-[11px] text-slate-500 block mt-0.5">
                                        📅 {{ $j->date->format('l, d M Y') }} • ⏰ {{ substr($j->hour, 0, 5) }} WIB • 📍 {{ $j->location }}
                                    </span>
                                </div>
                            </div>

                            <div>
                                @if($penuh)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700">
                                        Kuota Penuh
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Sisa {{ $j->sisa_kuota }} / {{ $j->kuota_jadwal }} slot
                                    </span>
                                @endif
                            </div>
                        </label>
                    @empty
                        <div class="p-6 text-center text-slate-400 bg-slate-50 rounded-xl border border-slate-200">
                            <p class="text-xs font-semibold">Tidak ada jadwal Posyandu mendatang yang aktif.</p>
                        </div>
                    @endforelse
                </div>
                @error('jadwal_id')
                    <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Info Batas Reschedule Notice -->
            <div class="p-3.5 bg-amber-50 rounded-xl border border-amber-200/60 flex items-start gap-2.5 text-xs text-amber-800">
                <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Perubahan jadwal (reschedule) hanya dapat dilakukan maksimal <strong>H-1 (24 jam sebelum kegiatan posyandu dimulai)</strong>. Pastikan memilih jadwal yang sesuai.</span>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('reservasi.index') }}" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:text-slate-800 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-[#00685F] hover:bg-teal-700 text-white font-bold text-xs rounded-xl shadow-md transition-all">
                    Simpan Reservasi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
