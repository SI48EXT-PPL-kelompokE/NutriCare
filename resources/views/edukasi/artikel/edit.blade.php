@extends('layouts.admin')

@section('title', 'Edit Artikel Edukasi')

@section('content')
<div class="space-y-6 max-w-4xl">

    <div>
        <nav class="flex items-center gap-2 text-xs text-slate-400 mb-1">
            <a href="{{ route('edukasi.artikel.index') }}" class="hover:text-slate-600 transition-colors">Artikel Edukasi</a>
            <span>/</span>
            <span class="text-slate-600 font-semibold">Perbarui Artikel</span>
        </nav>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Edit Artikel Edukasi</h1>
        <p class="text-xs text-slate-500 mt-0.5">Perbarui isi materi panduan gizi dan publikasi kesehatan balita.</p>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold space-y-1">
            <p class="font-bold">Terjadi kesalahan pada formulir:</p>
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('edukasi.artikel.update', $artikel->id) }}" method="POST" id="artikelEditForm" class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-5">
        @csrf
        @method('PUT')

        <div class="space-y-1.5">
            <label for="judul" class="block text-xs font-bold text-slate-700">Judul Artikel <span class="text-rose-500">*</span></label>
            <input 
                type="text" 
                name="judul" 
                id="judul"
                value="{{ old('judul', $artikel->judul) }}"
                required
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008f7a] focus:bg-white transition-all"
            >
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1.5">
                <label for="kategori" class="block text-xs font-bold text-slate-700">Kategori Materi <span class="text-rose-500">*</span></label>
                <select 
                    name="kategori" 
                    id="kategori"
                    required
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008f7a] focus:bg-white transition-all"
                >
                    <option value="">Pilih Kategori</option>
                    @foreach ($kategoriList as $kat)
                        <option value="{{ $kat }}" {{ old('kategori', $artikel->kategori) == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-1.5">
                <label for="status" class="block text-xs font-bold text-slate-700">Status Publikasi <span class="text-rose-500">*</span></label>
                <select 
                    name="status" 
                    id="status"
                    required
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008f7a] focus:bg-white transition-all"
                >
                    <option value="draft" {{ old('status', $artikel->status) == 'draft' ? 'selected' : '' }}>Draft (Simpan sebagai draf)</option>
                    <option value="published" {{ old('status', $artikel->status) == 'published' ? 'selected' : '' }}>Published (Terbit untuk publik)</option>
                </select>
            </div>
        </div>

        <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-700">Isi Konten Artikel <span class="text-rose-500">*</span></label>
            <input type="hidden" name="konten" id="kontenInput" value="{{ old('konten', $artikel->konten) }}">
            <div id="editorContainer" class="bg-white rounded-xl border border-slate-200 min-h-[260px] text-xs">
                {!! old('konten', $artikel->konten) !!}
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('edukasi.artikel.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition-colors">
                Batal
            </a>
            <button type="submit" class="px-5 py-2.5 bg-[#008f7a] hover:bg-[#007a68] text-white text-xs font-semibold rounded-xl shadow-sm transition-colors">
                Simpan Perubahan
            </button>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const quill = new Quill('#editorContainer', {
            theme: 'snow',
            placeholder: 'Tuliskan panduan edukasi kesehatan di sini...',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['blockquote', 'link'],
                    ['clean']
                ]
            }
        });

        const form = document.getElementById('artikelEditForm');
        const kontenInput = document.getElementById('kontenInput');

        form.addEventListener('submit', function (e) {
            const html = quill.root.innerHTML;
            if (quill.getText().trim().length === 0) {
                e.preventDefault();
                alert('Isi konten artikel tidak boleh kosong.');
                return;
            }
            kontenInput.value = html;
        });
    });
</script>
@endpush

