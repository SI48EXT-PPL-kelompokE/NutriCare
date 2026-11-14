@extends('layouts.admin')

@section('title', 'Catat Pengaduan Baru - Edukasi & Layanan Warga')

@section('content')
<div class="space-y-6 w-full max-w-4xl mx-auto">

    <div class="flex items-center justify-between">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('edukasi.pengaduan.index') }}" class="hover:text-slate-600 transition-colors">Edukasi & Layanan Warga</a>
                <span>/</span>
                <a href="{{ route('edukasi.pengaduan.index') }}" class="hover:text-slate-600 transition-colors">Pengaduan Warga</a>
                <span>/</span>
                <span class="text-slate-600 font-semibold">Catat Baru</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Catat Pengaduan Warga Baru</h1>
            <p class="text-xs text-slate-500 mt-1">
                Lengkapi formulir di bawah ini untuk mencatat aspirasi atau komplain layanan dari masyarakat.
            </p>
        </div>

        <a href="{{ route('edukasi.pengaduan.index') }}" 
           class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors shadow-2xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali ke Daftar</span>
        </a>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold">
            <p class="font-bold mb-1">Mohon periksa kembali input Anda:</p>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs p-6 md:p-8">
        <form action="{{ route('edukasi.pengaduan.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" x-data="{ isAnon: false }">
            @csrf

            <!-- Baris 1: Kategori & Status Anonimitas -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="kategori" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Kategori Layanan <span class="text-rose-500">*</span>
                    </label>
                    <select name="kategori" id="kategori" required class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#005953]">
                        <option value="">Pilih Kategori...</option>
                        @foreach ($kategoriList as $kat)
                            <option value="{{ $kat }}" {{ old('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Opsi Anonimitas Pelapor
                    </label>
                    <label class="flex items-center gap-3 p-2.5 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors">
                        <input type="checkbox" name="is_anonymous" value="1" x-model="isAnon" class="w-4 h-4 text-[#005953] rounded border-slate-300 focus:ring-[#005953]">
                        <span class="text-xs text-slate-700 font-medium">Sembunyikan identitas pelapor (Anonim)</span>
                    </label>
                </div>
            </div>

            <!-- Baris 2: Orang Tua / Warga Terdaftar (sembunyikan jika anonim) -->
            <div x-show="!isAnon" x-transition>
                <label for="orang_tua_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Warga / Orang Tua Terdaftar (Opsional)
                </label>
                <select name="orang_tua_id" id="orang_tua_id" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#005953]">
                    <option value="">-- Pilih jika warga terdata di sistem --</option>
                    @foreach ($orangTuasList as $ot)
                        <option value="{{ $ot->id }}" {{ old('orang_tua_id') == $ot->id ? 'selected' : '' }}>
                            {{ $ot->name }} (NIK: {{ $ot->nik }}) - {{ $ot->alamat }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Baris 3: Judul Pengaduan -->
            <div>
                <label for="judul" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Judul Pengaduan / Keluhan <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="judul" id="judul" value="{{ old('judul') }}" required placeholder="Contoh: Biskuit PMT Pemulihan belum diterima bulan ini" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#005953]">
            </div>

            <!-- Baris 4: Isi Pengaduan Lengkap -->
            <div>
                <label for="isi" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Uraian Pengaduan / Aspirasi <span class="text-rose-500">*</span>
                </label>
                <textarea name="isi" id="isi" rows="4" required placeholder="Tuliskan detail kronologi atau kebutuhan yang dilaporkan warga..." class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#005953]">{{ old('isi') }}</textarea>
            </div>

            <!-- Baris 5: Upload Foto Bukti -->
            <div>
                <label for="foto_bukti" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Lampiran Foto Bukti Fisik (Opsional)
                </label>
                <input type="file" name="foto_bukti" id="foto_bukti" accept="image/png,image/jpeg,image/jpg" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-teal-50 file:text-[#005953] hover:file:bg-teal-100 cursor-pointer">
                <p class="text-[11px] text-slate-400 mt-1">Format: JPG, JPEG, PNG. Ukuran maksimal 2MB.</p>
            </div>

            <!-- Tombol Aksi Form -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('edukasi.pengaduan.index') }}" class="px-5 py-2.5 text-xs font-semibold text-slate-500 hover:text-slate-700 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-[#005953] hover:bg-[#004742] text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                    Kirim Pengaduan
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

