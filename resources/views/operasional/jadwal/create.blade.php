@extends('layouts.app')

@section('title', 'Tambah Jadwal Posyandu')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Breadcrumb & Title -->
    <div>
        <nav class="flex items-center gap-2 text-xs text-slate-400 font-semibold mb-2">
            <a href="{{ route('jadwal.index') }}" class="hover:text-teal-600 transition-colors">Jadwal Posyandu</a>
            <span>/</span>
            <span class="text-slate-700">Tambah Jadwal</span>
        </nav>
        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-800 tracking-tight">Buat Jadwal Posyandu Baru</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">Tentukan tanggal, rentang jam operasional, lokasi posyandu, dan alokasikan tenaga kesehatan.</p>
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
        <form action="{{ route('jadwal.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Judul Posyandu -->
            <div>
                <label for="judul" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Nama / Judul Kegiatan <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="judul" id="judul" value="{{ old('judul') }}" required
                       placeholder="Contoh: Posyandu Balita Melati I"
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
                    <option value="">-- Pilih Tenaga Kesehatan --</option>
                    @foreach($nakesList as $nakes)
                        <option value="{{ $nakes->id }}" @selected(old('user_id') == $nakes->id)>
                            {{ $nakes->name }} ({{ $nakes->nip ?? 'NIP -' }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Sistem akan secara otomatis memeriksa ketersediaan nakes agar tidak bentrok dengan jadwal lain.</p>
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
                    <input type="date" name="date" id="date" value="{{ old('date', date('Y-m-d')) }}" required
                           class="w-full px-4 py-2.5 bg-slate-50 border @error('date') border-rose-400 @else border-slate-200 @enderror rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white transition-all">
                    @error('date')
                        <p class="text-[11px] text-rose-500 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="jam_mulai" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Jam Mulai <span class="text-rose-500">*</span>
                    </label>
                    <input type="time" name="jam_mulai" id="jam_mulai" value="{{ old('jam_mulai', '08:00') }}" required
                           class="w-full px-4 py-2.5 bg-slate-50 border @error('jam_mulai') border-rose-400 @else border-slate-200 @enderror rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white transition-all">
                    @error('jam_mulai')
                        <p class="text-[11px] text-rose-500 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="jam_selesai" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Jam Selesai <span class="text-rose-500">*</span>
                    </label>
                    <input type="time" name="jam_selesai" id="jam_selesai" value="{{ old('jam_selesai', '11:00') }}" required
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
                    <input type="text" name="location" id="location" value="{{ old('location') }}" required
                           placeholder="Contoh: Balai Warga RW 02, Kel. Sukajadi"
                           class="w-full px-4 py-2.5 bg-slate-50 border @error('location') border-rose-400 @else border-slate-200 @enderror rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white transition-all">
                    @error('location')
                        <p class="text-[11px] text-rose-500 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="kuota_jadwal" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Kuota Balita <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" name="kuota_jadwal" id="kuota_jadwal" min="1" max="500" value="{{ old('kuota_jadwal', 30) }}" required
                           class="w-full px-4 py-2.5 bg-slate-50 border @error('kuota_jadwal') border-rose-400 @else border-slate-200 @enderror rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white transition-all">
                    @error('kuota_jadwal')
                        <p class="text-[11px] text-rose-500 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Buttons -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('jadwal.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-bold rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-md shadow-teal-600/20 transition-all">
                    Simpan Jadwal
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

