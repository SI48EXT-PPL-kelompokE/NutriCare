@extends('layouts.app')

@section('title', 'Ubah Jadwal Posyandu')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Breadcrumb & Title -->
    <div>
        <nav class="flex items-center gap-2 text-xs text-slate-400 font-semibold mb-2">
            <a href="{{ route('jadwal.index') }}" class="hover:text-teal-600 transition-colors">Jadwal Posyandu</a>
            <span>/</span>
            <span class="text-slate-700">Ubah Jadwal</span>
        </nav>
        <div class="flex items-center justify-between">
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-800 tracking-tight">Perbarui Jadwal Posyandu</h1>
            @if($jadwal->status === 'aktif')
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Status: Aktif</span>
            @else
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-200 text-slate-700">Status: Dibatalkan</span>
            @endif
        </div>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">Lakukan penyesuaian waktu, kuota, atau penanggung jawab jadwal kegiatan posyandu.</p>
    </div>

    <!-- Error Summary Alert -->
    @if($errors->any())
    <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-xs sm:text-sm">
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
    <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-xs">
        <form action="{{ route('jadwal.update', $jadwal) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Judul Posyandu -->
            <div>
                <label for="judul" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Nama / Judul Kegiatan <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="judul" id="judul" value="{{ old('judul', $jadwal->judul) }}" required
                       class="w-full px-4 py-2.5 bg-slate-50 border @error('judul') border-rose-400 @else border-slate-200 @enderror rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white transition-all">
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
                        class="w-full px-4 py-2.5 bg-slate-50 border @error('user_id') border-rose-400 @else border-slate-200 @enderror rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white transition-all">
                    @foreach($nakesList as $nakes)
                        <option value="{{ $nakes->id }}" @selected(old('user_id', $jadwal->user_id) == $nakes->id)>
                            {{ $nakes->name }} ({{ $nakes->nip ?? 'NIP -' }})
                        </option>
                    @endforeach
                </select>
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
                           class="w-full px-4 py-2.5 bg-slate-50 border @error('date') border-rose-400 @else border-slate-200 @enderror rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white transition-all">
                    @error('date')
                        <p class="text-[11px] text-rose-500 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="jam_mulai" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Jam Mulai <span class="text-rose-500">*</span>
                    </label>
                    <input type="time" name="jam_mulai" id="jam_mulai" value="{{ old('jam_mulai', substr($jadwal->hour, 0, 5)) }}" required
                           class="w-full px-4 py-2.5 bg-slate-50 border @error('jam_mulai') border-rose-400 @else border-slate-200 @enderror rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white transition-all">
                    @error('jam_mulai')
                        <p class="text-[11px] text-rose-500 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="jam_selesai" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Jam Selesai <span class="text-rose-500">*</span>
                    </label>
                    <input type="time" name="jam_selesai" id="jam_selesai" value="{{ old('jam_selesai', substr($jadwal->jam_selesai, 0, 5)) }}" required
                           class="w-full px-4 py-2.5 bg-slate-50 border @error('jam_selesai') border-rose-400 @else border-slate-200 @enderror rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white transition-all">
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
                           class="w-full px-4 py-2.5 bg-slate-50 border @error('location') border-rose-400 @else border-slate-200 @enderror rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white transition-all">
                    @error('location')
                        <p class="text-[11px] text-rose-500 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="kuota_jadwal" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Kuota Balita <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" name="kuota_jadwal" id="kuota_jadwal" min="1" max="500" value="{{ old('kuota_jadwal', $jadwal->kuota_jadwal) }}" required
                           class="w-full px-4 py-2.5 bg-slate-50 border @error('kuota_jadwal') border-rose-400 @else border-slate-200 @enderror rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white transition-all">
                    @error('kuota_jadwal')
                        <p class="text-[11px] text-rose-500 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Status Jadwal -->
            <div>
                <label for="status" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Status Jadwal <span class="text-rose-500">*</span>
                </label>
                <select name="status" id="status" required
                        class="w-full sm:w-1/3 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white transition-all">
                    <option value="aktif" @selected(old('status', $jadwal->status) === 'aktif')>Aktif</option>
                    <option value="dibatalkan" @selected(old('status', $jadwal->status) === 'dibatalkan')>Dibatalkan</option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <div>
                    @if($jadwal->status === 'aktif')
                    <button type="button" onclick="document.getElementById('cancelForm').submit()" 
                            class="px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs sm:text-sm font-bold rounded-xl transition-colors">
                        Batalkan Jadwal Ini
                    </button>
                    @endif
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('jadwal.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-bold rounded-xl transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-md shadow-teal-600/20 transition-all">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>

        <form id="cancelForm" action="{{ route('jadwal.cancel', $jadwal) }}" method="POST" class="hidden">
            @csrf
            @method('PATCH')
        </form>
    </div>
</div>
@endsection

