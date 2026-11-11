@extends('layouts.app')

@section('title', 'Detail Catatan Imunisasi')

@section('content')
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-xs font-medium text-slate-400 mb-6">
        <a href="#" class="hover:text-slate-600">KMS Digital</a>
        <span>/</span>
        <a href="{{ route('imunisasi.index') }}" class="hover:text-slate-600">Riwayat Imunisasi</a>
        <span>/</span>
        <span class="text-slate-700 font-semibold">Detail Catatan</span>
    </div>

    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Detail Catatan Imunisasi</h1>
        <p class="text-xs text-slate-500 mt-1">Informasi lengkap pemberian imunisasi</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Detail Imunisasi -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="p-4 border-b border-slate-200 bg-slate-50">
                <h2 class="text-sm font-bold text-slate-800">Informasi Imunisasi</h2>
            </div>
            <div class="p-6 space-y-4">
                <!-- Jenis Vaksin -->
                <div>
                    <div class="text-xs font-semibold text-slate-600 mb-1">Jenis Vaksin</div>
                    <div class="text-lg font-bold text-slate-900">{{ $imunisasi->jenis_vaksin }}</div>
                </div>

                <!-- Tanggal Pemberian -->
                <div>
                    <div class="text-xs font-semibold text-slate-600 mb-1">Tanggal Pemberian</div>
                    <div class="text-sm text-slate-800">{{ $imunisasi->tanggal->isoFormat('dddd, D MMMM YYYY') }}</div>
                </div>

                <!-- Nomor Batch -->
                <div>
                    <div class="text-xs font-semibold text-slate-600 mb-1">Nomor Batch</div>
                    @if($imunisasi->no_batch)
                        <x-code-chip>{{ $imunisasi->no_batch }}</x-code-chip>
                    @else
                        <span class="text-sm text-slate-400">Tidak dicatat</span>
                    @endif
                </div>

                <!-- Petugas -->
                <div>
                    <div class="text-xs font-semibold text-slate-600 mb-1">Petugas Pencatat</div>
                    <div class="text-sm text-slate-800">{{ $imunisasi->petugas->name ?? 'Tidak diketahui' }}</div>
                    <div class="text-xs text-slate-500 mt-0.5">
                        Dicatat pada {{ $imunisasi->created_at->isoFormat('D MMMM YYYY, HH:mm') }}
                    </div>
                </div>

                @if($imunisasi->updated_at != $imunisasi->created_at)
                    <div class="text-xs text-slate-500 pt-2 border-t border-slate-100">
                        Terakhir diperbarui {{ $imunisasi->updated_at->isoFormat('D MMMM YYYY, HH:mm') }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Ringkasan Balita -->
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="p-4 border-b border-slate-200 bg-slate-50">
                <h2 class="text-sm font-bold text-slate-800">Data Balita</h2>
            </div>
            <div class="p-6 space-y-3">
                <div>
                    <div class="text-xs font-semibold text-slate-600 mb-1">Nama Balita</div>
                    <div class="text-sm font-bold text-slate-900">{{ $imunisasi->balita->name }}</div>
                </div>

                <div>
                    <div class="text-xs font-semibold text-slate-600 mb-1">Orang Tua</div>
                    <div class="text-sm text-slate-800">{{ $imunisasi->balita->orangTua->name ?? '-' }}</div>
                </div>

                <div>
                    <div class="text-xs font-semibold text-slate-600 mb-1">Tanggal Lahir</div>
                    <div class="text-sm text-slate-800">{{ $imunisasi->balita->birth_date->isoFormat('D MMMM YYYY') }}</div>
                </div>

                <div>
                    <div class="text-xs font-semibold text-slate-600 mb-1">Usia Saat Imunisasi</div>
                    <div class="text-sm text-slate-800">{{ $usiaBulan }} bulan</div>
                </div>

                <div>
                    <div class="text-xs font-semibold text-slate-600 mb-1">Jenis Kelamin</div>
                    <div class="text-sm text-slate-800">{{ $imunisasi->balita->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tombol Aksi -->
    <div class="mt-6 flex items-center gap-3">
        <a href="{{ route('imunisasi.edit', $imunisasi) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Ubah Data
        </a>
        <form action="{{ route('imunisasi.destroy', $imunisasi) }}" method="POST" 
              onsubmit="return confirm('Yakin ingin menghapus catatan imunisasi ini?')"
              class="inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 text-white text-sm font-semibold rounded-lg hover:bg-rose-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Hapus
            </button>
        </form>
        <a href="{{ route('imunisasi.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-semibold rounded-lg hover:bg-slate-200 transition-colors">
            Kembali ke Daftar
        </a>
    </div>
@endsection
