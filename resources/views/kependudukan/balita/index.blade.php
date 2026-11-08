@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- BREADCRUMB -->
    <nav class="flex items-center gap-2 text-xs text-slate-400 font-medium">
        <a href="{{ route('kependudukan.balita.index') }}" class="hover:text-slate-600 transition-colors">Data Balita</a>
        <span>&rsaquo;</span>
        <a href="{{ route('kependudukan.balita.index') }}" class="hover:text-slate-600 transition-colors">Data Balita & Kependudukan</a>
        <span>&rsaquo;</span>
        <span class="text-slate-700 font-semibold">Daftar Balita</span>
    </nav>

    <!-- HEADER TITLE & TOMBOL TAMBAH BALITA BARU -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Data Balita & Kependudukan</h2>
            <p class="text-xs text-slate-500 mt-1">Kelola data balita, orang tua, dan informasi identitas.</p>
        </div>

        <div>
            <button
                type="button"
                onclick="openCreateModal()"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#005953] hover:bg-[#004742] text-white rounded-xl text-xs font-semibold transition-colors shadow-sm"
            >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" x2="12" y1="5" y2="19"/>
                    <line x1="5" x2="19" y1="12" y2="12"/>
                </svg>
                <span>Tambah Balita Baru</span>
            </button>
        </div>
    </div>

    <!-- NAVIGATION TABS -->
    <div class="flex items-center gap-8 border-b border-slate-200">
        <!-- Tab 1: Daftar Balita (Active) -->
        <a href="{{ route('kependudukan.balita.index') }}" class="pb-3 text-sm font-bold text-[#008f7a] border-b-2 border-[#008f7a] flex items-center gap-2">
            <svg class="w-4 h-4 text-[#008f7a]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
            </svg>
            <span>Daftar Balita</span>
            <span class="px-2 py-0.5 text-xs font-bold bg-[#e6f7f5] text-[#008f7a] rounded-full">
                {{ $totalBalita }}
            </span>
        </a>

        <!-- Tab 2: Data Orang Tua / Ibu Hamil -->
        <a href="{{ route('kependudukan.orang-tua.index') }}" class="pb-3 text-sm font-semibold text-slate-500 hover:text-slate-800 transition-colors flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            <span>Data Orang Tua / Ibu Hamil</span>
            <span class="px-2 py-0.5 text-xs font-semibold bg-slate-100 text-slate-600 rounded-full">
                {{ $totalOrangTua }}
            </span>
        </a>
    </div>

    <!-- FILTER & SEARCH TOOLBAR -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
        <form method="GET" action="{{ route('kependudukan.balita.index') }}" class="flex flex-wrap items-center gap-3 w-full sm:w-auto flex-1">
            <!-- Search Input -->
            <div class="relative w-full sm:w-80">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.3-4.3"/>
                    </svg>
                </span>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari nama balita atau NIK..."
                    class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-lg text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#008f7a] transition-all"
                >
            </div>

            <!-- Filter Jenis Kelamin -->
            <select
                name="jenis_kelamin"
                onchange="this.form.submit()"
                class="px-3.5 py-2 bg-white border border-slate-200 rounded-lg text-xs text-slate-700 font-medium focus:outline-none focus:ring-2 focus:ring-[#008f7a]"
            >
                <option value="">Semua Jenis Kelamin</option>
                <option value="L" {{ request('jenis_kelamin') === 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                <option value="P" {{ request('jenis_kelamin') === 'P' ? 'selected' : '' }}>Perempuan (P)</option>
            </select>

            <!-- Filter Orang Tua -->
            <select
                name="orang_tua_id"
                onchange="this.form.submit()"
                class="px-3.5 py-2 bg-white border border-slate-200 rounded-lg text-xs text-slate-700 font-medium focus:outline-none focus:ring-2 focus:ring-[#008f7a] max-w-xs"
            >
                <option value="all">Semua Orang Tua</option>
                @foreach($orangTuaOptions as $ot)
                    <option value="{{ $ot->id }}" {{ (string) request('orang_tua_id') === (string) $ot->id ? 'selected' : '' }}>
                        {{ $ot->name }} ({{ $ot->nik }})
                    </option>
                @endforeach
            </select>
        </form>

        <!-- Right Controls: Ekspor & Reset -->
        <div class="flex items-center gap-2 self-end sm:self-auto">
            <button type="button" class="flex items-center gap-2 px-3.5 py-2 border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 transition-colors shadow-sm">
                <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="17 8 12 3 7 8"/>
                    <line x1="12" x2="12" y1="3" y2="15"/>
                </svg>
                <span>Ekspor</span>
            </button>

            <a
                href="{{ route('kependudukan.balita.index') }}"
                title="Reset Filter"
                class="p-2 border border-slate-200 rounded-lg text-slate-500 bg-white hover:bg-slate-50 hover:text-slate-700 transition-colors shadow-sm"
            >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                    <path d="M3 3v5h5"/>
                    <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/>
                    <path d="M16 21h5v-5"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- TABLE CONTAINER -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider bg-white">
                        <th class="py-4 px-6">Nama Balita</th>
                        <th class="py-4 px-6">Usia</th>
                        <th class="py-4 px-6">Nama Orang Tua</th>
                        <th class="py-4 px-6">NIK</th>
                        <th class="py-4 px-6">QR Identitas</th>
                        <th class="py-4 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($balitas as $balita)
                    @php
                        $balitaPayload = [
                            'id' => $balita->id,
                            'orang_tua_id' => $balita->orang_tua_id,
                            'name' => $balita->name,
                            'birth_date' => $balita->birth_date?->format('Y-m-d'),
                            'jenis_kelamin' => $balita->jenis_kelamin,
                            'qr_code' => $balita->qr_code,
                            'orang_tua_name' => $balita->orangTua?->name,
                            'orang_tua_nik' => $balita->orangTua?->nik,
                        ];
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <!-- Nama Balita + Jenis Kelamin -->
                        <td class="py-4 px-6 whitespace-nowrap">
                            <span class="font-bold text-slate-900 text-sm">{{ $balita->name }}</span>
                            <span class="text-[11px] text-slate-400 font-medium ml-1">
                                ({{ $balita->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }})
                            </span>
                        </td>

                        <!-- Usia Balita (KF-11: Hitung Otomatis dalam Bulan) -->
                        <td class="py-4 px-6 text-slate-700 font-medium whitespace-nowrap">
                            {{ \App\Http\Resources\BalitaResource::formatUsia($balita->birth_date) }}
                        </td>

                        <!-- Nama Orang Tua -->
                        <td class="py-4 px-6 text-slate-700 font-medium">
                            {{ $balita->orangTua?->name ?? '-' }}
                        </td>

                        <!-- NIK Orang Tua -->
                        <td class="py-4 px-6 font-mono text-slate-500 text-xs tracking-tight whitespace-nowrap">
                            {{ $balita->orangTua?->nik ?? '-' }}
                        </td>

                        <!-- QR Identitas Button (Tampilan Visual QR pada KF-12) -->
                        <td class="py-4 px-6 whitespace-nowrap">
                            <button
                                type="button"
                                disabled
                                aria-disabled="true"
                                title="Tampilan QR Code akan tersedia pada KF-12"
                                class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 bg-slate-50 text-slate-600 text-[11px] font-bold shadow-2xs disabled:opacity-75 disabled:cursor-not-allowed"
                            >
                                <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect width="5" height="5" x="3" y="3" rx="1"/>
                                    <rect width="5" height="5" x="16" y="3" rx="1"/>
                                    <rect width="5" height="5" x="3" y="16" rx="1"/>
                                    <path d="M21 16h-3a2 2 0 0 0-2 2v3"/>
                                    <path d="M21 21v.01"/>
                                    <path d="M12 7v3a2 2 0 0 1-2 2H7"/>
                                    <path d="M3 12h.01"/>
                                    <path d="M12 3h.01"/>
                                    <path d="M12 16v.01"/>
                                    <path d="M16 12h1"/>
                                    <path d="M21 12v.01"/>
                                    <path d="M12 21v-1"/>
                                </svg>
                                <span>QR</span>
                            </button>
                        </td>

                        <!-- Aksi: Lihat KMS & Menu Dropdown Tiga Titik -->
                        <td class="py-4 px-6 text-right whitespace-nowrap">
                            <div class="inline-flex items-center justify-end gap-2">
                                <button
                                    type="button"
                                    disabled
                                    aria-disabled="true"
                                    title="Integrasi KMS belum tersedia"
                                    class="text-[#008f7a]/60 font-bold text-xs disabled:cursor-not-allowed"
                                >
                                    Lihat KMS
                                </button>

                                <div class="relative inline-block text-left">
                                    <button
                                        type="button"
                                        id="action-trigger-{{ $balita->id }}"
                                        onclick="toggleActionMenu(event, {{ $balita->id }})"
                                        aria-haspopup="true"
                                        aria-expanded="false"
                                        aria-controls="action-menu-{{ $balita->id }}"
                                        aria-label="Menu aksi untuk {{ $balita->name }}"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors focus:outline-none focus:ring-2 focus:ring-[#008f7a]"
                                    >
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="5" r="1"/>
                                            <circle cx="12" cy="12" r="1"/>
                                            <circle cx="12" cy="19" r="1"/>
                                        </svg>
                                    </button>

                                    <div
                                        id="action-menu-{{ $balita->id }}"
                                        role="menu"
                                        aria-labelledby="action-trigger-{{ $balita->id }}"
                                        class="action-dropdown-menu hidden fixed z-50 w-44 rounded-xl bg-white border border-slate-200 shadow-lg py-1.5 text-left"
                                    >
                                        <a
                                            href="{{ route('kependudukan.balita.show', $balita->id) }}"
                                            role="menuitem"
                                            class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-[#008f7a] transition-colors"
                                        >
                                            <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                            <span>Lihat Detail</span>
                                        </a>

                                        <button
                                            type="button"
                                            role="menuitem"
                                            onclick='closeAllActionMenus(); openEditModal(@json($balitaPayload))'
                                            class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-sky-600 transition-colors text-left"
                                        >
                                            <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M12 20h9"/>
                                                <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                            </svg>
                                            <span>Edit Data Balita</span>
                                        </button>

                                        <div class="my-1 border-t border-slate-100"></div>

                                        <button
                                            type="button"
                                            role="menuitem"
                                            onclick="closeAllActionMenus(); confirmDelete({{ $balita->id }}, '{{ addslashes($balita->name) }}')"
                                            class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition-colors text-left"
                                        >
                                            <svg class="w-3.5 h-3.5 text-rose-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M3 6h18"/>
                                                <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                                                <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                            </svg>
                                            <span>Hapus Data Balita</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">
                            <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="8" x2="16" y1="12" y2="12"/>
                            </svg>
                            <p class="font-medium text-sm text-slate-600">Tidak ada data balita ditemukan.</p>
                            <p class="text-xs text-slate-400 mt-1">Silakan sesuaikan filter pencarian atau tambahkan data balita baru.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINATION BAR -->
        <div class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 bg-white">
            <p class="text-xs text-slate-500">
                Menampilkan
                <span class="font-semibold text-slate-800">{{ $balitas->firstItem() ?? 0 }}–{{ $balitas->lastItem() ?? 0 }}</span>
                dari
                <span class="font-semibold text-slate-800">{{ $balitas->total() }}</span>
                balita
            </p>

            @if ($balitas->hasPages())
            <div class="flex items-center gap-1">
                {{-- Previous Page Link --}}
                @if ($balitas->onFirstPage())
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-300 border border-slate-100 text-xs cursor-not-allowed">&lsaquo;</span>
                @else
                    <a href="{{ $balitas->previousPageUrl() }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-600 border border-slate-200 hover:bg-slate-50 text-xs transition-colors">&lsaquo;</a>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($balitas->getUrlRange(1, $balitas->lastPage()) as $page => $url)
                    @if ($page == $balitas->currentPage())
                        <span class="w-8 h-8 rounded-lg flex items-center justify-center bg-[#005953] text-white font-bold text-xs shadow-sm">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-600 border border-slate-200 hover:bg-slate-50 text-xs font-semibold transition-colors">{{ $page }}</a>
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($balitas->hasMorePages())
                    <a href="{{ $balitas->nextPageUrl() }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-600 border border-slate-200 hover:bg-slate-50 text-xs transition-colors">&rsaquo;</a>
                @else
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-300 border border-slate-100 text-xs cursor-not-allowed">&rsaquo;</span>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL FORM TAMBAH / EDIT DATA BALITA                           -->
<!-- ============================================================== -->
<div id="modalBalitaForm" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-[460px] w-full p-7 shadow-2xl border border-slate-100 transform transition-all relative">

        <!-- Modal Header -->
        <div class="flex items-center justify-between pb-4">
            <h3 id="modalTitle" class="text-xl font-bold text-slate-900 tracking-tight">Tambah Data Balita</h3>
            <button type="button" onclick="closeModal()" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg transition-colors">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" x2="6" y1="6" y2="18"/>
                    <line x1="6" x2="18" y1="6" y2="18"/>
                </svg>
            </button>
        </div>

        <!-- Inline Error Box untuk Pesan Validasi Backend -->
        <div id="formErrorBox" class="hidden mb-4 bg-[#fff1f2] border border-rose-200 rounded-xl px-4 py-3 text-xs text-rose-800 font-medium">
            <p class="font-bold mb-1">Periksa kembali data yang dimasukkan:</p>
            <ul id="formErrorList" class="list-disc list-inside space-y-0.5"></ul>
        </div>

        <!-- Form Body -->
        <form id="balitaForm" onsubmit="submitBalitaForm(event)" class="space-y-4">
            <input type="hidden" id="formId">

            <!-- FIELD 1: Nama Balita -->
            <div>
                <label for="formName" class="block text-[13px] font-bold text-slate-800 mb-1.5">
                    Nama Lengkap Balita <span class="text-rose-500">*</span>
                </label>
                <input
                    type="text"
                    id="formName"
                    maxlength="255"
                    required
                    placeholder="e.g. Ahmad Rayhan"
                    class="w-full px-4 py-2.5 bg-[#f8fafc] border border-slate-200 rounded-xl text-sm font-medium text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#6ba299] focus:border-transparent transition-all"
                >
            </div>

            <!-- FIELD 2: Tanggal Lahir -->
            <div>
                <label for="formBirthDate" class="block text-[13px] font-bold text-slate-800 mb-1.5">
                    Tanggal Lahir <span class="text-rose-500">*</span>
                </label>
                <input
                    type="date"
                    id="formBirthDate"
                    max="{{ date('Y-m-d') }}"
                    required
                    class="w-full px-4 py-2.5 bg-[#f8fafc] border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#6ba299] focus:border-transparent transition-all"
                >
            </div>

            <!-- FIELD 3: Jenis Kelamin -->
            <div>
                <label for="formJenisKelamin" class="block text-[13px] font-bold text-slate-800 mb-1.5">
                    Jenis Kelamin <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <select
                        id="formJenisKelamin"
                        required
                        class="w-full appearance-none px-4 py-2.5 pr-10 bg-[#f8fafc] border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#6ba299] focus:border-transparent transition-all cursor-pointer"
                    >
                        <option value="" disabled selected>Pilih Jenis Kelamin...</option>
                        <option value="L">Laki-laki (L)</option>
                        <option value="P">Perempuan (P)</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m6 9 6 6 6-6"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- FIELD 4: Orang Tua Terkait -->
            <div>
                <label for="formOrangTuaId" class="block text-[13px] font-bold text-slate-800 mb-1.5">
                    Orang Tua Terkait <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <select
                        id="formOrangTuaId"
                        required
                        class="w-full appearance-none px-4 py-2.5 pr-10 bg-[#f8fafc] border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#6ba299] focus:border-transparent transition-all cursor-pointer"
                    >
                        <option value="" disabled selected>Pilih Orang Tua Terdaftar...</option>
                        @foreach($orangTuaOptions as $ot)
                            <option value="{{ $ot->id }}">{{ $ot->name }} — NIK: {{ $ot->nik }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m6 9 6 6 6-6"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- INFO BANNER QR IDENTIFIER -->
            <div class="bg-[#eff6ff] rounded-xl px-4 py-3 flex items-center gap-3 text-xs text-slate-700 font-medium">
                <svg class="w-4 h-4 text-[#008f7a] flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <rect width="5" height="5" x="3" y="3" rx="1"/>
                    <rect width="5" height="5" x="16" y="3" rx="1"/>
                    <rect width="5" height="5" x="3" y="16" rx="1"/>
                    <path d="M21 16h-3a2 2 0 0 0-2 2v3"/>
                </svg>
                <span id="qrInfoText">Identifier QR unik akan dibuat secara otomatis oleh sistem.</span>
            </div>

            <!-- MODAL FOOTER -->
            <div class="pt-5 mt-6 border-t border-slate-100 flex items-center justify-end gap-5">
                <button
                    type="button"
                    onclick="closeModal()"
                    class="text-sm font-semibold text-slate-700 hover:text-slate-900 transition-colors"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    id="submitBtn"
                    class="px-5 py-2.5 bg-[#6ba299] hover:bg-[#5a9289] text-white font-semibold text-sm rounded-xl shadow-sm transition-all flex items-center gap-2"
                >
                    <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    <span>Simpan Data</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const modal = document.getElementById('modalBalitaForm');
    const modalTitle = document.getElementById('modalTitle');
    const form = document.getElementById('balitaForm');
    const formErrorBox = document.getElementById('formErrorBox');
    const formErrorList = document.getElementById('formErrorList');
    const qrInfoText = document.getElementById('qrInfoText');

    function closeAllActionMenus() {
        document.querySelectorAll('.action-dropdown-menu').forEach(menu => {
            menu.classList.add('hidden');
        });
        document.querySelectorAll('[id^="action-trigger-"]').forEach(btn => {
            btn.setAttribute('aria-expanded', 'false');
        });
    }

    function toggleActionMenu(event, id) {
        event.stopPropagation();
        const menu = document.getElementById(`action-menu-${id}`);
        const trigger = document.getElementById(`action-trigger-${id}`);
        if (!menu || !trigger) return;

        const isHidden = menu.classList.contains('hidden');
        closeAllActionMenus();

        if (isHidden) {
            menu.classList.remove('hidden');
            trigger.setAttribute('aria-expanded', 'true');

            const rect = trigger.getBoundingClientRect();
            const menuWidth = menu.offsetWidth || 176;
            const menuHeight = menu.offsetHeight || 132;

            let left = rect.right - menuWidth;
            if (left < 8) left = 8;

            let top = rect.bottom + 6;
            if (top + menuHeight + 8 > window.innerHeight && rect.top - menuHeight - 6 > 8) {
                top = rect.top - menuHeight - 6;
            }

            menu.style.left = `${left}px`;
            menu.style.top = `${top}px`;
        }
    }

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.action-dropdown-menu') && !e.target.closest('[id^="action-trigger-"]')) {
            closeAllActionMenus();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAllActionMenus();
            if (!modal.classList.contains('hidden')) {
                closeModal();
            }
        }
    });

    window.addEventListener('scroll', closeAllActionMenus, true);
    window.addEventListener('resize', closeAllActionMenus);

    function hideErrors() {
        formErrorBox.classList.add('hidden');
        formErrorList.innerHTML = '';
    }

    function showErrors(errors) {
        formErrorList.innerHTML = '';
        Object.values(errors).flat().forEach(msg => {
            const li = document.createElement('li');
            li.textContent = msg;
            formErrorList.appendChild(li);
        });
        formErrorBox.classList.remove('hidden');
    }

    function openCreateModal() {
        document.getElementById('formId').value = '';
        form.reset();
        hideErrors();
        modalTitle.innerText = 'Tambah Data Balita';
        qrInfoText.innerText = 'Identifier QR unik akan dibuat secara otomatis oleh sistem.';
        modal.classList.remove('hidden');
    }

    function openEditModal(data) {
        document.getElementById('formId').value = data.id;
        document.getElementById('formName').value = data.name;
        document.getElementById('formBirthDate').value = data.birth_date;
        document.getElementById('formJenisKelamin').value = data.jenis_kelamin;
        document.getElementById('formOrangTuaId').value = data.orang_tua_id;

        hideErrors();
        modalTitle.innerText = 'Edit Data Balita';
        qrInfoText.innerText = `Identifier QR (${data.qr_code}) tetap dipertahankan dan tidak berubah.`;
        modal.classList.remove('hidden');
    }

    function closeModal() {
        modal.classList.add('hidden');
    }

    async function submitBalitaForm(e) {
        e.preventDefault();
        hideErrors();

        const id = document.getElementById('formId').value;
        const isEdit = !!id;
        const submitBtn = document.getElementById('submitBtn');

        submitBtn.disabled = true;
        submitBtn.innerHTML = `<span>Menyimpan...</span>`;

        const payload = {
            name: document.getElementById('formName').value.trim(),
            birth_date: document.getElementById('formBirthDate').value,
            jenis_kelamin: document.getElementById('formJenisKelamin').value,
            orang_tua_id: parseInt(document.getElementById('formOrangTuaId').value, 10)
        };

        const url = isEdit ? `/api/balita/${id}` : '/api/balita';
        const method = isEdit ? 'PUT' : 'POST';

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (!response.ok) {
                if (data.errors) {
                    showErrors(data.errors);
                }
                let errorMsg = data.message || 'Terjadi kesalahan validasi.';
                if (data.errors) {
                    errorMsg = Object.values(data.errors).flat().join('<br>');
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menyimpan',
                    html: errorMsg
                });
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

        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Kesalahan Sistem',
                text: error.message
            });
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = `
                <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <span>Simpan Data</span>
            `;
        }
    }

    async function confirmDelete(id, name) {
        const result = await Swal.fire({
            title: 'Hapus Data Balita?',
            text: `Apakah Anda yakin ingin menghapus data balita "${name}"? Tindakan ini tidak dapat dibatalkan.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch(`/api/balita/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (!response.ok) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Tidak Dapat Dihapus',
                        text: data.message || 'Data balita tidak dapat dihapus karena masih digunakan oleh layanan lain.'
                    });
                    return;
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Terhapus!',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });

            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Sistem',
                    text: error.message
                });
            }
        }
    }
</script>
@endpush

