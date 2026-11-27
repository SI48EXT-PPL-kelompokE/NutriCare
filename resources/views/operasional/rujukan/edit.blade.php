@extends('layouts.app')

@section('title', 'Perbarui Surat Rujukan - ' . $rujukan->nomor_surat)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumbs -->
    <div class="flex items-center gap-2 text-xs font-medium text-slate-400">
        <a href="{{ route('jadwal.index') }}" class="hover:text-slate-600">Jadwal & Rujukan</a>
        <span>/</span>
        <a href="{{ route('rujukan.index') }}" class="hover:text-slate-600">Surat Rujukan</a>
        <span>/</span>
        <span class="text-slate-700 font-semibold">Edit Surat</span>
    </div>

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="font-mono text-xs font-bold px-2.5 py-1 rounded-lg bg-teal-50 text-teal-700 border border-teal-200">{{ $rujukan->nomor_surat }}</span>
                <h1 class="text-xl font-extrabold text-[#0B1C30]">Perbarui Data Surat Rujukan</h1>
            </div>
            <p class="text-xs text-[#6D7A77] mt-1">Perbarui fasilitas tujuan, diagnosis atau status tindak lanjut pasien rujukan.</p>
        </div>

        <a href="{{ route('rujukan.show', $rujukan) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors">
            Kembali ke Detail
        </a>
    </div>

    <!-- Readonly Information Card -->
    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
        <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Balita Pasien</span>
            <p class="font-bold text-slate-800 text-sm mt-0.5">{{ $rujukan->balita?->name }}</p>
            <p class="text-slate-500">JK: {{ $rujukan->balita?->jenis_kelamin }} | Lahir: {{ $rujukan->balita?->birth_date?->format('d M Y') }}</p>
        </div>
        <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Orang Tua / Wali</span>
            <p class="font-semibold text-slate-800 mt-0.5">{{ $rujukan->balita?->orangTua?->name ?? '-' }}</p>
            <p class="text-slate-500">NIK: {{ $rujukan->balita?->orangTua?->nik ?? '-' }}</p>
        </div>
        <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tenaga Kesehatan Penerbit</span>
            <p class="font-semibold text-slate-800 mt-0.5">{{ $rujukan->nakes?->name ?? '-' }}</p>
            <p class="text-slate-500">Tanggal: {{ $rujukan->created_at->format('d M Y, H:i') }} WIB</p>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
        <form method="POST" action="{{ route('rujukan.update', $rujukan) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Faskes Tujuan -->
            <div>
                <label for="faskes_tujuan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Fasilitas Kesehatan Tujuan Rujukan <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="faskes_tujuan" id="faskes_tujuan" value="{{ old('faskes_tujuan', $rujukan->faskes_tujuan) }}" required class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 font-medium text-slate-800 p-3 focus:ring-teal-500 focus:border-teal-500 @error('faskes_tujuan') border-rose-400 @enderror">
                @error('faskes_tujuan')
                    <p class="text-[11px] text-rose-500 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Alasan / Diagnosa Sementara -->
            <div>
                <label for="alasan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Alasan Rujukan / Diagnosa Medis <span class="text-rose-500">*</span>
                </label>
                <textarea name="alasan" id="alasan" rows="4" required class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 font-medium text-slate-800 p-3 focus:ring-teal-500 focus:border-teal-500 @error('alasan') border-rose-400 @enderror">{{ old('alasan', $rujukan->alasan) }}</textarea>
                @error('alasan')
                    <p class="text-[11px] text-rose-500 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status Follow Up -->
            <div>
                <label for="status_follow_up" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Status Tindak Lanjut Pasien <span class="text-rose-500">*</span>
                </label>
                <select name="status_follow_up" id="status_follow_up" required class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 font-medium text-slate-800 p-3 focus:ring-teal-500 focus:border-teal-500">
                    <option value="belum" {{ old('status_follow_up', $rujukan->status_follow_up) === 'belum' ? 'selected' : '' }}>Belum Diproses (Surat Diterbitkan)</option>
                    <option value="proses" {{ old('status_follow_up', $rujukan->status_follow_up) === 'proses' ? 'selected' : '' }}>Dalam Proses Pemeriksaan di Faskes Tujuan</option>
                    <option value="selesai" {{ old('status_follow_up', $rujukan->status_follow_up) === 'selesai' ? 'selected' : '' }}>Selesai / Sudah Ditangani Faskes Rujukan</option>
                </select>
                @error('status_follow_up')
                    <p class="text-[11px] text-rose-500 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('rujukan.show', $rujukan) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 text-xs font-bold hover:bg-slate-50 transition-colors">
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
