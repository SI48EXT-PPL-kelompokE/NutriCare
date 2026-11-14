@extends('layouts.admin')

@section('title', 'Sesi Konseling Gizi')

@section('content')
<div class="space-y-6">

    <!-- Header & navigasi -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <span>Layanan Konseling</span>
                <span>/</span>
                <span class="text-slate-600 font-semibold">Sesi Konseling Gizi</span>
            </nav>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Sesi Konseling Gizi Balita</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola pengajuan konsultasi gizi keluarga balita, penetapan jadwal nakes, dan pencatatan hasil konsultasi.</p>
        </div>

        <div class="flex items-center gap-2">
            @can('access-module', ['konseling', 'create'])
            <a href="{{ route('konseling.create') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#008f7a] hover:bg-[#007a68] text-white text-xs font-semibold rounded-xl transition-colors shadow-sm">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Ajukan Sesi Konseling</span>
            </a>
            @endcan
        </div>
    </div>

    @if (session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 text-xs text-emerald-800">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Kartu statistik status konseling -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <span class="text-[11px] font-semibold text-slate-500 block">Total Sesi</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold text-slate-900">{{ $stats['total'] }}</span>
                <span class="text-[11px] text-slate-400">Pengajuan</span>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <span class="text-[11px] font-semibold text-amber-700 block">Menunggu Jadwal</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold text-amber-600">{{ $stats['diajukan'] }}</span>
                <span class="text-[11px] px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 font-medium">Diajukan</span>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <span class="text-[11px] font-semibold text-sky-700 block">Terjadwal Nakes</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold text-sky-600">{{ $stats['dijadwalkan'] }}</span>
                <span class="text-[11px] px-2 py-0.5 rounded-full bg-sky-50 text-sky-700 font-medium">Aktif</span>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <span class="text-[11px] font-semibold text-emerald-700 block">Selesai Konseling</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold text-emerald-600">{{ $stats['selesai'] }}</span>
                <span class="text-[11px] px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-medium">Tuntas</span>
            </div>
        </div>
    </div>

    <!-- Filter & bilah pencarian -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
        <x-search-form results="search-results" :action="route('konseling.index')" id="filterKonselingForm" class="flex flex-col md:flex-row md:items-center gap-3">
            <x-search-input name="q" id="searchKonselingInput" :value="request('q')" placeholder="Cari nama orang tua, NIK, atau topik keluhan..." wrapper-class="w-full min-w-0 sm:min-w-56 sm:flex-1" />

            <div class="w-full md:w-44">
                <select 
                    name="status" 
                    id="filterStatus"
                    data-search-filter
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008f7a] focus:bg-white transition-all"
                >
                    <option value="">Semua Status</option>
                    <option value="diajukan" {{ request('status') == 'diajukan' ? 'selected' : '' }}>Diajukan (Menunggu)</option>
                    <option value="dijadwalkan" {{ request('status') == 'dijadwalkan' ? 'selected' : '' }}>Dijadwalkan</option>
                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>

            <div class="w-full md:w-56">
                <select 
                    name="topik" 
                    id="filterTopik"
                    data-search-filter
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008f7a] focus:bg-white transition-all"
                >
                    <option value="">Semua Topik Konseling</option>
                    @foreach ($topikList as $topikItem)
                        <option value="{{ $topikItem }}" {{ request('topik') == $topikItem ? 'selected' : '' }}>{{ $topikItem }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" id="btnFilterKonseling" class="px-4 py-2.5 bg-[#008f7a] hover:bg-[#007a68] text-white text-xs font-semibold rounded-xl transition-colors shadow-sm flex items-center gap-2">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span>Filter</span>
                </button>
                <a href="{{ route('konseling.index') }}" id="reset-konseling-search" data-search-sync
                    @if (!request()->anyFilled(['q', 'status', 'topik'])) hidden @endif
                    class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition-colors">
                    Reset
                </a>
            </div>
        </x-search-form>
    </div>

    <!-- Tabel daftar sesi konseling -->
    <x-table-card id="search-results" aria-live="polite">
        <x-data-table>
            <x-slot:head>
                <tr>
                    <th scope="col" class="py-3.5 px-4">Orang Tua & Balita</th>
                    <th scope="col" class="py-3.5 px-4">Topik Konsultasi</th>
                    <th scope="col" class="py-3.5 px-4">Nakes Pendamping</th>
                    <th scope="col" class="py-3.5 px-4">Jadwal Sesi</th>
                    <th scope="col" class="py-3.5 px-4">Status</th>
                    <th scope="col" class="py-3.5 px-4 text-center">Aksi</th>
                </tr>
            </x-slot:head>

            @forelse ($sesiList as $sesi)
                <tr class="hover:bg-slate-50/70 transition-colors">
                    <td class="py-3.5 px-4">
                        <span class="font-bold text-slate-900 block">{{ $sesi->orangTua->name ?? 'Orang Tua' }}</span>
                        <span class="text-[11px] text-slate-400 block font-normal">NIK: {{ $sesi->orangTua->nik ?? '-' }} • Kontak: {{ $sesi->orangTua->kontak ?? '-' }}</span>
                    </td>
                    <td class="py-3.5 px-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700">
                            {{ $sesi->topik }}
                        </span>
                    </td>
                    <td class="py-3.5 px-4">
                        @if ($sesi->nakes)
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-teal-100 text-[#008f7a] flex items-center justify-center font-bold text-[10px]">
                                    {{ substr($sesi->nakes->name, 0, 1) }}
                                </div>
                                <span class="text-slate-800 text-xs font-medium">{{ $sesi->nakes->name }}</span>
                            </div>
                        @else
                            <span class="text-slate-400 italic text-[11px]">Belum ditugaskan</span>
                        @endif
                    </td>
                    <td class="py-3.5 px-4 text-slate-600">
                        @if ($sesi->jadwal)
                            <span class="block font-medium text-slate-800">{{ $sesi->jadwal->format('d M Y') }}</span>
                            <span class="text-[11px] text-slate-400">{{ $sesi->jadwal->format('H:i') }} WIB</span>
                        @else
                            <span class="text-slate-400 italic text-[11px]">Menunggu penetapan</span>
                        @endif
                    </td>
                    <td class="py-3.5 px-4">
                        @if ($sesi->status === 'diajukan')
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                Diajukan
                            </span>
                        @elseif ($sesi->status === 'dijadwalkan')
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold bg-sky-50 text-sky-700 border border-sky-200">
                                Dijadwalkan
                            </span>
                        @elseif ($sesi->status === 'selesai')
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Selesai
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700">
                                {{ ucfirst($sesi->status) }}
                            </span>
                        @endif
                    </td>
                    <td class="py-3.5 px-4 text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            <a href="{{ route('konseling.show', $sesi->id) }}"
                               title="Lihat Detail & Tindakan"
                               class="p-1.5 text-slate-500 hover:text-[#008f7a] hover:bg-[#ecfdf5] rounded-lg transition-colors">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </a>
                            <form action="{{ route('konseling.destroy', $sesi->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data sesi konseling ini?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        title="Hapus Sesi"
                                        class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M3 6h18"></path>
                                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-12 text-center text-slate-400">
                        <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <p class="text-sm font-semibold text-slate-700">Tidak ada sesi konseling ditemukan</p>
                        <p class="text-xs text-slate-400 mt-1">Coba sesuaikan kata kunci pencarian atau bersihkan filter yang aktif.</p>
                    </td>
                </tr>
            @endforelse
        </x-data-table>

        <x-pagination :paginator="$sesiList" label="sesi konseling" />
    </x-table-card>

</div>
@endsection
