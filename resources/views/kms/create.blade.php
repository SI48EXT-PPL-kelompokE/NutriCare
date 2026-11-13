@extends('layouts.app')

@section('title', 'Tambah Pengukuran')

@section('content')
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-xs font-medium text-slate-400 mb-6">
        <a href="{{ route('kms.index') }}" class="hover:text-slate-600">KMS Digital</a>
        <span>/</span>
        <a href="{{ route('kms.index') }}" class="hover:text-slate-600">Pengukuran</a>
        <span>/</span>
        <span class="text-slate-700 font-semibold">Tambah</span>
    </div>

    <!-- Header Section -->
    <div class="mb-6">
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Tambah Pengukuran Balita</h1>
        <p class="text-xs text-slate-500 mt-1">Catat hasil pengukuran berat badan, tinggi/panjang badan, dan LILA balita</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden max-w-2xl">
        <div class="p-4 border-b border-slate-200 flex items-center gap-3">
            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            <h2 class="text-sm font-bold text-slate-800">Form Pengukuran</h2>
        </div>

        <form action="{{ route('kms.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            @include('kms._form')
        </form>
    </div>
@endsection
