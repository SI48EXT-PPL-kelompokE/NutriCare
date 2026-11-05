@extends('layouts.admin')

@section('title', $artikel->judul)

@section('content')
<div class="space-y-6 max-w-4xl">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('edukasi.artikel.index') }}" class="hover:text-slate-600 transition-colors">Artikel Edukasi</a>
                <span>/</span>
                <span class="text-slate-600 font-semibold">Detail Artikel</span>
            </nav>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">{{ $artikel->judul }}</h1>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('edukasi.artikel.edit', $artikel->id) }}" 
               class="inline-flex items-center gap-2 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"></path>
                </svg>
                <span>Edit</span>
            </a>
            <a href="{{ route('edukasi.artikel.index') }}" 
               class="inline-flex items-center gap-2 px-3.5 py-2 bg-[#008f7a] hover:bg-[#007a68] text-white text-xs font-semibold rounded-xl transition-colors">
                <span>Kembali ke Daftar</span>
            </a>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-6">
        <div class="flex flex-wrap items-center gap-3 pb-4 border-b border-slate-100 text-xs text-slate-500">
            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700">
                {{ $artikel->kategori }}
            </span>

            @if ($artikel->status === 'published')
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Status: Published
                </span>
            @else
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                    Status: Draft
                </span>
            @endif

            <span class="text-slate-300">•</span>
            <span>Penulis: <strong class="text-slate-700">{{ $artikel->penulis->name ?? 'Staf Fasilitas Kesehatan' }}</strong></span>

            <span class="text-slate-300">•</span>
            <span>Slug: <code class="text-[11px] bg-slate-50 px-2 py-0.5 rounded border border-slate-100 text-slate-600">{{ $artikel->url_slug }}</code></span>

            @if ($artikel->published_at)
                <span class="text-slate-300">•</span>
                <span>Terbit: {{ $artikel->published_at->format('d M Y, H:i') }} WIB</span>
            @endif
        </div>

        <article class="prose prose-slate max-w-none text-xs leading-relaxed text-slate-700">
            {!! $artikel->konten !!}
        </article>
    </div>

</div>
@endsection

