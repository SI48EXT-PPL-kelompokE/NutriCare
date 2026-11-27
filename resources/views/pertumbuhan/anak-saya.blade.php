@extends('layouts.app')

@section('title', 'KMS Anak Saya')

@section('content')
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-xs font-medium text-slate-400 mb-6">
        <a href="#" class="hover:text-slate-600">KMS Digital</a>
        <span>/</span>
        <span class="text-slate-700 font-semibold">KMS Anak Saya</span>
    </div>

    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">KMS Anak Saya</h1>
        <p class="text-xs text-slate-500 mt-1">Pilih anak untuk melihat grafik pertumbuhannya</p>
    </div>

    @if ($kartu->isEmpty())
        <div class="bg-white rounded-xl border border-slate-200">
            <x-empty-state title="Belum ada data anak terdaftar." description="Hubungi petugas posyandu untuk mendaftarkan data anak Anda." />
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach ($kartu as $item)
                @php($anak = $item['balita'])
                <div class="bg-white rounded-xl border border-slate-200 p-5 flex flex-col">
                    <div class="flex items-start gap-3">
                        <div class="w-12 h-12 shrink-0 rounded-full bg-teal-100 text-teal-800 font-extrabold flex items-center justify-center">
                            {{ mb_strtoupper(mb_substr((string) $anak->name, 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <h2 class="text-sm font-bold text-slate-900 truncate">{{ $anak->name }}</h2>
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ $anak->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }} &bull; {{ $item['usia_label'] }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <span class="text-xs text-slate-400 uppercase tracking-wide block">Status gizi terakhir</span>
                        <div class="mt-1.5 flex items-center gap-2">
                            @if ($item['status'])
                                <x-status-pill :status="$item['status']" />
                            @else
                                <span class="text-sm text-slate-400">Belum ada penilaian</span>
                            @endif
                            @if (! $item['lengkap'] && $item['status'])
                                <span class="text-xs text-sky-700 font-semibold">Penilaian sebagian</span>
                            @endif
                        </div>
                    </div>

                    <a href="{{ route('balita.pertumbuhan', $anak) }}"
                       class="mt-5 inline-flex items-center justify-center gap-2 px-4 py-2 bg-teal-600 text-white text-sm font-semibold rounded-lg hover:bg-teal-700 transition-colors min-h-[44px]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M22 12h-4l-3 9-6-18-3 9H2"/>
                        </svg>
                        Lihat Grafik
                    </a>
                </div>
            @endforeach
        </div>
    @endif
@endsection
