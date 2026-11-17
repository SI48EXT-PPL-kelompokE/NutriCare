@extends('layouts.app')

@section('title', 'Riwayat Imunisasi')

@section('content')
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-xs font-medium text-slate-400 mb-6">
        <a href="#" class="hover:text-slate-600">KMS Digital</a>
        <span>/</span>
        <span class="text-slate-700 font-semibold">Riwayat Imunisasi</span>
    </div>

    <!-- Header Section -->
    <div class="mb-6">
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Riwayat Imunisasi</h1>
        <p class="text-xs text-slate-500 mt-1">Pencatatan pemberian imunisasi balita sesuai jadwal nasional</p>
    </div>

    <!-- Tab Navigasi KMS Digital -->
    <div class="flex items-center gap-2 border-b border-slate-200 text-xs font-semibold text-slate-500 pb-px mb-6">
        <span class="inline-flex items-center gap-2 px-4 py-2.5 rounded-t-xl text-slate-400 cursor-not-allowed">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
            </svg>
            <span>Pengukuran</span>
        </span>

        <a href="{{ route('imunisasi.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-t-xl text-teal-700 font-bold border-b-2 border-teal-600 bg-white">
            <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <span>Imunisasi (Aktif)</span>
        </a>

        <span class="inline-flex items-center gap-2 px-4 py-2.5 rounded-t-xl text-slate-400 cursor-not-allowed">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
            </svg>
            <span>Vitamin & Suplemen</span>
        </span>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 mb-6">
        <x-search-form results="search-results" :action="route('imunisasi.index')" class="flex flex-col gap-3 lg:flex-row">
            <x-search-input :value="request('search')" placeholder="Cari nama balita, jenis vaksin, atau no. batch..." wrapper-class="min-w-0 flex-1" />
            @if (request()->filled('balita_id'))
                <input type="hidden" name="balita_id" value="{{ request('balita_id') }}">
            @endif

            <!-- Filter Jenis Vaksin -->
            <div class="w-full lg:w-48">
                <select name="jenis_vaksin" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <option value="">Semua Jenis Vaksin</option>
                    @foreach($jenisVaksinList as $vaksin)
                        <option value="{{ $vaksin }}" {{ request('jenis_vaksin') == $vaksin ? 'selected' : '' }}>
                            {{ $vaksin }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Tanggal Dari -->
            <div class="w-full lg:w-40">
                <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" 
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
            </div>

            <!-- Tanggal Sampai -->
            <div class="w-full lg:w-40">
                <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" 
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
            </div>

            <!-- Tombol Filter & Tambah -->
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-semibold rounded-lg hover:bg-slate-200 transition-colors">
                    Filter
                </button>
                @can('access-module', ['imunisasi', 'create'])
                <a href="{{ route('imunisasi.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-teal-600 text-white text-sm font-semibold rounded-lg hover:bg-teal-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Imunisasi
                </a>
                @endcan
            </div>
        </x-search-form>
    </div>

    <!-- Tabel Riwayat Imunisasi -->
    <x-table-card id="search-results" aria-live="polite">
        <div class="p-4 border-b border-slate-200 flex items-center gap-3">
            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <h2 class="text-sm font-bold text-slate-800">Daftar Riwayat Imunisasi</h2>
        </div>

        @if ($imunisasi->isNotEmpty())
            <x-data-table>
                <x-slot:head>
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left">Balita</th>
                        <th scope="col" class="px-4 py-3 text-left">Jenis Vaksin</th>
                        <th scope="col" class="px-4 py-3 text-left">Tanggal Pemberian</th>
                        <th scope="col" class="px-4 py-3 text-left">No. Batch</th>
                        <th scope="col" class="px-4 py-3 text-left">Petugas</th>
                        <th scope="col" class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </x-slot:head>

                @foreach($imunisasi as $item)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3">
                            <div class="font-semibold text-sm text-slate-800">{{ $item->balita->name }}</div>
                            <div class="text-xs text-slate-500">{{ $item->balita->orangTua->name ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-700">{{ $item->jenis_vaksin }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700">
                            {{ \Carbon\Carbon::parse($item->tanggal)->isoFormat('D MMM YYYY') }}
                        </td>
                        <td class="px-4 py-3">
                            @if($item->no_batch)
                                <x-code-chip>{{ $item->no_batch }}</x-code-chip>
                            @else
                                <span class="text-xs text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-700">{{ $item->petugas->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('imunisasi.show', $item) }}"
                                   class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-teal-700 hover:bg-teal-50 rounded transition-colors"
                                   title="Lihat Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Lihat
                                </a>
                                @can('access-module', ['imunisasi', 'edit'])
                                <a href="{{ route('imunisasi.edit', $item) }}"
                                   class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-blue-700 hover:bg-blue-50 rounded transition-colors"
                                   title="Ubah">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Ubah
                                </a>
                                @endcan
                                @can('access-module', ['imunisasi', 'delete'])
                                <form action="{{ route('imunisasi.destroy', $item) }}" method="POST"
                                      onsubmit="return confirm('Yakin ingin menghapus catatan imunisasi {{ $item->jenis_vaksin }} untuk {{ $item->balita->name }}?')"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-rose-700 hover:bg-rose-50 rounded transition-colors"
                                            title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Hapus
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-data-table>

        @else
            <x-empty-state 
                title="Belum ada catatan imunisasi"
                description="Silakan tambahkan catatan imunisasi balita untuk memulai">
                @can('access-module', ['imunisasi', 'create'])
                <a href="{{ route('imunisasi.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-teal-600 text-white text-sm font-semibold rounded-lg hover:bg-teal-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Catatan Pertama
                </a>
                @endcan
            </x-empty-state>
        @endif
        <x-pagination :paginator="$imunisasi" label="catatan imunisasi" />
    </x-table-card>
@endsection
