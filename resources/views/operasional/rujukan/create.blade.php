@extends('layouts.app')

@section('title', 'Buat Surat Rujukan Baru')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumbs -->
    <div class="flex items-center gap-2 text-xs font-medium text-slate-400">
        <a href="{{ route('jadwal.index') }}" class="hover:text-slate-600">Jadwal & Rujukan</a>
        <span>/</span>
        <a href="{{ route('rujukan.index') }}" class="hover:text-slate-600">Surat Rujukan</a>
        <span>/</span>
        <span class="text-slate-700 font-semibold">Buat Surat Baru</span>
    </div>

    <!-- Header Section -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <h1 class="text-2xl font-extrabold text-[#0B1C30] tracking-tight">Penerbitan Surat Rujukan Faskes</h1>
        <p class="text-xs text-[#6D7A77] mt-1">Nomor surat rujukan dan QR code verifikasi keaslian akan dibuat secara otomatis oleh sistem.</p>
    </div>

    <!-- Info Banner if selected balita has critical KMS -->
    @if($selectedBalita)
        @php
            $latestKms = $selectedBalita->pengukuranKms->first();
        @endphp
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="text-xs text-amber-900 space-y-1">
                <p class="font-bold">Rujukan Rekomendasi KMS: {{ $selectedBalita->name }}</p>
                <p>Pengukuran Terakhir: {{ $latestKms?->date?->format('d M Y') ?? '-' }} | BB: {{ $latestKms?->berat_badan ?? '-' }} kg, TB: {{ $latestKms?->tinggi_badan ?? '-' }} cm | Status Gizi: <span class="font-bold underline">{{ $latestKms?->status_gizi ?? 'Perlu Tindak Lanjut' }}</span></p>
            </div>
        </div>
    @endif

    <!-- Form Section -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
        <form method="POST" action="{{ route('rujukan.store') }}" class="space-y-6">
            @csrf

            <!-- Balita Selection -->
            <div>
                <label for="balita_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Pilih Balita <span class="text-rose-500">*</span>
                </label>
                <select name="balita_id" id="balita_id" required class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 font-medium text-slate-800 p-3 focus:ring-teal-500 focus:border-teal-500 @error('balita_id') border-rose-400 @enderror">
                    <option value="">-- Pilih Balita --</option>
                    @foreach($balitaList as $b)
                        <option value="{{ $b->id }}" {{ (old('balita_id', $selectedBalitaId) == $b->id) ? 'selected' : '' }}>
                            {{ $b->name }} (Ortu: {{ $b->orangTua?->name ?? 'Tidak ada data ortu' }}) - JK: {{ $b->jenis_kelamin }} {{ $b->pengukuranKms->isEmpty() ? '[Belum ada KMS]' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('balita_id')
                    <p class="text-[11px] text-rose-500 font-semibold mt-1">{{ $message }}</p>
                @enderror

                @if($selectedBalita)
                    @php
                        $latestKms = $selectedBalita->pengukuranKms->first();
                    @endphp
                    @if($latestKms)
                        <div class="mt-2.5 p-3 rounded-xl bg-teal-50/70 border border-teal-100 flex items-center justify-between text-xs">
                            <span class="text-teal-900 font-semibold">Pengukuran Terakhir: {{ $latestKms->date?->format('d M Y') }} (BB: {{ $latestKms->berat_badan }} kg, TB: {{ $latestKms->tinggi_badan }} cm)</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-100 text-teal-800">{{ $latestKms->status_gizi ?? 'Hasil KMS Terlampir' }}</span>
                        </div>
                    @else
                        <div class="mt-2.5 p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800">
                            <strong>Perhatian:</strong> Balita ini belum memiliki data pengukuran KMS. Surat rujukan hanya dapat diterbitkan setelah data pengukuran KMS tersedia.
                        </div>
                    @endif
                @endif
            </div>

            <!-- Faskes Tujuan -->
            <div>
                <label for="faskes_tujuan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Fasilitas Kesehatan Tujuan Rujukan <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="faskes_tujuan" id="faskes_tujuan" value="{{ old('faskes_tujuan') }}" required placeholder="Contoh: RSUD dr. Soetomo / Puskesmas Rujukan Kecamatan" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 font-medium text-slate-800 p-3 focus:ring-teal-500 focus:border-teal-500 @error('faskes_tujuan') border-rose-400 @enderror">
                @error('faskes_tujuan')
                    <p class="text-[11px] text-rose-500 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Alasan / Diagnosa Sementara -->
            <div>
                <label for="alasan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Alasan Rujukan / Diagnosa Awal / Riwayat Klinis <span class="text-rose-500">*</span>
                </label>
                <textarea name="alasan" id="alasan" rows="4" required placeholder="Jelaskan indikasi medis, hasil pemantauan KMS, penurunan berat badan berturut-turut, atau kondisi klinis balita yang melatarbelakangi rujukan..." class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 font-medium text-slate-800 p-3 focus:ring-teal-500 focus:border-teal-500 @error('alasan') border-rose-400 @enderror">{{ old('alasan', $selectedBalita ? 'Indikasi gizi buruk/gangguan pertumbuhan berdasarkan pemantauan KMS. Memerlukan evaluasi dokter spesialis anak di faskes lanjutan.' : '') }}</textarea>
                @error('alasan')
                    <p class="text-[11px] text-rose-500 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status Follow Up Awal -->
            <div>
                <label for="status_follow_up" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Status Tindak Lanjut
                </label>
                <select name="status_follow_up" id="status_follow_up" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 font-medium text-slate-800 p-3 focus:ring-teal-500 focus:border-teal-500">
                    <option value="belum" {{ old('status_follow_up') === 'belum' ? 'selected' : '' }}>Belum Diproses (Surat Baru)</option>
                    <option value="proses" {{ old('status_follow_up') === 'proses' ? 'selected' : '' }}>Dalam Proses Rujukan Faskes</option>
                    <option value="selesai" {{ old('status_follow_up') === 'selesai' ? 'selected' : '' }}>Selesai / Sudah Ditangani</option>
                </select>
                @error('status_follow_up')
                    <p class="text-[11px] text-rose-500 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('rujukan.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 text-xs font-bold hover:bg-slate-50 transition-colors">
                    Batal
                </a>
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#00685F] text-white text-xs font-bold hover:bg-[#00524B] shadow-xs transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Terbitkan Surat Rujukan</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
