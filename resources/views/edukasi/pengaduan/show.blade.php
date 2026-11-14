@extends('layouts.admin')

@section('title', 'Detail Pengaduan #' . $pengaduan->nomor_tiket . ' - Edukasi & Layanan Warga')

@section('content')
<div class="space-y-6 w-full max-w-4xl mx-auto">

    <!-- Header & Breadcrumb -->
    <div class="flex items-center justify-between">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('edukasi.pengaduan.index') }}" class="hover:text-slate-600 transition-colors">Edukasi & Layanan Warga</a>
                <span>/</span>
                <a href="{{ route('edukasi.pengaduan.index') }}" class="hover:text-slate-600 transition-colors">Pengaduan Warga</a>
                <span>/</span>
                <span class="text-slate-600 font-semibold">#{{ $pengaduan->nomor_tiket }}</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Detail Pengaduan #{{ $pengaduan->nomor_tiket }}</h1>
            <p class="text-xs text-slate-500 mt-1">
                Dilaporkan pada {{ $pengaduan->created_at->format('d M Y, H:i') }} WIB
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

    @if (session('success'))
        <div class="p-4 rounded-xl bg-teal-50 border border-teal-200 text-teal-800 text-xs font-semibold flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 text-teal-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Kolom Kiri (2 Kolom): Detail Laporan Warga -->
        <div class="md:col-span-2 space-y-6">
            <div class="bg-white border border-slate-200/90 rounded-2xl p-6 shadow-xs space-y-5">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Kategori</span>
                        <span class="text-sm font-bold text-slate-800">{{ $pengaduan->kategori }}</span>
                    </div>

                    <div>
                        @if ($pengaduan->status === 'baru')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-sky-50 text-sky-700 border border-sky-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                                Baru
                            </span>
                        @elseif ($pengaduan->status === 'diproses')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                Diproses
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-teal-50 text-teal-700 border border-teal-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-teal-600"></span>
                                Selesai
                            </span>
                        @endif
                    </div>
                </div>

                <div>
                    <h3 class="text-base font-bold text-slate-900 leading-snug">{{ $pengaduan->judul }}</h3>
                    <p class="text-xs text-slate-600 leading-relaxed mt-2 whitespace-pre-line">{{ $pengaduan->isi }}</p>
                </div>

                @if ($pengaduan->foto_bukti_path)
                    <div class="pt-4 border-t border-slate-100">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Lampiran Bukti Fisik</span>
                        <a href="{{ asset('storage/' . $pengaduan->foto_bukti_path) }}" target="_blank" class="inline-block rounded-xl overflow-hidden border border-slate-200 max-w-xs hover:opacity-90 transition-opacity">
                            <img src="{{ asset('storage/' . $pengaduan->foto_bukti_path) }}" alt="Foto Bukti" class="w-full h-auto object-cover max-h-48">
                        </a>
                    </div>
                @endif
            </div>

            <!-- Form Tanggapi Petugas / Puskesmas -->
            <div class="bg-white border border-slate-200/90 rounded-2xl p-6 shadow-xs space-y-4">
                <h4 class="text-sm font-bold text-slate-900 tracking-tight">Tindak Lanjut & Tanggapan Petugas</h4>
                
                <form action="{{ route('edukasi.pengaduan.tanggapi', $pengaduan->id) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="status" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Pembaruan Status
                        </label>
                        <select name="status" id="status" required class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#005953]">
                            <option value="baru" {{ $pengaduan->status === 'baru' ? 'selected' : '' }}>Baru (Menunggu Tindak Lanjut)</option>
                            <option value="diproses" {{ $pengaduan->status === 'diproses' ? 'selected' : '' }}>Diproses (Sedang Ditangani)</option>
                            <option value="selesai" {{ $pengaduan->status === 'selesai' ? 'selected' : '' }}>Selesai (Telah Dituntaskan)</option>
                        </select>
                    </div>

                    <div>
                        <label for="catatan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Catatan Solusi / Balasan Petugas
                        </label>
                        <textarea name="catatan" id="catatan" rows="3" required placeholder="Tuliskan tindakan penanganan atau informasi balasan untuk warga..." class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#005953]">{{ old('catatan', $pengaduan->catatan) }}</textarea>
                    </div>

                    <div class="flex items-center justify-end">
                        <button type="submit" class="px-5 py-2.5 bg-[#005953] hover:bg-[#004742] text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                            Simpan Tanggapan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Kolom Kanan (1 Kolom): Profil Pelapor & Info Log -->
        <div class="space-y-6">
            <div class="bg-white border border-slate-200/90 rounded-2xl p-6 shadow-xs space-y-4">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider pb-2 border-b border-slate-100">
                    Informasi Pelapor
                </h4>

                @if ($pengaduan->is_anonymous)
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-600">
                        <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                        </svg>
                        <div>
                            <span class="font-bold text-xs block text-slate-800">Pelapor Anonim</span>
                            <span class="text-[11px] text-slate-400 block">Identitas warga dirahasiakan</span>
                        </div>
                    </div>
                @else
                    <div class="space-y-2 text-xs">
                        <div>
                            <span class="text-slate-400 block text-[11px]">Nama Warga</span>
                            <span class="font-bold text-slate-800">{{ $pengaduan->orangTua->name ?? 'Warga Terdaftar' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">NIK</span>
                            <span class="font-medium text-slate-700">{{ $pengaduan->orangTua->nik ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Alamat / Posyandu</span>
                            <span class="font-medium text-slate-700">{{ $pengaduan->orangTua->alamat ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Kontak</span>
                            <span class="font-medium text-slate-700">{{ $pengaduan->orangTua->kontak ?? '-' }}</span>
                        </div>
                    </div>
                @endif
            </div>

            <div class="bg-white border border-slate-200/90 rounded-2xl p-6 shadow-xs space-y-3">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider pb-2 border-b border-slate-100">
                    Petugas Penindak Lanjut
                </h4>
                <div class="text-xs">
                    <span class="text-slate-400 block text-[11px]">Ditangani Oleh</span>
                    <span class="font-bold text-slate-800">
                        {{ $pengaduan->adminPenanggap->name ?? 'Belum Ditugaskan' }}
                    </span>
                    @if ($pengaduan->adminPenanggap)
                        <span class="text-[11px] text-slate-400 block mt-0.5">
                            {{ $pengaduan->adminPenanggap->email ?? '' }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

    </div>

</div>
@endsection

