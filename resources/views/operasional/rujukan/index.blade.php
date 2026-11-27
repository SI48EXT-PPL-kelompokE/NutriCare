@extends('layouts.app')

@section('title', 'Surat Rujukan & Rekomendasi Klinis')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumbs -->
    <div class="flex items-center gap-2 text-xs font-medium text-slate-400">
        <a href="{{ route('jadwal.index') }}" class="hover:text-slate-600">Jadwal & Rujukan</a>
        <span>/</span>
        <span class="text-slate-700 font-semibold">Surat Rujukan</span>
    </div>

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-2xl font-extrabold text-[#0B1C30] tracking-tight">Surat Rujukan & Pemantauan Lanjutan</h1>
            <p class="text-xs text-[#6D7A77] mt-1">Penerbitan surat rujukan faskes lanjutan, cetak PDF ber-QR verifikasi, dan rekomendasi balita berisiko berdasarkan data KMS.</p>
        </div>

        @can('create', App\Models\SuratRujukan::class)
        <div class="flex items-center gap-3">
            <a href="{{ route('rujukan.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#00685F] text-white text-xs font-bold hover:bg-[#00524B] shadow-xs transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Buat Surat Rujukan</span>
            </a>
        </div>
        @endcan
    </div>

    <!-- Subnavigation Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 text-xs font-semibold text-slate-500 pb-px">
        <a href="{{ route('jadwal.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-t-xl hover:text-slate-700 hover:bg-slate-50 transition-colors">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span>Kalender & Agenda</span>
        </a>

        <a href="{{ route('presensi.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-t-xl hover:text-slate-700 hover:bg-slate-50 transition-colors">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Presensi & Antrean Hari Ini</span>
        </a>

        <a href="{{ route('reservasi.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-t-xl hover:text-slate-700 hover:bg-slate-50 transition-colors">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span>Reservasi Kunjungan</span>
        </a>

        <a href="{{ route('rujukan.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-t-xl text-[#00685F] font-bold border-b-2 border-[#00685F] bg-white transition-colors">
            <svg class="w-4 h-4 text-[#00685F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span>Surat Rujukan</span>
        </a>
    </div>

    <!-- Alert Success -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-semibold text-emerald-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- SECTION 1: Rekomendasi Rujukan Balita Berdasarkan Hasil KMS -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-amber-100 text-amber-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </span>
                    <h2 class="text-base font-bold text-[#0B1C30]">Saran Rujukan Berdasarkan Hasil KMS</h2>
                </div>
                <p class="text-xs text-slate-500 mt-1">Daftar balita terindikasi gizi buruk atau z-score kritis pada pemantauan KMS terakhir yang belum memiliki rujukan aktif.</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $saranRujukan->count() > 0 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600' }}">
                {{ $saranRujukan->count() }} Balita Perlu Ditindaklanjuti
            </span>
        </div>

        @if($saranRujukan->isEmpty())
            @if(($totalEvaluatedKms ?? 0) === 0)
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="text-sm font-bold text-slate-700">Belum Ada Hasil Evaluasi KMS</h3>
                    <p class="text-xs text-slate-400 mt-1">Data pengukuran antropometri balita belum memiliki evaluasi status gizi atau z-score (menunggu kalkulasi otomatis modul KMS).</p>
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-emerald-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="text-sm font-bold text-slate-700">Semua Balita Terpantau Baik</h3>
                    <p class="text-xs text-slate-400 mt-1">Tidak ada balita dengan status gizi kritis yang membutuhkan rujukan mendesak saat ini.</p>
                </div>
            @endif
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-3">Nama Balita</th>
                            <th class="py-3 px-3">Orang Tua</th>
                            <th class="py-3 px-3">Tanggal Ukur</th>
                            <th class="py-3 px-3">BB / TB</th>
                            <th class="py-3 px-3">Status Gizi (KMS)</th>
                            <th class="py-3 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @foreach($saranRujukan as $balita)
                            @php
                                $lastKms = $balita->pengukuranKms->first();
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-800">{{ $balita->name }}</div>
                                    <div class="text-[10px] text-slate-400">JK: {{ $balita->jenis_kelamin }}</div>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-semibold text-slate-700">{{ $balita->orangTua?->name ?? '-' }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $balita->orangTua?->phone ?? '-' }}</div>
                                </td>
                                <td class="py-3 px-3 text-slate-600">
                                    {{ $lastKms?->date?->format('d M Y') ?? '-' }}
                                </td>
                                <td class="py-3 px-3">
                                    <span class="font-bold text-slate-800">{{ $lastKms?->berat_badan ?? '-' }} kg</span> /
                                    <span class="text-slate-600">{{ $lastKms?->tinggi_badan ?? '-' }} cm</span>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        {{ $lastKms?->status_gizi ?? 'Indikasi Gizi Buruk' }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right">
                                    @can('create', App\Models\SuratRujukan::class)
                                    <a href="{{ route('rujukan.create', ['balita_id' => $balita->id]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-teal-600 text-white font-bold text-[11px] hover:bg-teal-700 shadow-2xs transition-colors">
                                        <span>Rujuk Sekarang</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                        </svg>
                                    </a>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- SECTION 2: Riwayat Dokumen Surat Rujukan -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <h2 class="text-base font-bold text-[#0B1C30]">Daftar & Riwayat Surat Rujukan</h2>
                <p class="text-xs text-slate-500 mt-1">Dokumen resmi rujukan faskes tujuan yang telah diterbitkan.</p>
            </div>

            <!-- Filter & Search Form -->
            <form method="GET" action="{{ route('rujukan.index') }}" class="flex flex-wrap items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. Surat / Nama Balita..." class="text-xs rounded-xl border-slate-200 bg-slate-50 py-2 px-3 focus:ring-teal-500 focus:border-teal-500 w-52 font-medium text-slate-800">

                <select name="status" onchange="this.form.submit()" class="text-xs rounded-xl border-slate-200 bg-slate-50 font-medium text-slate-800 py-2 px-3 focus:ring-teal-500 focus:border-teal-500">
                    <option value="">Semua Status</option>
                    <option value="belum" {{ request('status') === 'belum' ? 'selected' : '' }}>Belum Diproses</option>
                    <option value="proses" {{ request('status') === 'proses' ? 'selected' : '' }}>Dalam Proses</option>
                    <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>

                <button type="submit" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>

                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('rujukan.index') }}" class="text-xs text-rose-600 hover:underline font-semibold ml-1">Reset</a>
                @endif
            </form>
        </div>

        @if($rujukanList->isEmpty())
            <div class="text-center py-12">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="text-sm font-bold text-slate-700">Belum Ada Surat Rujukan</h3>
                <p class="text-xs text-slate-400 mt-1">Gunakan tombol "Buat Surat Rujukan" di atas untuk menerbitkan dokumen rujukan baru.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-3">No. Surat</th>
                            <th class="py-3 px-3">Balita / Orang Tua</th>
                            <th class="py-3 px-3">Faskes Tujuan</th>
                            <th class="py-3 px-3">Nakes Penerbit</th>
                            <th class="py-3 px-3">Status</th>
                            <th class="py-3 px-3">Tanggal Terbit</th>
                            <th class="py-3 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @foreach($rujukanList as $surat)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3 px-3">
                                    <div class="font-mono font-bold text-[#00685F]">{{ $surat->nomor_surat }}</div>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-800">{{ $surat->balita?->name ?? '-' }}</div>
                                    <div class="text-[10px] text-slate-400">Ortu: {{ $surat->balita?->orangTua?->name ?? '-' }}</div>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-semibold text-slate-700">{{ $surat->faskes_tujuan }}</div>
                                    <div class="text-[10px] text-slate-400 line-clamp-1">{{ Str::limit($surat->alasan, 40) }}</div>
                                </td>
                                <td class="py-3 px-3 text-slate-600">
                                    {{ $surat->nakes?->name ?? '-' }}
                                </td>
                                <td class="py-3 px-3">
                                    @if($surat->status_follow_up === 'selesai')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            Selesai
                                        </span>
                                    @elseif($surat->status_follow_up === 'proses')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">
                                            Diproses
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                            Belum Diproses
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-slate-500">
                                    {{ $surat->created_at->format('d M Y') }}
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="{{ route('rujukan.show', $surat) }}" title="Lihat Detail" class="p-1.5 rounded-lg text-slate-600 hover:text-teal-600 hover:bg-teal-50 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>

                                        <a href="{{ route('rujukan.pdf', $surat) }}" target="_blank" title="Unduh PDF" class="p-1.5 rounded-lg text-slate-600 hover:text-rose-600 hover:bg-rose-50 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                            </svg>
                                        </a>

                                        @can('update', $surat)
                                        <a href="{{ route('rujukan.edit', $surat) }}" title="Edit Rujukan" class="p-1.5 rounded-lg text-slate-600 hover:text-amber-600 hover:bg-amber-50 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        @endcan

                                        @can('delete', $surat)
                                        <form method="POST" action="{{ route('rujukan.destroy', $surat) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus surat rujukan ini?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus Rujukan" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $rujukanList->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
