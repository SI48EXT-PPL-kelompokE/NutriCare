@extends('layouts.admin')

@section('title', 'Detail Balita')

@section('content')
<div class="space-y-6 max-w-4xl">

    <!-- BREADCRUMB -->
    <nav class="flex items-center gap-2 text-xs text-slate-400 font-medium">
        <a href="{{ route('kependudukan.balita.index') }}" class="hover:text-slate-600 transition-colors">Data Balita</a>
        <span>&rsaquo;</span>
        <a href="{{ route('kependudukan.balita.index') }}" class="hover:text-slate-600 transition-colors">Daftar Balita</a>
        <span>&rsaquo;</span>
        <span class="text-slate-700 font-semibold">Detail Data Balita</span>
    </nav>

    <!-- HEADER & ACTIONS -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $balita->name }}</h1>
            <p class="text-xs text-slate-500 mt-1">Informasi identitas dasar balita dan data orang tua terkait.</p>
        </div>

        <div class="flex items-center gap-3">
            <a
                href="{{ route('kependudukan.balita.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold transition-colors shadow-sm"
            >
                <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
                <span>Kembali</span>
            </a>

            @can('access-module', ['balita', 'edit'])
            <button
                type="button"
                onclick="openEditModal()"
                class="inline-flex items-center gap-2 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-semibold transition-colors shadow-sm"
            >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 20h9"/>
                    <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                </svg>
                <span>Edit Data Balita</span>
            </button>
            @endcan
        </div>
    </div>

    <!-- DETAIL CARDS -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Card 1: Identitas Balita -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">Identitas Balita</h3>
                <span class="px-2.5 py-1 rounded-md border border-slate-200 bg-slate-50 text-slate-700 text-xs font-mono font-semibold">
                    {{ $balita->qr_code }}
                </span>
            </div>

            <dl class="space-y-3 text-xs">
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <dt class="text-slate-500 font-medium">Nama Lengkap</dt>
                    <dd class="text-slate-900 font-bold">{{ $balita->name }}</dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <dt class="text-slate-500 font-medium">Tanggal Lahir</dt>
                    <dd class="text-slate-900 font-semibold">{{ $balita->birth_date?->format('Y-m-d') }}</dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <dt class="text-slate-500 font-medium">Usia Balita</dt>
                    <dd class="text-teal-700 font-bold">{{ \App\Http\Resources\BalitaResource::formatUsia($balita->birth_date) }}</dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <dt class="text-slate-500 font-medium">Jenis Kelamin</dt>
                    <dd class="text-slate-900 font-semibold">
                        {{ $balita->jenis_kelamin === 'L' ? 'Laki-laki (L)' : 'Perempuan (P)' }}
                    </dd>
                </div>
                <div class="flex justify-between py-1.5">
                    <dt class="text-slate-500 font-medium">Identifier QR</dt>
                    <dd class="text-slate-700 font-mono font-semibold">{{ $balita->qr_code }}</dd>
                </div>
            </dl>
        </div>

        <!-- Card 2: Data Orang Tua Terkait -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">Orang Tua Terkait</h3>
                <span class="px-2.5 py-0.5 rounded-full bg-teal-50 text-teal-700 text-[11px] font-bold">
                    Terhubung
                </span>
            </div>

            @if($balita->orangTua)
            <dl class="space-y-3 text-xs">
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <dt class="text-slate-500 font-medium">Nama Orang Tua</dt>
                    <dd class="text-slate-900 font-bold">{{ $balita->orangTua->name }}</dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <dt class="text-slate-500 font-medium">NIK Orang Tua</dt>
                    <dd class="text-slate-700 font-mono font-semibold">{{ $balita->orangTua->nik }}</dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <dt class="text-slate-500 font-medium">Kontak / WhatsApp</dt>
                    <dd class="text-slate-700 font-mono">{{ $balita->orangTua->kontak }}</dd>
                </div>
                <div class="py-1.5">
                    <dt class="text-slate-500 font-medium mb-1">Alamat Domisili</dt>
                    <dd class="text-slate-700">{{ $balita->orangTua->alamat }}</dd>
                </div>
            </dl>
            @else
            <p class="text-xs text-slate-400">Data orang tua tidak ditemukan.</p>
            @endif
        </div>
    </div>
</div>

<!-- MODAL EDIT DATA BALITA -->
<div id="modalEditBalita" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/50 backdrop-blur-sm items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-[460px] w-full p-7 shadow-2xl border border-slate-100 relative">
        <div class="flex items-center justify-between pb-4">
            <h3 class="text-xl font-bold text-slate-900 tracking-tight">Edit Data Balita</h3>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" x2="6" y1="6" y2="18"/>
                    <line x1="6" x2="18" y1="6" y2="18"/>
                </svg>
            </button>
        </div>

        <form id="editBalitaForm" onsubmit="submitEditForm(event)" class="space-y-4">
            <div>
                <label class="block text-[13px] font-bold text-slate-800 mb-1.5">Nama Lengkap Balita <span class="text-rose-500">*</span></label>
                <input type="text" id="editName" value="{{ $balita->name }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
            </div>

            <div>
                <label class="block text-[13px] font-bold text-slate-800 mb-1.5">Tanggal Lahir <span class="text-rose-500">*</span></label>
                <input type="date" id="editBirthDate" value="{{ $balita->birth_date?->format('Y-m-d') }}" max="{{ date('Y-m-d') }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
            </div>

            <div>
                <label class="block text-[13px] font-bold text-slate-800 mb-1.5">Jenis Kelamin <span class="text-rose-500">*</span></label>
                <select id="editJenisKelamin" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <option value="L" {{ $balita->jenis_kelamin === 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                    <option value="P" {{ $balita->jenis_kelamin === 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                </select>
            </div>

            <div>
                <label class="block text-[13px] font-bold text-slate-800 mb-1.5">Orang Tua Terkait <span class="text-rose-500">*</span></label>
                <select id="editOrangTuaId" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                    @foreach($orangTuaOptions as $ot)
                        <option value="{{ $ot->id }}" {{ $balita->orang_tua_id === $ot->id ? 'selected' : '' }}>
                            {{ $ot->name }} — NIK: {{ $ot->nik }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="pt-5 mt-6 border-t border-slate-100 flex items-center justify-end gap-5">
                <button type="button" onclick="closeEditModal()" class="text-sm font-semibold text-slate-700 hover:text-slate-900">Batal</button>
                <button type="submit" id="editSubmitBtn" class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-semibold text-sm rounded-xl shadow-sm">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const editModal = document.getElementById('modalEditBalita');

    function openEditModal() {
        editModal.classList.replace('hidden', 'flex');
    }

    function closeEditModal() {
        editModal.classList.replace('flex', 'hidden');
    }

    async function submitEditForm(e) {
        e.preventDefault();
        const btn = document.getElementById('editSubmitBtn');
        btn.disabled = true;

        const payload = {
            name: document.getElementById('editName').value.trim(),
            birth_date: document.getElementById('editBirthDate').value,
            jenis_kelamin: document.getElementById('editJenisKelamin').value,
            orang_tua_id: parseInt(document.getElementById('editOrangTuaId').value, 10)
        };

        try {
            const response = await fetch('/api/balita/{{ $balita->id }}', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(payload)
            });

            if (response.status === 401) {

                window.location.assign(@js(route('login')));

                return;

            }

            const data = await response.json();

            if (!response.ok) {
                let errorMsg = data.message || 'Gagal memperbarui data.';
                if (data.errors) {
                    errorMsg = Object.values(data.errors).flat().join('<br>');
                }
                Swal.fire({ icon: 'error', title: 'Gagal Menyimpan', html: errorMsg });
                return;
            }

            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location.reload();
            });
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Kesalahan Sistem', text: err.message });
        } finally {
            btn.disabled = false;
        }
    }
</script>
@endpush

