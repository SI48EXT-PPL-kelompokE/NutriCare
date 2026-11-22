@extends('layouts.app')

@section('title', 'Detail Surat Rujukan - ' . $rujukan->nomor_surat)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumbs -->
    <div class="flex items-center gap-2 text-xs font-medium text-slate-400">
        <a href="{{ route('jadwal.index') }}" class="hover:text-slate-600">Jadwal & Rujukan</a>
        <span>/</span>
        <a href="{{ route('rujukan.index') }}" class="hover:text-slate-600">Surat Rujukan</a>
        <span>/</span>
        <span class="text-slate-700 font-semibold">{{ $rujukan->nomor_surat }}</span>
    </div>

    <!-- Alert Success -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-semibold text-emerald-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="font-mono text-xs font-bold px-3 py-1 rounded-lg bg-teal-50 text-teal-800 border border-teal-200">{{ $rujukan->nomor_surat }}</span>
                @if($rujukan->status_follow_up === 'selesai')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Selesai</span>
                @elseif($rujukan->status_follow_up === 'proses')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">Sedang Diproses</span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">Belum Diproses</span>
                @endif
            </div>
            <h1 class="text-2xl font-extrabold text-[#0B1C30] tracking-tight mt-2">Surat Rujukan Pasien Balita</h1>
            <p class="text-xs text-[#6D7A77]">Diterbitkan oleh {{ $rujukan->nakes?->name ?? 'Tenaga Kesehatan' }} pada {{ $rujukan->created_at->format('d F Y, H:i') }} WIB.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('rujukan.pdf', $rujukan) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-rose-600 text-white text-xs font-bold hover:bg-rose-700 shadow-xs transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Cetak / Unduh PDF</span>
            </a>

            @can('update', $rujukan)
            <a href="{{ route('rujukan.edit', $rujukan) }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 text-xs font-bold hover:bg-slate-50 transition-colors">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <span>Edit</span>
            </a>
            @endcan

            @can('delete', $rujukan)
            <form method="POST" action="{{ route('rujukan.destroy', $rujukan) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus surat rujukan ini?')" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-rose-200 bg-rose-50 text-rose-700 text-xs font-bold hover:bg-rose-100 transition-colors">
                    <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <span>Hapus</span>
                </button>
            </form>
            @endcan
        </div>
    </div>

    <!-- Main Content Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Col 1 & 2: Informasi Klinis & Pasien -->
        <div class="md:col-span-2 space-y-6">
            <!-- Data Balita & Orang Tua -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                <h2 class="text-sm font-bold text-[#0B1C30] uppercase tracking-wider border-b border-slate-100 pb-3">Identitas Pasien Balita</h2>
                
                <div class="grid grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 font-medium">Nama Lengkap Balita</span>
                        <p class="font-bold text-slate-800 text-sm mt-0.5">{{ $rujukan->balita?->name }}</p>
                    </div>
                    <div>
                        <span class="text-slate-400 font-medium">Jenis Kelamin</span>
                        <p class="font-semibold text-slate-700 mt-0.5">{{ $rujukan->balita?->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                    </div>
                    <div>
                        <span class="text-slate-400 font-medium">Tanggal Lahir</span>
                        <p class="font-semibold text-slate-700 mt-0.5">{{ $rujukan->balita?->birth_date?->format('d F Y') ?? '-' }}</p>
                    </div>
                    <div>
                        <span class="text-slate-400 font-medium">Nama Orang Tua / Wali</span>
                        <p class="font-semibold text-slate-700 mt-0.5">{{ $rujukan->balita?->orangTua?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <span class="text-slate-400 font-medium">NIK Orang Tua</span>
                        <p class="font-semibold text-slate-700 mt-0.5">{{ $rujukan->balita?->orangTua?->nik ?? '-' }}</p>
                    </div>
                    <div>
                        <span class="text-slate-400 font-medium">Kontak Telepon</span>
                        <p class="font-semibold text-slate-700 mt-0.5">{{ $rujukan->balita?->orangTua?->phone ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Detail Rujukan Medis -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                <h2 class="text-sm font-bold text-[#0B1C30] uppercase tracking-wider border-b border-slate-100 pb-3">Informasi Fasilitas & Diagnosis</h2>

                <div class="space-y-4 text-xs">
                    <div>
                        <span class="text-slate-400 font-medium">Fasilitas Kesehatan Tujuan</span>
                        <p class="font-bold text-slate-800 text-sm mt-0.5">{{ $rujukan->faskes_tujuan }}</p>
                    </div>

                    <div>
                        <span class="text-slate-400 font-medium">Alasan / Diagnosis Klinis Rujukan</span>
                        <div class="mt-1 p-4 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 leading-relaxed font-medium whitespace-pre-line">
                            {{ $rujukan->alasan }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pengukuran KMS Terakhir jika ada -->
            @php
                $latestKms = $rujukan->balita?->pengukuranKms?->first();
            @endphp
            @if($latestKms)
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                <h2 class="text-sm font-bold text-[#0B1C30] uppercase tracking-wider border-b border-slate-100 pb-3">Data Antropometri KMS Terakhir</h2>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-[10px] text-slate-400 uppercase font-bold">Berat Badan</span>
                        <p class="text-sm font-bold text-slate-800 mt-0.5">{{ $latestKms->berat_badan }} kg</p>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-[10px] text-slate-400 uppercase font-bold">Tinggi Badan</span>
                        <p class="text-sm font-bold text-slate-800 mt-0.5">{{ $latestKms->tinggi_badan }} cm</p>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-[10px] text-slate-400 uppercase font-bold">LiLA</span>
                        <p class="text-sm font-bold text-slate-800 mt-0.5">{{ $latestKms->lingkar_lengan_atas ?? '-' }} cm</p>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-[10px] text-slate-400 uppercase font-bold">Status Gizi</span>
                        <p class="text-sm font-bold text-rose-600 mt-0.5">{{ $latestKms->status_gizi ?? 'Evaluasi Gizi' }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Col 3: QR Verifikasi Keaslian & Legalitas -->
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs text-center space-y-4">
                <h2 class="text-sm font-bold text-[#0B1C30]">QR Verifikasi Dokumen</h2>
                <p class="text-[11px] text-slate-500">Pindai kode QR untuk memverifikasi keaslian surat rujukan ini secara online.</p>

                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 inline-block shadow-2xs">
                    {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(160)->generate(route('rujukan.verify', ['code' => $rujukan->qr_verification_code])) !!}
                </div>

                <div class="space-y-1">
                    <span class="text-[10px] font-mono font-bold text-slate-400">KODE VERIFIKASI:</span>
                    <p class="font-mono text-[10px] text-slate-600 break-all bg-slate-100 px-2 py-1 rounded">{{ $rujukan->qr_verification_code }}</p>
                </div>

                <a href="{{ route('rujukan.verify', ['code' => $rujukan->qr_verification_code]) }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-teal-700 font-bold hover:underline">
                    <span>Uji Halaman Verifikasi Publik</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
            </div>

            <!-- Tenaga Medis Card -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs text-xs space-y-3">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tenaga Kesehatan Penanggung Jawab</span>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-teal-100 text-teal-800 font-bold flex items-center justify-center shrink-0">
                        {{ strtoupper(substr($rujukan->nakes?->name ?? 'N', 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-bold text-slate-800 text-sm">{{ $rujukan->nakes?->name ?? '-' }}</p>
                        <p class="text-[11px] text-slate-500">NIP: {{ $rujukan->nakes?->nip ?? 'Tenaga Medis Puskesmas' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
