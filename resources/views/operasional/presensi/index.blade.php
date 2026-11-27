@extends('layouts.app')

@section('title', 'Presensi Posyandu & Antrean Hari Ini')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumbs -->
    <div class="flex items-center gap-2 text-xs font-medium text-slate-400">
        <a href="{{ route('jadwal.index') }}" class="hover:text-slate-600">Jadwal & Rujukan</a>
        <span>/</span>
        <span class="text-slate-700 font-semibold">Presensi Posyandu</span>
    </div>

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-2xl font-extrabold text-[#0B1C30] tracking-tight">Presensi Posyandu & Antrean Hari Ini</h1>
            <p class="text-xs text-[#6D7A77] mt-1">Pencatatan kehadiran balita secara otomatis melalui pemindaian QR kartu identitas balita.</p>
        </div>

        <!-- Schedule Selector -->
        <div class="flex items-center gap-3">
            <form method="GET" action="{{ route('presensi.index') }}" class="flex items-center gap-2">
                <label for="jadwal_id" class="text-xs font-semibold text-slate-600 shrink-0">Pilih Jadwal:</label>
                <select name="jadwal_id" id="jadwal_id" onchange="this.form.submit()" class="text-xs rounded-xl border-slate-200 bg-slate-50 font-semibold text-slate-800 py-2 px-3 focus:ring-teal-500 focus:border-teal-500">
                    @foreach($jadwalList as $j)
                        <option value="{{ $j->id }}" {{ $selectedJadwalId == $j->id ? 'selected' : '' }}>
                            {{ $j->judul }} ({{ $j->date->format('d M Y') }})
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    <!-- Subnavigation Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 text-xs font-semibold text-slate-500 pb-px">
        <a href="{{ route('jadwal.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-t-xl hover:text-slate-700 hover:bg-slate-50 transition-colors">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span>Kalender & Agenda</span>
        </a>

        <a href="{{ route('presensi.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-t-xl text-[#00685F] font-bold border-b-2 border-[#00685F] bg-white transition-colors">
            <svg class="w-4 h-4 text-[#00685F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

        @can('viewAny', App\Models\SuratRujukan::class)
        <a href="{{ route('rujukan.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-t-xl hover:text-slate-700 hover:bg-slate-50 transition-colors">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span>Surat Rujukan</span>
        </a>
        @endcan
    </div>

    <!-- Session Info & Rekap Bar -->
    @if($rekap)
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-semibold text-[#6D7A77] uppercase tracking-wider block">Total Hadir</span>
            <span class="text-2xl font-black text-emerald-600 mt-1 block">{{ $rekap['hadir'] }}</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-semibold text-[#6D7A77] uppercase tracking-wider block">Tidak Hadir / Izin</span>
            <span class="text-2xl font-black text-rose-500 mt-1 block">{{ $rekap['tidak_hadir'] }}</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-semibold text-[#6D7A77] uppercase tracking-wider block">Total Terdaftar Reservasi</span>
            <span class="text-2xl font-black text-slate-700 mt-1 block">{{ $rekap['total_reservasi'] }} / {{ $rekap['kuota'] }}</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-semibold text-[#6D7A77] uppercase tracking-wider block">Tingkat Kehadiran</span>
            <span class="text-2xl font-black text-[#00685F] mt-1 block">{{ $rekap['persentase_kehadiran'] }}%</span>
        </div>
    </div>
    @endif

    <!-- QR Scanner Area: 2 Columns -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Kolom Kiri: Panel Kamera Scanner (5 Kolom) -->
        <div class="lg:col-span-5 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <h2 class="text-sm font-bold text-[#0B1C30]">Pemindai QR Kartu Balita</h2>
                </div>
                <span id="camera-status" class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600">
                    Siap Memindai
                </span>
            </div>

            <!-- Scanner Box -->
            <div class="relative overflow-hidden rounded-xl bg-slate-900 border border-slate-300 aspect-square flex flex-col items-center justify-center">
                <div id="qr-reader" class="w-full h-full"></div>
                <div id="scanner-placeholder" class="absolute inset-0 flex flex-col items-center justify-center p-6 text-center text-white bg-slate-900/90 space-y-3 z-10">
                    <div class="w-16 h-16 rounded-2xl bg-teal-600/30 border border-teal-500/50 flex items-center justify-center text-teal-400">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold">Kamera Belum Aktif</p>
                        <p class="text-[11px] text-slate-400 mt-1">Tekan tombol di bawah untuk menyalakan kamera pemindai QR.</p>
                    </div>
                    <button type="button" id="btn-toggle-camera" onclick="toggleCamera()" class="px-4 py-2 bg-[#00685F] hover:bg-teal-700 text-white font-bold text-xs rounded-xl shadow-md transition-all">
                        Aktifkan Kamera
                    </button>
                </div>
            </div>

            <!-- Petunjuk Scan -->
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex items-start gap-2.5 text-[11px] text-[#6D7A77]">
                <svg class="w-4 h-4 text-[#00685F] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Arahkan kode QR yang tertera pada Kartu Identitas Balita ke area kamera hingga terdengar sinyal konfirmasi.</span>
            </div>
        </div>

        <!-- Kolom Kanan: Hasil Identifikasi Balita (7 Kolom) -->
        <div class="lg:col-span-7 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between space-y-4">
            <div>
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <h2 class="text-sm font-bold text-[#0B1C30]">Hasil Identifikasi & Presensi</h2>
                    <span id="scan-badge" class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-500 uppercase tracking-wider">
                        Standby
                    </span>
                </div>

                <!-- Live Result Container -->
                <div id="result-box" class="space-y-4">
                    <!-- Default Waiting State -->
                    <div id="state-waiting" class="py-12 flex flex-col items-center justify-center text-center space-y-3 text-slate-400">
                        <div class="w-14 h-14 rounded-full bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-300">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v16m8-8H4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-600">Menunggu Pemindaian Kartu</p>
                            <p class="text-[11px] text-slate-400 mt-1 max-w-sm">Data identitas balita dan konfirmasi presensi akan otomatis muncul di sini setelah QR dipindai.</p>
                        </div>
                    </div>

                    <!-- Scanned Balita Card (Hidden by default) -->
                    <div id="state-result" class="hidden space-y-4">
                        <div id="result-alert" class="p-3.5 rounded-xl border text-xs font-semibold flex items-center gap-2.5">
                            <!-- Injected by JS -->
                        </div>

                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-[#6D7A77] tracking-wider block">Nama Balita</span>
                                    <h3 id="balita-name" class="text-base font-extrabold text-[#0B1C30]">-</h3>
                                </div>
                                <span id="balita-gender-badge" class="px-2.5 py-1 rounded-full text-xs font-bold bg-teal-100 text-[#00685F]">
                                    -
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-3 text-xs pt-2 border-t border-slate-200/60">
                                <div>
                                    <span class="text-[10px] font-semibold text-slate-400 uppercase block">Identifier QR</span>
                                    <span id="balita-qr" class="font-mono font-bold text-slate-700">-</span>
                                </div>
                                <div>
                                    <span class="text-[10px] font-semibold text-slate-400 uppercase block">Tanggal Lahir</span>
                                    <span id="balita-birth" class="font-semibold text-slate-700">-</span>
                                </div>
                                <div>
                                    <span class="text-[10px] font-semibold text-slate-400 uppercase block">Orang Tua / Wali</span>
                                    <span id="balita-parent" class="font-semibold text-slate-700">-</span>
                                </div>
                                <div>
                                    <span class="text-[10px] font-semibold text-slate-400 uppercase block">Waktu Presensi</span>
                                    <span id="presensi-time" class="font-semibold text-slate-700">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Alert / Info -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-[#6D7A77]">
                <span>Sesi Posyandu: <strong>{{ $selectedJadwal?->judul ?? 'Tidak Ada Jadwal Dipilih' }}</strong></span>
                <span class="text-slate-400 text-[11px]">{{ now()->format('d M Y') }}</span>
            </div>
        </div>
    </div>

    <!-- Tabel Kehadiran Hari Ini -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-extrabold text-[#0B1C30]">Daftar Kehadiran Hari Ini</h2>
                <p class="text-xs text-[#6D7A77] mt-0.5">Catatan presensi balita pada jadwal sesi posyandu terpilih.</p>
            </div>

            <!-- Filter & Search Form -->
            <form method="GET" action="{{ route('presensi.index') }}" class="flex flex-wrap items-center gap-2">
                <input type="hidden" name="jadwal_id" value="{{ $selectedJadwalId }}">
                
                <select name="status" class="text-xs rounded-xl border-slate-200 bg-slate-50 text-slate-700 py-2 px-3">
                    <option value="">Semua Status</option>
                    <option value="hadir" {{ request('status') === 'hadir' ? 'selected' : '' }}>Hadir</option>
                    <option value="tidak_hadir" {{ request('status') === 'tidak_hadir' ? 'selected' : '' }}>Tidak Hadir</option>
                </select>

                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama balita / QR..." class="text-xs rounded-xl border-slate-200 bg-slate-50 text-slate-800 py-2 px-3 w-48 focus:ring-teal-500 focus:border-teal-500">

                <button type="submit" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors">
                    Filter
                </button>
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/80 text-[11px] font-bold text-[#6D7A77] uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-6">Waktu Presensi</th>
                        <th class="py-3.5 px-6">Nama Balita</th>
                        <th class="py-3.5 px-6">Identifier QR</th>
                        <th class="py-3.5 px-6">Orang Tua / NIK</th>
                        <th class="py-3.5 px-6">Status Kehadiran</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($presensiList as $p)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3.5 px-6 font-mono text-slate-500">
                            {{ $p->created_at->format('H:i') }} WIB
                        </td>
                        <td class="py-3.5 px-6">
                            <span class="font-bold text-[#0B1C30] block">{{ $p->balita?->name ?? '-' }}</span>
                            <span class="text-[10px] text-[#6D7A77]">{{ $p->balita?->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                        </td>
                        <td class="py-3.5 px-6 font-mono text-xs text-teal-800 font-semibold">
                            {{ $p->balita?->qr_code ?? '-' }}
                        </td>
                        <td class="py-3.5 px-6">
                            <span class="font-semibold text-slate-800 block">{{ $p->balita?->orangTua?->name ?? '-' }}</span>
                            <span class="text-[10px] font-mono text-slate-400">{{ $p->balita?->orangTua?->nik ?? '-' }}</span>
                        </td>
                        <td class="py-3.5 px-6">
                            @if($p->presence_status === 'hadir')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Hadir
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    Tidak Hadir
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-6 text-right space-x-2">
                            <!-- Toggle Status Form -->
                            <form method="POST" action="{{ route('presensi.update', $p) }}" class="inline-block">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="presence_status" value="{{ $p->presence_status === 'hadir' ? 'tidak_hadir' : 'hadir' }}">
                                <button type="submit" class="text-[11px] font-semibold text-slate-500 hover:text-slate-800 underline">
                                    {{ $p->presence_status === 'hadir' ? 'Tandai Tidak Hadir' : 'Tandai Hadir' }}
                                </button>
                            </form>

                            <!-- Delete Form -->
                            <form method="POST" action="{{ route('presensi.destroy', $p) }}" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data presensi ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-[11px] font-semibold text-rose-500 hover:text-rose-700">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">
                            <p class="text-xs font-semibold">Belum ada data kehadiran untuk jadwal ini.</p>
                            <p class="text-[11px] text-slate-400 mt-1">Gunakan scanner QR di atas untuk mencatat presensi.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($presensiList->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $presensiList->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
let html5QrCode = null;
let isCameraActive = false;
let isProcessing = false;
const currentJadwalId = {{ $selectedJadwalId ?? 'null' }};

function toggleCamera() {
    if (!currentJadwalId) {
        alert('Silakan pilih jadwal posyandu terlebih dahulu.');
        return;
    }

    if (isCameraActive) {
        stopCamera();
    } else {
        startCamera();
    }
}

function startCamera() {
    const readerElement = document.getElementById('qr-reader');
    const placeholder = document.getElementById('scanner-placeholder');
    const statusText = document.getElementById('camera-status');
    const btnToggle = document.getElementById('btn-toggle-camera');

    if (!html5QrCode) {
        html5QrCode = new Html5Qrcode("qr-reader");
    }

    placeholder.classList.add('hidden');
    statusText.innerText = 'Menghubungkan Kamera...';
    statusText.className = 'text-[11px] font-semibold px-2.5 py-1 rounded-full bg-amber-100 text-amber-700';

    html5QrCode.start(
        { facingMode: "environment" },
        {
            fps: 10,
            qrbox: { width: 250, height: 250 }
        },
        (decodedText, decodedResult) => {
            onScanSuccess(decodedText);
        },
        (errorMessage) => {
            // Scanning in progress
        }
    ).then(() => {
        isCameraActive = true;
        statusText.innerText = 'Kamera Aktif';
        statusText.className = 'text-[11px] font-semibold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700';
        btnToggle.innerText = 'Matikan Kamera';
    }).catch(err => {
        console.error('Kamera error:', err);
        placeholder.classList.remove('hidden');
        statusText.innerText = 'Kamera Gagal / Izin Ditolak';
        statusText.className = 'text-[11px] font-semibold px-2.5 py-1 rounded-full bg-rose-100 text-rose-700';
        alert('Gagal mengakses kamera. Pastikan izin kamera telah diberikan di peramban Anda.');
    });
}

function stopCamera() {
    if (html5QrCode && isCameraActive) {
        html5QrCode.stop().then(() => {
            isCameraActive = false;
            document.getElementById('scanner-placeholder').classList.remove('hidden');
            document.getElementById('camera-status').innerText = 'Kamera Dimatikan';
            document.getElementById('camera-status').className = 'text-[11px] font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600';
            document.getElementById('btn-toggle-camera').innerText = 'Aktifkan Kamera';
        }).catch(err => console.error(err));
    }
}

function onScanSuccess(decodedText) {
    if (isProcessing) return;
    isProcessing = true;

    // Send check-in request to backend
    fetch("{{ route('presensi.check-in') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            jadwal_id: currentJadwalId,
            qr_code: decodedText
        })
    })
    .then(async response => {
        const data = await response.json();
        if (!response.ok) {
            throw new Error(data.message || (data.errors ? Object.values(data.errors)[0][0] : 'Check-in gagal.'));
        }
        return data;
    })
    .then(data => {
        displayScanResult(data, true);
    })
    .catch(error => {
        displayScanError(error.message);
    })
    .finally(() => {
        setTimeout(() => {
            isProcessing = false;
        }, 2000); // 2 second pause before next scan
    });
}

function displayScanResult(data, isSuccess) {
    document.getElementById('state-waiting').classList.add('hidden');
    document.getElementById('state-result').classList.remove('hidden');

    const alertBox = document.getElementById('result-alert');
    const badge = document.getElementById('scan-badge');

    if (data.status === 'already_checked_in') {
        alertBox.className = 'p-3.5 rounded-xl border text-xs font-semibold flex items-center gap-2.5 bg-amber-50 border-amber-200 text-amber-800';
        alertBox.innerHTML = `<span>⚠️ ${data.message}</span>`;
        badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-md bg-amber-100 text-amber-700 uppercase tracking-wider';
        badge.innerText = 'Sudah Hadir';
    } else {
        alertBox.className = 'p-3.5 rounded-xl border text-xs font-semibold flex items-center gap-2.5 bg-emerald-50 border-emerald-200 text-emerald-800';
        alertBox.innerHTML = `<span>✓ ${data.message}</span>`;
        badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-700 uppercase tracking-wider';
        badge.innerText = 'Check-in Berhasil';
    }

    if (data.balita) {
        document.getElementById('balita-name').innerText = data.balita.name;
        document.getElementById('balita-gender-badge').innerText = data.balita.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
        document.getElementById('balita-qr').innerText = data.balita.qr_code;
        document.getElementById('balita-birth').innerText = data.balita.birth_date ? new Date(data.balita.birth_date).toLocaleDateString('id-ID') : '-';
        document.getElementById('balita-parent').innerText = data.balita.orang_tua ? `${data.balita.orang_tua.name} (${data.balita.orang_tua.nik})` : '-';
        document.getElementById('presensi-time').innerText = new Date().toLocaleTimeString('id-ID') + ' WIB';
    }
}

function displayScanError(message) {
    document.getElementById('state-waiting').classList.add('hidden');
    document.getElementById('state-result').classList.remove('hidden');

    const alertBox = document.getElementById('result-alert');
    const badge = document.getElementById('scan-badge');

    alertBox.className = 'p-3.5 rounded-xl border text-xs font-semibold flex items-center gap-2.5 bg-rose-50 border-rose-200 text-rose-800';
    alertBox.innerHTML = `<span>✕ ${message}</span>`;
    badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-md bg-rose-100 text-rose-700 uppercase tracking-wider';
    badge.innerText = 'Check-in Ditolak';
}
</script>
@endpush
@endsection
