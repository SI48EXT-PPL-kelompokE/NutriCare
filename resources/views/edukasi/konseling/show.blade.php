@extends('layouts.admin')

@section('title', 'Detail Sesi Konseling - ' . $sesi->topik)

@section('content')
<div class="space-y-6 w-full">

    <!-- Header & navigasi -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('konseling.index') }}" class="hover:text-slate-600 transition-colors">Sesi Konseling</a>
                <span>/</span>
                <span class="text-slate-600 font-semibold">Detail Sesi #{{ $sesi->id }}</span>
            </nav>
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">{{ $sesi->topik }}</h1>
                @if ($sesi->status === 'diajukan')
                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                        Status: Diajukan
                    </span>
                @elseif ($sesi->status === 'dijadwalkan')
                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold bg-sky-50 text-sky-700 border border-sky-200">
                        Status: Dijadwalkan
                    </span>
                @elseif ($sesi->status === 'selesai')
                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Status: Selesai
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-0.5">Diajukan pada {{ $sesi->created_at->format('d M Y, H:i') }} WIB</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('konseling.index') }}" 
               class="inline-flex items-center gap-2 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Kembali ke Daftar</span>
            </a>
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

    @if (session('status_notifikasi'))
        <div class="p-4 bg-sky-50 border border-sky-200 rounded-2xl flex items-center gap-3 text-xs text-sky-800">
            <svg class="w-5 h-5 text-sky-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <div>
                <span class="font-bold block">Status Notifikasi (KF-49)</span>
                <span class="text-sky-700">{{ session('status_notifikasi') }}</span>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Kolom kiri: progres & hasil konseling -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Tahapan status konseling & log notifikasi -->
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-4">Alur Perkembangan Sesi & Notifikasi</h2>
                <div class="grid grid-cols-3 gap-2 text-center text-xs">
                    <div class="p-3 rounded-xl {{ in_array($sesi->status, ['diajukan', 'dijadwalkan', 'selesai']) ? 'bg-amber-50 border border-amber-200 text-amber-800 font-semibold' : 'bg-slate-50 text-slate-400' }}">
                        <span class="block text-[10px] uppercase text-amber-600 font-bold mb-0.5">Tahap 1</span>
                        <span>1. Diajukan</span>
                    </div>

                    <div class="p-3 rounded-xl {{ in_array($sesi->status, ['dijadwalkan', 'selesai']) ? 'bg-sky-50 border border-sky-200 text-sky-800 font-semibold' : 'bg-slate-50 text-slate-400' }}">
                        <span class="block text-[10px] uppercase text-sky-600 font-bold mb-0.5">Tahap 2</span>
                        <span>2. Dijadwalkan</span>
                    </div>

                    <div class="p-3 rounded-xl {{ $sesi->status === 'selesai' ? 'bg-emerald-50 border border-emerald-200 text-emerald-800 font-semibold' : 'bg-slate-50 text-slate-400' }}">
                        <span class="block text-[10px] uppercase text-emerald-600 font-bold mb-0.5">Tahap 3</span>
                        <span>3. Selesai</span>
                    </div>
                </div>

                <!-- Kartu Status Notifikasi Terkirim (KF-49) -->
                <div class="mt-4 p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-3 text-xs">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <div class="p-2 bg-teal-50 border border-teal-200 text-[#008f7a] rounded-lg shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                            </div>
                            <div class="space-y-0.5">
                                <span class="font-bold text-slate-900 block">Riwayat & Status Notifikasi (KF-49) — Penerima:</span>
                                <span class="text-[11px] text-slate-500 block">
                                    Ditujukan untuk: <strong>{{ $sesi->orangTua->name ?? 'Orang Tua' }}</strong> 
                                    @if ($sesi->orangTua && $sesi->orangTua->balita && $sesi->orangTua->balita->count() > 0)
                                        (Balita: <strong>{{ $sesi->orangTua->balita->first()->name }}</strong>)
                                    @endif
                                </span>
                                @if ($sesi->status === 'diajukan')
                                    <p class="text-slate-600 leading-relaxed pt-1">
                                        Pesan: Permohonan konseling gizi topik '{{ $sesi->topik }}' telah tercatat dan sedang menunggu penetapan jadwal nakes.
                                    </p>
                                @elseif ($sesi->status === 'dijadwalkan')
                                    <p class="text-slate-600 leading-relaxed pt-1">
                                        Pesan: Jadwal konseling gizi balita telah ditetapkan bersama <strong>{{ $sesi->nakes->name ?? 'Tenaga Medis' }}</strong> pada <strong>{{ $sesi->jadwal ? $sesi->jadwal->format('d M Y, H:i') . ' WIB' : '-' }}</strong>.
                                    </p>
                                @elseif ($sesi->status === 'selesai')
                                    <p class="text-slate-600 leading-relaxed pt-1">
                                        Pesan: Sesi konseling telah selesai. Rekomendasi pola makan dan hasil evaluasi klinis balita siap ditinjau.
                                    </p>
                                @endif
                            </div>
                        </div>

                        <!-- Tombol Izin & Uji Notifikasi Peramban Asli -->
                        <div class="shrink-0">
                            <button type="button" id="btnRequestBrowserNotif" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-teal-300 hover:bg-teal-50 text-[#008f7a] text-[11px] font-semibold rounded-lg shadow-2xs transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <span id="labelBrowserNotif">Aktifkan Notifikasi Desktop</span>
                            </button>
                        </div>
                    </div>

                    <!-- Indikator Status Izin Browser -->
                    <div id="browserNotifStatus" class="text-[11px] text-slate-400 border-t border-slate-200/60 pt-2 flex items-center justify-between">
                        <span id="browserNotifMsg">Peramban belum mengaktifkan izin notifikasi desktop untuk orang tua.</span>
                        <span id="browserNotifBadge" class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-200 text-slate-600">Belum Aktif</span>
                    </div>
                </div>
            </div>

            <!-- Hasil evaluasi konseling klinis -->
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Catatan & Hasil Konseling Klinis</h2>
                        <p class="text-xs text-slate-400">Dokumentasi hasil pemeriksaan, rekomendasi gizi, dan tindak lanjut balita.</p>
                    </div>
                    @if ($sesi->status === 'selesai')
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700">
                            Tercatat Permanen
                        </span>
                    @endif
                </div>

                @if ($sesi->status === 'selesai')
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
                        <p class="text-xs leading-relaxed text-slate-700 whitespace-pre-line">{{ $sesi->hasil }}</p>
                    </div>
                @elseif ($sesi->status === 'dijadwalkan')
                    <div class="p-4 bg-sky-50/70 border border-sky-100 rounded-xl mb-4 text-xs text-sky-800">
                        Sesi konsultasi telah dijadwalkan. Tenaga kesehatan dapat mengisi catatan hasil di bawah setelah konsultasi selesai dilakukan.
                    </div>

                    <form action="{{ route('konseling.selesai', $sesi->id) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label for="hasil" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Masukkan Hasil Konseling & Anjuran Pola Gizi <span class="text-rose-500">*</span>
                            </label>
                            <textarea 
                                name="hasil" 
                                id="hasil" 
                                rows="5" 
                                required
                                placeholder="Tuliskan evaluasi antropometri, rekomendasi porsi MPASI, anjuran protein hewani, atau suplemen gizi balita..."
                                class="w-full px-3.5 py-2.5 bg-slate-50 border @error('hasil') border-rose-400 @else border-slate-200 @enderror rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008f7a] focus:bg-white transition-all resize-y"
                            >{{ old('hasil', str_starts_with($sesi->hasil ?? '', 'Keluhan awal: ') ? '' : $sesi->hasil) }}</textarea>
                            @error('hasil')
                                <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" id="btnSimpanHasilKonseling" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl transition-colors shadow-sm flex items-center gap-2">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 6 9 17l-5-5"/>
                                </svg>
                                <span>Simpan Hasil & Tandai Selesai</span>
                            </button>
                        </div>
                    </form>
                @else
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-500 leading-relaxed">
                        <p class="font-semibold text-slate-700 mb-1">Menunggu Penetapan Jadwal</p>
                        <p>Catatan keluhan awal yang dilaporkan orang tua:</p>
                        <p class="italic text-slate-600 mt-1">{{ $sesi->hasil ?? 'Tidak ada catatan keluhan awal spesifik yang dicantumkan.' }}</p>
                        <p class="mt-3 text-[11px] text-slate-400">Silakan tetapkan jadwal dan nakes pendamping pada panel samping kanan untuk mengaktifkan sesi.</p>
                    </div>
                @endif
            </div>

        </div>

        <!-- Kolom kanan: data keluarga & aksi jadwal nakes -->
        <div class="space-y-6">

            <!-- Data orang tua & balita -->
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
                <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider pb-2 border-b border-slate-100">
                    Data Orang Tua & Balita
                </h2>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Nama Orang Tua:</span>
                        <span class="font-bold text-slate-900">{{ $sesi->orangTua->name ?? '-' }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 block text-[11px]">Nomor Induk Kependudukan (NIK):</span>
                        <code class="text-slate-700 font-mono bg-slate-50 px-2 py-0.5 rounded border border-slate-100">{{ $sesi->orangTua->nik ?? '-' }}</code>
                    </div>

                    <div>
                        <span class="text-slate-400 block text-[11px]">Kontak / WhatsApp:</span>
                        <span class="text-slate-800 font-medium">{{ $sesi->orangTua->kontak ?? '-' }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 block text-[11px]">Alamat Domisili:</span>
                        <span class="text-slate-600">{{ $sesi->orangTua->alamat ?? '-' }}</span>
                    </div>

                    @if ($sesi->orangTua && $sesi->orangTua->balita && $sesi->orangTua->balita->count() > 0)
                        <div class="pt-3 border-t border-slate-100">
                            <span class="text-slate-400 block text-[11px] mb-1.5">Balita Terdaftar:</span>
                            <div class="space-y-1.5">
                                @foreach ($sesi->orangTua->balita as $balita)
                                    <div class="p-2 bg-slate-50 border border-slate-100 rounded-lg flex items-center justify-between text-[11px]">
                                        <span class="font-semibold text-slate-800">{{ $balita->name }}</span>
                                        <span class="text-slate-500">{{ $balita->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Penjadwalan & nakes bertugas -->
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
                <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider pb-2 border-b border-slate-100">
                    Penjadwalan & Tenaga Medis
                </h2>

                @if ($sesi->status === 'diajukan')
                    @if ($errors->has('jadwal') || $errors->has('user_id'))
                        <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-700 space-y-1">
                            <div class="flex items-center gap-1.5 font-bold text-rose-800">
                                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span>Jadwal Nakes Tidak Tersedia</span>
                            </div>
                            <p class="leading-relaxed">{{ $errors->first('jadwal') ?: $errors->first('user_id') }}</p>
                        </div>
                    @endif

                    <form action="{{ route('konseling.jadwalkan', $sesi->id) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label for="user_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Pilih Tenaga Kesehatan <span class="text-rose-500">*</span>
                            </label>
                            <select 
                                name="user_id" 
                                id="user_id" 
                                required
                                class="w-full px-3.5 py-2.5 bg-slate-50 border @error('user_id') border-rose-400 @else border-slate-200 @enderror rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008f7a] focus:bg-white transition-all"
                            >
                                <option value="">Pilih Nakes / Bidan...</option>
                                @foreach ($nakesList as $nakes)
                                    <option value="{{ $nakes->id }}" {{ old('user_id', $sesi->user_id) == $nakes->id ? 'selected' : '' }}>
                                        {{ $nakes->name }} ({{ $nakes->wilayah_tugas ?? 'Puskesmas' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="jadwal" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Tanggal & Jam Konsultasi <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="datetime-local" 
                                name="jadwal" 
                                id="jadwal" 
                                required
                                value="{{ old('jadwal', $sesi->jadwal ? $sesi->jadwal->format('Y-m-d\TH:i') : '') }}"
                                class="w-full px-3.5 py-2.5 bg-slate-50 border @error('jadwal') border-rose-400 @else border-slate-200 @enderror rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#008f7a] focus:bg-white transition-all"
                            >
                            @error('jadwal')
                                <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                            <div id="availabilityNotice" class="hidden mt-2 p-2 rounded-lg text-[11px] leading-tight"></div>
                        </div>

                        <button type="submit" id="btnJadwalkanKonseling" class="w-full py-2.5 bg-[#008f7a] hover:bg-[#007a68] text-white text-xs font-semibold rounded-xl transition-colors shadow-sm flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                                <line x1="16" x2="16" y1="2" y2="6"/>
                                <line x1="8" x2="8" y1="2" y2="6"/>
                                <line x1="3" x2="21" y1="10" y2="10"/>
                            </svg>
                            <span>Tetapkan Jadwal & Nakes</span>
                        </button>
                    </form>
                @else
                    <div class="space-y-3 text-xs">
                        <div>
                            <span class="text-slate-400 block text-[11px]">Nakes Pendamping:</span>
                            <div class="flex items-center gap-2.5 mt-1">
                                <div class="w-8 h-8 rounded-full bg-teal-100 text-[#008f7a] flex items-center justify-center font-bold text-xs">
                                    {{ substr($sesi->nakes->name ?? 'N', 0, 1) }}
                                </div>
                                <div>
                                    <span class="font-bold text-slate-900 block">{{ $sesi->nakes->name ?? 'Tenaga Kesehatan' }}</span>
                                    <span class="text-[11px] text-slate-400">{{ $sesi->nakes->wilayah_tugas ?? 'Puskesmas' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-slate-100">
                            <span class="text-slate-400 block text-[11px]">Jadwal Sesi:</span>
                            <span class="font-semibold text-slate-800 text-sm block mt-0.5">
                                {{ $sesi->jadwal ? $sesi->jadwal->format('d M Y, H:i') . ' WIB' : '-' }}
                            </span>
                        </div>
                    </div>
                @endif
            </div>

        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const userIdInput = document.getElementById('user_id');
        const jadwalInput = document.getElementById('jadwal');
        const noticeBox = document.getElementById('availabilityNotice');

        function checkAvailability() {
            if (!userIdInput || !jadwalInput || !noticeBox) return;

            const userId = userIdInput.value;
            const jadwal = jadwalInput.value;

            if (!userId || !jadwal) {
                noticeBox.classList.add('hidden');
                return;
            }

            const url = `{{ route('konseling.cek-ketersediaan') }}?user_id=${userId}&jadwal=${encodeURIComponent(jadwal)}&ignore_id={{ $sesi->id }}`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    noticeBox.classList.remove('hidden', 'bg-rose-50', 'text-rose-700', 'border-rose-200', 'bg-emerald-50', 'text-emerald-700', 'border-emerald-200');
                    noticeBox.classList.add('border');

                    if (data.available) {
                        noticeBox.classList.add('bg-emerald-50', 'text-emerald-700', 'border-emerald-200');
                        noticeBox.textContent = data.message;
                    } else {
                        noticeBox.classList.add('bg-rose-50', 'text-rose-700', 'border-rose-200');
                        noticeBox.textContent = data.message;
                    }
                })
                .catch(() => {
                    noticeBox.classList.add('hidden');
                });
        }

        if (userIdInput && jadwalInput) {
            userIdInput.addEventListener('change', checkAvailability);
            jadwalInput.addEventListener('change', checkAvailability);
        }

        // --- Web Notification API (KF-49: Notifikasi Desktop Orang Tua) ---
        const btnNotif = document.getElementById('btnRequestBrowserNotif');
        const notifBadge = document.getElementById('browserNotifBadge');
        const notifMsg = document.getElementById('browserNotifMsg');
        const labelNotif = document.getElementById('labelBrowserNotif');

        const parentName = @json($sesi->orangTua->name ?? 'Orang Tua');
        const balitaName = @json($sesi->orangTua?->balita?->first()?->name ?? 'Balita');
        const currentStatus = @json($sesi->status);
        const topikKonseling = @json($sesi->topik);
        const jadwalText = @json($sesi->jadwal ? $sesi->jadwal->format('d M Y, H:i') . ' WIB' : '');
        const nakesName = @json($sesi->nakes->name ?? 'Tenaga Kesehatan');

        function getNotifPayload() {
            if (currentStatus === 'dijadwalkan') {
                return {
                    title: `NutriCare: Jadwal Konseling Ditetapkan!`,
                    body: `Halo Ibu/Bpk ${parentName}, jadwal konseling gizi untuk balita ${balitaName} telah ditetapkan bersama ${nakesName} pada ${jadwalText}.`
                };
            } else if (currentStatus === 'selesai') {
                return {
                    title: `NutriCare: Hasil Konseling Siap Dilihat`,
                    body: `Halo Ibu/Bpk ${parentName}, sesi konseling gizi untuk balita ${balitaName} telah selesai. Catatan evaluasi dan anjuran gizi siap ditinjau.`
                };
            } else {
                return {
                    title: `NutriCare: Permohonan Konseling Diajukan`,
                    body: `Halo Ibu/Bpk ${parentName}, pengajuan konseling gizi topik '${topikKonseling}' atas balita ${balitaName} berhasil terdaftar dan menunggu jadwal nakes.`
                };
            }
        }

        function triggerNativeNotification(title, body) {
            if (!("Notification" in window)) return;

            if (Notification.permission === "granted") {
                new Notification(title, {
                    body: body,
                    icon: 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%23008f7a"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>'
                });
            }
        }

        function updateNotifUi() {
            if (!("Notification" in window)) {
                if (notifMsg) notifMsg.textContent = "Peramban ini tidak mendukung Web Notification API.";
                if (btnNotif) btnNotif.style.display = 'none';
                return;
            }

            if (Notification.permission === "granted") {
                if (notifBadge) {
                    notifBadge.textContent = "Aktif";
                    notifBadge.className = "px-2 py-0.5 rounded text-[10px] font-medium bg-emerald-100 text-emerald-800";
                }
                if (notifMsg) notifMsg.textContent = `Notifikasi peramban aktif untuk akun Orang Tua (Ibu/Bpk ${parentName}).`;
                if (labelNotif) labelNotif.textContent = "Uji Pop-up Notifikasi";
            } else if (Notification.permission === "denied") {
                if (notifBadge) {
                    notifBadge.textContent = "Diblokir";
                    notifBadge.className = "px-2 py-0.5 rounded text-[10px] font-medium bg-rose-100 text-rose-800";
                }
                if (notifMsg) notifMsg.textContent = "Izin notifikasi diblokir di setelan peramban.";
                if (labelNotif) labelNotif.textContent = "Izin Diblokir";
            }
        }

        updateNotifUi();

        if (btnNotif) {
            btnNotif.addEventListener('click', function () {
                if (!("Notification" in window)) return;

                if (Notification.permission === "granted") {
                    const payload = getNotifPayload();
                    triggerNativeNotification(payload.title, payload.body);
                } else if (Notification.permission !== "denied") {
                    Notification.requestPermission().then(function (permission) {
                        updateNotifUi();
                        if (permission === "granted") {
                            const payload = getNotifPayload();
                            triggerNativeNotification(payload.title, payload.body);
                        }
                    });
                }
            });
        }

        @if (session('status_notifikasi'))
            if ("Notification" in window && Notification.permission === "granted") {
                const payload = getNotifPayload();
                triggerNativeNotification(payload.title, payload.body);
            }
        @endif
    });
</script>
@endpush

