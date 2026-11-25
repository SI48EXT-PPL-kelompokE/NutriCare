@extends('layouts.admin')

@section('title', 'Ubah Pengaduan #' . $pengaduan->nomor_tiket . ' - Edukasi & Layanan Warga')

@section('content')
<div class="space-y-6 w-full max-w-4xl mx-auto">

    <div class="flex items-center justify-between">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('edukasi.pengaduan.index') }}" class="hover:text-slate-600 transition-colors">Edukasi & Layanan Warga</a>
                <span>/</span>
                <a href="{{ route('edukasi.pengaduan.index') }}" class="hover:text-slate-600 transition-colors">Pengaduan Warga</a>
                <span>/</span>
                <span class="text-slate-600 font-semibold">Edit #{{ $pengaduan->nomor_tiket }}</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Ubah Pengaduan #{{ $pengaduan->nomor_tiket }}</h1>
            <p class="text-xs text-slate-500 mt-1">
                Perbarui rincian pengaduan selama status laporan masih Baru.
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
        <form action="{{ route('edukasi.pengaduan.update', $pengaduan->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('edukasi.pengaduan._form', ['pengaduan' => $pengaduan, 'submitLabel' => 'Simpan Perubahan'])
        </form>
    </div>

</div>
@endsection
