@extends('layouts.admin')

@section('title', 'Ajukan Sesi Konseling Gizi')

@section('content')
<div class="space-y-6 w-full">

    <!-- Header & navigasi -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <span>Sesi Konseling</span>
                <span>/</span>
                <span class="text-slate-600 font-semibold">Ajukan Sesi Baru</span>
            </nav>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Formulir Pengajuan Konseling Gizi</h1>
            <p class="text-xs text-slate-500 mt-0.5">Lengkapi data keluarga balita dan topik keluhan gizi untuk berkonsultasi dengan tenaga kesehatan.</p>
        </div>

        <div>
            <a href="{{ route('home') }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Kartu formulir pengajuan -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 md:p-8 shadow-sm">
        <form action="{{ route('konseling.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Baris 1: Grid 2 kolom untuk Orang Tua dan Topik -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Pilihan orang tua & balita -->
                <div>
                    <label for="orang_tua_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Orang Tua dan Balita Terdaftar <span class="text-rose-500">*</span>
                    </label>
                    <select 
                        name="orang_tua_id" 
                        id="orang_tua_id" 
                        required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border @error('orang_tua_id') border-rose-400 @else border-slate-200 @enderror rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008f7a] focus:bg-white transition-all"
                    >
                        <option value="">Pilih Orang Tua / Balita...</option>
                        @foreach ($orangTuaList as $ot)
                            @php
                                $balitaNames = $ot->balita->pluck('name')->implode(', ');
                            @endphp
                            <option value="{{ $ot->id }}" @selected(old('orang_tua_id', $ot->id) == $ot->id)>
                                {{ $ot->name }} (NIK: {{ $ot->nik }}) {{ $balitaNames ? '— Balita: ' . $balitaNames : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('orang_tua_id')
                        <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Topik konseling -->
                <div>
                    <label for="topik" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Topik Keluhan Konseling <span class="text-rose-500">*</span>
                    </label>
                    <select 
                        name="topik" 
                        id="topik" 
                        required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border @error('topik') border-rose-400 @else border-slate-200 @enderror rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008f7a] focus:bg-white transition-all"
                    >
                        <option value="">Pilih Topik Konsultasi...</option>
                        @foreach ($topikList as $item)
                            <option value="{{ $item }}" {{ old('topik') == $item ? 'selected' : '' }}>
                                {{ $item }}
                            </option>
                        @endforeach
                    </select>
                    @error('topik')
                        <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Baris 2: Perkiraan waktu yang diharapkan -->
            <div>
                <label for="jadwal_harapan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Perkiraan Waktu yang Diinginkan (Opsional)
                </label>
                <input 
                    type="datetime-local" 
                    name="jadwal_harapan" 
                    id="jadwal_harapan" 
                    value="{{ old('jadwal_harapan') }}"
                    class="w-full md:w-1/2 px-3.5 py-2.5 bg-slate-50 border @error('jadwal_harapan') border-rose-400 @else border-slate-200 @enderror rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008f7a] focus:bg-white transition-all"
                >
                <p class="text-[11px] text-slate-400 mt-1">Jadwal konsultasi final akan diverifikasi dan ditetapkan oleh tenaga kesehatan yang bertugas.</p>
                @error('jadwal_harapan')
                    <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Baris 3: Keluhan awal / catatan masalah gizi -->
            <div>
                <label for="keluhan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Catatan Keluhan Awal / Riwayat Masalah Gizi
                </label>
                <textarea 
                    name="keluhan" 
                    id="keluhan" 
                    rows="5" 
                    placeholder="Contoh: Balita mengalami penurunan nafsu makan sejak 2 minggu terakhir, berat badan tidak naik pada penimbangan KMS..."
                    class="w-full px-3.5 py-2.5 bg-slate-50 border @error('keluhan') border-rose-400 @else border-slate-200 @enderror rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008f7a] focus:bg-white transition-all resize-y"
                >{{ old('keluhan') }}</textarea>
                @error('keluhan')
                    <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tombol aksi -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('home') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit" id="btnSubmitKonseling" class="px-6 py-2.5 bg-[#008f7a] hover:bg-[#007a68] text-white text-xs font-semibold rounded-xl transition-colors shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14"></path>
                        <path d="m12 5 7 7-7 7"></path>
                    </svg>
                    <span>Kirim Pengajuan Konseling</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

