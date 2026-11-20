@extends('layouts.app')

@section('title', 'Grafik Pertumbuhan - ' . $grafik['balita']['nama'])

@section('content')
    @php
        $b = $grafik['balita'];
        $ringkasan = $grafik['ringkasan'];
        $adaData = $grafik['jumlah_pengukuran'] > 0;
        $chartPayload = [
            'balita' => [
                'nama' => $b['nama'],
                'jenis_kelamin_label' => $b['jenis_kelamin_label'],
            ],
            'jumlah_pengukuran' => $grafik['jumlah_pengukuran'],
            'indikator' => $grafik['indikator'],
        ];
        $tabLabels = ['bb_u' => 'Berat / Umur', 'tb_u' => 'Tinggi / Umur', 'bb_tb' => 'BB / TB'];
    @endphp

    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-xs font-medium text-slate-400 mb-6">
        <a href="{{ route('kms.index') }}" class="hover:text-slate-600">KMS Digital</a>
        <span>/</span>
        <span class="text-slate-700 font-semibold">{{ $b['nama'] }}</span>
    </div>

    <!-- Header -->
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Grafik Pertumbuhan</h1>
            <p class="text-xs text-slate-500 mt-1">Pemantauan pertumbuhan {{ $b['nama'] }} dari waktu ke waktu</p>
        </div>
        @can('access-module', ['kms', 'create'])
        <a href="{{ route('kms.create', ['balita_id' => $b['id']]) }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-teal-600 text-white text-sm font-semibold rounded-lg hover:bg-teal-700 transition-colors min-h-[44px]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Input Pengukuran
        </a>
        @endcan
    </div>

    <!-- Kartu Identitas Balita -->
    <div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">
        <div class="flex items-start gap-4">
            <div class="w-14 h-14 shrink-0 rounded-full bg-teal-100 text-teal-800 font-extrabold flex items-center justify-center text-lg">
                {{ $b['inisial'] }}
            </div>
            <div class="min-w-0 flex-1">
                <h2 class="text-lg font-bold text-slate-900">{{ $b['nama'] }}</h2>
                <div class="mt-2 grid grid-cols-2 sm:grid-cols-4 gap-x-4 gap-y-2 text-xs">
                    <div>
                        <span class="text-slate-400 uppercase tracking-wide block">Usia</span>
                        <span class="font-semibold text-slate-700">{{ $b['usia_sekarang_label'] ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 uppercase tracking-wide block">Jenis Kelamin</span>
                        <span class="font-semibold text-slate-700">{{ $b['jenis_kelamin_label'] }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 uppercase tracking-wide block">Tanggal Lahir</span>
                        <span class="font-semibold text-slate-700">{{ $b['tanggal_lahir_label'] ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 uppercase tracking-wide block">Orang Tua</span>
                        <span class="font-semibold text-slate-700">{{ $b['orang_tua'] ?? '-' }}</span>
                    </div>
                    @can('view-balita-identity')
                    <div>
                        <span class="text-slate-400 uppercase tracking-wide block">NIK Orang Tua</span>
                        <span class="font-semibold text-slate-700">{{ $balita->orangTua->nik ?? '-' }}</span>
                    </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    @if (! $adaData)
        <!-- Keadaan kosong: belum ada pengukuran -->
        <div class="bg-white rounded-xl border border-slate-200">
            <div class="p-4 border-b border-slate-200">
                <h2 class="text-sm font-bold text-slate-800">Kurva Pertumbuhan Balita (Standar Antropometri WHO)</h2>
            </div>
            <x-empty-state title="Belum ada data pengukuran." description="Grafik akan muncul setelah data pengukuran pertama dicatat.">
                @can('access-module', ['kms', 'create'])
                <a href="{{ route('kms.create', ['balita_id' => $b['id']]) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-teal-600 text-white text-sm font-semibold rounded-lg hover:bg-teal-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Input Pengukuran
                </a>
                @endcan
            </x-empty-state>
        </div>
    @else
        <!-- Kartu Grafik -->
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden mb-6"
             data-growth-chart
             data-chart="{{ json_encode($chartPayload) }}"
             x-data="{ aktif: 'bb_u', pilih(k) { this.aktif = k; if (window.growthChart) { window.growthChart.pilih(k); } } }">
            <div class="p-4 border-b border-slate-200 flex items-center gap-3">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M22 12h-4l-3 9-6-18-3 9H2"/>
                </svg>
                <h2 class="text-sm font-bold text-slate-800">Kurva Pertumbuhan Balita (Standar Antropometri WHO)</h2>
            </div>

            <!-- Tab indikator -->
            <div class="px-4 pt-4 border-b border-slate-200">
                <div role="tablist" aria-label="Pilih indikator pertumbuhan" class="flex flex-wrap gap-1">
                    @foreach ($tabLabels as $kode => $label)
                        <button type="button"
                                role="tab"
                                id="tab-{{ $kode }}"
                                aria-controls="panel-{{ $kode }}"
                                :aria-selected="aktif === '{{ $kode }}' ? 'true' : 'false'"
                                @click="pilih('{{ $kode }}')"
                                :class="aktif === '{{ $kode }}'
                                    ? 'text-teal-700 border-teal-600 bg-teal-50/60'
                                    : 'text-slate-500 border-transparent hover:bg-slate-50'"
                                class="px-4 py-2.5 text-xs font-bold rounded-t-xl border-b-2 transition-colors min-h-[44px]">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="p-6 space-y-6">
                <!-- Kartu Ringkasan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                    <!-- BB/U terkini -->
                    <div class="rounded-xl border border-slate-200 p-4">
                        <span class="text-xs text-slate-500 uppercase tracking-wide">BB/U terkini</span>
                        <div class="mt-2 text-2xl font-bold text-slate-800">
                            @if ($ringkasan['bb_u_terkini']['nilai_label'] !== null)
                                {{ $ringkasan['bb_u_terkini']['nilai_label'] }}
                                <span class="text-sm font-normal text-slate-500">kg</span>
                            @else
                                <span class="text-sm font-semibold text-slate-400">Belum tersedia</span>
                            @endif
                        </div>
                        <div class="mt-1 text-xs text-slate-500">
                            {{ $ringkasan['bb_u_terkini']['z_label'] ?? 'Z-score: -' }}
                        </div>
                        <div class="mt-1 text-xs font-semibold text-slate-600">
                            {{ $ringkasan['bb_u_terkini']['kategori'] ?? '-' }}
                        </div>
                    </div>

                    <!-- TB/U terkini -->
                    <div class="rounded-xl border border-slate-200 p-4">
                        <span class="text-xs text-slate-500 uppercase tracking-wide">TB/U terkini</span>
                        <div class="mt-2 text-2xl font-bold text-slate-800">
                            @if ($ringkasan['tb_u_terkini']['nilai_label'] !== null)
                                {{ $ringkasan['tb_u_terkini']['nilai_label'] }}
                                <span class="text-sm font-normal text-slate-500">cm</span>
                            @else
                                <span class="text-sm font-semibold text-slate-400">Belum tersedia</span>
                            @endif
                        </div>
                        <div class="mt-1 text-xs text-slate-500">
                            {{ $ringkasan['tb_u_terkini']['z_label'] ?? 'Z-score: -' }}
                        </div>
                        <div class="mt-1 text-xs font-semibold text-slate-600">
                            {{ $ringkasan['tb_u_terkini']['kategori'] ?? '-' }}
                        </div>
                    </div>

                    <!-- Kenaikan terakhir -->
                    <div class="rounded-xl border border-slate-200 p-4">
                        <span class="text-xs text-slate-500 uppercase tracking-wide">Kenaikan terakhir</span>
                        <div class="mt-2 text-2xl font-bold text-slate-800">
                            {{ $ringkasan['kenaikan_terakhir']['label'] }}
                        </div>
                        <div class="mt-1 text-xs text-slate-500">Dibanding pengukuran sebelumnya</div>
                    </div>

                    <!-- Status -->
                    <div class="rounded-xl border border-slate-200 p-4">
                        <span class="text-xs text-slate-500 uppercase tracking-wide">Status</span>
                        <div class="mt-2">
                            @if ($ringkasan['status']['nilai'])
                                <x-status-pill :status="$ringkasan['status']['nilai']" size="lg" />
                            @else
                                <span class="text-sm font-semibold text-slate-400">Belum dapat dinilai</span>
                            @endif
                        </div>
                        @if (! $ringkasan['status']['lengkap'])
                            <div class="mt-2 text-xs font-semibold text-sky-700">Penilaian sebagian</div>
                        @endif
                        <div class="mt-1 text-xs text-slate-500">{{ $ringkasan['status']['stunting_keterangan'] }}</div>
                    </div>
                </div>

                <!-- Kanvas grafik -->
                <div class="relative hidden" data-chart-canvas-wrap>
                    <div class="h-[280px] sm:h-[400px]">
                        <canvas id="canvasPertumbuhan" role="img"
                                aria-label="Grafik pertumbuhan {{ $b['nama'] }}"></canvas>
                    </div>
                </div>

                <p data-chart-fallback class="text-sm text-slate-500 italic">
                    Grafik tidak dapat ditampilkan. Data tetap tersedia pada tabel riwayat di bawah.
                </p>

                <!-- Legenda + catatan -->
                <div class="border-t border-slate-100 pt-4">
                    <div class="flex flex-wrap gap-x-5 gap-y-2 text-xs text-slate-600">
                        @foreach ($grafik['indikator']['bb_u']['kurva'] ?? [] as $garis)
                            <span class="inline-flex items-center gap-2">
                                <span @class(['inline-block w-6 border-t-2 border-slate-400', 'border-dashed' => $garis['k'] != 0])></span>
                                {{ $garis['label'] }}
                            </span>
                        @endforeach
                        <span class="inline-flex items-center gap-2">
                            <span class="inline-block w-6 border-t-[3px] border-teal-700"></span>
                            Hasil ukur {{ $b['nama'] }}
                        </span>
                    </div>
                    <p class="mt-3 text-xs text-slate-500">
                        Grafik ini adalah alat bantu pemantauan. Keputusan penanganan dan rujukan ditetapkan oleh tenaga kesehatan.
                    </p>
                </div>

                @foreach ($tabLabels as $kode => $label)
                    @if (($grafik['indikator'][$kode]['catatan_kurva'] ?? null) !== null)
                        <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg p-3"
                           data-catatan-kurva="{{ $kode }}">
                            {{ $label }}: {{ $grafik['indikator'][$kode]['catatan_kurva'] }}
                            Titik hasil ukur tetap ditampilkan.
                        </p>
                    @endif
                @endforeach

                @if ($grafik['jumlah_pengukuran'] === 1)
                    <p class="text-xs text-slate-500">Tambahkan pengukuran berikutnya untuk melihat tren pertumbuhan.</p>
                @endif
            </div>
        </div>
    @endif

    <!-- Kartu Riwayat Pengukuran -->
    <x-table-card>
        <div class="p-4 border-b border-slate-200 flex items-center gap-3">
            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <h2 class="text-sm font-bold text-slate-800">Riwayat Pengukuran</h2>
        </div>

        @if (! $adaData)
            <x-empty-state title="Belum ada data pengukuran." />
        @else
            <div x-data="{ semua: false }">
                <x-data-table>
                    <x-slot:head>
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left">Tanggal</th>
                            <th scope="col" class="px-4 py-3 text-left">Usia</th>
                            <th scope="col" class="px-4 py-3 text-right">BB (kg)</th>
                            <th scope="col" class="px-4 py-3 text-right">Δ BB</th>
                            <th scope="col" class="px-4 py-3 text-right">TB/PB (cm)</th>
                            <th scope="col" class="px-4 py-3 text-left">Cara Ukur</th>
                            <th scope="col" class="px-4 py-3 text-right">LILA (cm)</th>
                            <th scope="col" class="px-4 py-3 text-left">Status Gizi</th>
                            <th scope="col" class="px-4 py-3 text-left">Pemeriksa</th>
                        </tr>
                    </x-slot:head>

                    @foreach ($grafik['riwayat'] as $i => $r)
                        <tr @if ($i >= 5) x-show="semua" @endif class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $r['tanggal'] }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $r['usia_label'] }}</td>
                            <td class="px-4 py-3 text-sm text-slate-800 font-semibold text-right">{{ $r['berat_label'] ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-right">
                                @if ($r['selisih_bb_label'] !== null)
                                    <span class="font-medium {{ $r['selisih_bb'] >= 0 ? 'text-teal-700' : 'text-rose-700' }}">{{ $r['selisih_bb_label'] }}</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-800 font-semibold text-right">{{ $r['tinggi_label'] ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $r['cara_ukur'] }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700 text-right">{{ $r['lingkar_lengan_atas_label'] ?? '-' }}</td>
                            <td class="px-4 py-3"><x-status-pill :status="$r['status']" /></td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $r['petugas'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </x-data-table>

                @if (count($grafik['riwayat']) > 5)
                    <div class="p-4 border-t border-slate-200 text-center">
                        <button type="button" @click="semua = !semua"
                                class="text-xs font-bold text-teal-700 hover:underline min-h-[44px] px-4">
                            <span x-text="semua ? 'Tampilkan lebih sedikit' : 'Tampilkan semua'"></span>
                        </button>
                    </div>
                @endif
            </div>
        @endif
    </x-table-card>
@endsection

@push('scripts')
    @vite('resources/js/pages/growth-chart.js')
@endpush
