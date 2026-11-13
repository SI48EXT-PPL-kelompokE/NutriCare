@extends('layouts.app')

@section('title', 'Ubah Catatan Imunisasi')

@section('content')
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-xs font-medium text-slate-400 mb-6">
        <a href="#" class="hover:text-slate-600">KMS Digital</a>
        <span>/</span>
        <a href="{{ route('imunisasi.index') }}" class="hover:text-slate-600">Riwayat Imunisasi</a>
        <span>/</span>
        <span class="text-slate-700 font-semibold">Ubah Catatan</span>
    </div>

    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Ubah Catatan Imunisasi</h1>
        <p class="text-xs text-slate-500 mt-1">Perbarui data pemberian imunisasi balita</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-xl border border-slate-200 p-6 max-w-2xl">
        <form action="{{ route('imunisasi.update', $imunisasi) }}" method="POST">
            @csrf
            @method('PUT')
            @include('imunisasi._form')
        </form>
    </div>
@endsection
