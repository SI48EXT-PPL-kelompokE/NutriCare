@extends('layouts.app')

@section('title', 'Ubah Jadwal Posyandu')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumbs -->
    <div class="flex items-center gap-2 text-xs font-medium text-slate-400">
        <a href="{{ route('jadwal.index') }}" class="hover:text-slate-600">Jadwal & Rujukan</a>
        <span>/</span>
        <a href="{{ route('jadwal.index') }}" class="hover:text-slate-600">Kalender & Agenda</a>
        <span>/</span>
        <span class="text-slate-700 font-semibold">Ubah Jadwal</span>
    </div>

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl font-extrabold text-[#0B1C30]">Perbarui Jadwal Posyandu</h1>
                @if($jadwal->status === 'aktif')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Status: Aktif</span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-200 text-slate-700">Status: Dibatalkan</span>
                @endif
            </div>
            <p class="text-xs text-[#6D7A77] mt-1">Lakukan penyesuaian waktu kegiatan, kuota balita, atau alokasi tenaga kesehatan penanggung jawab.</p>
        </div>

        <a href="{{ route('jadwal.show', $jadwal) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors">
            Kembali ke Detail
        </a>
    </div>

    <!-- Error Summary Alert -->
    @if($errors->any())
    <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs">
        <div class="font-bold flex items-center gap-2 mb-1">
            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Periksa kembali input formulir:</span>
        </div>
        <ul class="list-disc list-inside space-y-1 pl-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
        <form action="{{ route('jadwal.update', $jadwal) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Judul Posyandu -->
            <div>
                <label for="judul" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Nama / Judul Kegiatan <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="judul" id="judul" value="{{ old('judul', $jadwal->judul) }}" required
                       class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 font-medium text-slate-800 p-3 focus:ring-teal-500 focus:border-teal-500 @error('judul') border-rose-400 @enderror">
                @error('judul')
                    <p class="text-[11px] text-rose-500 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Nakes Penanggung Jawab -->
            <div>
                <label for="user_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Tenaga Kesehatan Penanggung Jawab <span class="text-rose-500">*</span>
                </label>
                <select name="user_id" id="user_id" required
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 font-medium text-slate-800 p-3 focus:ring-teal-500 focus:border-teal-500 @error('user_id') border-rose-400 @enderror">
                    <option value="">-- Pilih Tenaga Kesehatan --</option>
                    @foreach($nakesList as $nakes)
                        <option value="{{ $nakes->id }}" @selected(old('user_id', $jadwal->user_id) == $nakes->id)>
                            {{ $nakes->name }} ({{ $nakes->nip ?? 'NIP -' }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Pengecekan bentrok nakes mengecualikan jadwal ini sendiri.</p>
                @error('user_id')
                    <p class="text-[11px] text-rose-500 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tanggal & Jam Mulai / Selesai -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="date" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Tanggal Kegiatan <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="date" id="date" value="{{ old('date', $jadwal->date ? $jadwal->date->format('Y-m-d') : '') }}" required
                           class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 font-medium text-slate-800 p-3 focus:ring-teal-500 focus:border-teal-500 @error('date') border-rose-400 @enderror">
                    @error('date')
                        <p class="text-[11px] text-rose-500 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="jam_mulai" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Jam Mulai <span class="text-rose-500">*</span>
                    </label>
                    <input type="time" name="jam_mulai" id="jam_mulai" value="{{ old('jam_mulai', substr($jadwal->hour, 0, 5)) }}" required
                           class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 font-medium text-slate-800 p-3 focus:ring-teal-500 focus:border-teal-500 @error('jam_mulai') border-rose-400 @enderror">
                    @error('jam_mulai')
                        <p class="text-[11px] text-rose-500 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="jam_selesai" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Jam Selesai <span class="text-rose-500">*</span>
                    </label>
                    <input type="time" name="jam_selesai" id="jam_selesai" value="{{ old('jam_selesai', substr($jadwal->jam_selesai, 0, 5)) }}" required
                           class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 font-medium text-slate-800 p-3 focus:ring-teal-500 focus:border-teal-500 @error('jam_selesai') border-rose-400 @enderror">
                    @error('jam_selesai')
                        <p class="text-[11px] text-rose-500 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Lokasi & Kuota -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label for="location" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Lokasi Posyandu <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="location" id="location" value="{{ old('location', $jadwal->location) }}" required
                           class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 font-medium text-slate-800 p-3 focus:ring-teal-500 focus:border-teal-500 @error('location') border-rose-400 @enderror">
                    @error('location')
                        <p class="text-[11px] text-rose-500 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="kuota_jadwal" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Kuota Balita <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" name="kuota_jadwal" id="kuota_jadwal" min="1" max="500" value="{{ old('kuota_jadwal', $jadwal->kuota_jadwal) }}" required
                           class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 font-medium text-slate-800 p-3 focus:ring-teal-500 focus:border-teal-500 @error('kuota_jadwal') border-rose-400 @enderror">
                    @error('kuota_jadwal')
                        <p class="text-[11px] text-rose-500 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Status Jadwal -->
            <div>
                <label for="status" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Status Operasional Jadwal
                </label>
                <select name="status" id="status" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 font-medium text-slate-800 p-3 focus:ring-teal-500 focus:border-teal-500">
                    <option value="aktif" @selected(old('status', $jadwal->status) === 'aktif')>Aktif</option>
                    <option value="dibatalkan" @selected(old('status', $jadwal->status) === 'dibatalkan')>Dibatalkan</option>
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Jadwal yang dibatalkan tidak memblokir interval nakes dan membatalkan reservasi terkait.</p>
            </div>

            <!-- Buttons -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('jadwal.show', $jadwal) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 text-xs font-bold hover:bg-slate-50 transition-colors">
                    Batal
                </a>
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#00685F] text-white text-xs font-bold hover:bg-[#00524B] shadow-xs transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
