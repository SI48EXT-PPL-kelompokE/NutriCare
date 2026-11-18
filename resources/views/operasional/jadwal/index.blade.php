@extends('layouts.app')

@section('title', 'Jadwal Agenda Posyandu')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumbs -->
    <div class="flex items-center gap-2 text-xs font-medium text-slate-400">
        <a href="{{ route('jadwal.index') }}" class="hover:text-slate-600">Jadwal & Rujukan</a>
        <span>/</span>
        <span class="text-slate-700 font-semibold">Kalender & Agenda</span>
    </div>

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-2xl font-extrabold text-[#0B1C30] tracking-tight">Jadwal Agenda Posyandu</h1>
            <p class="text-xs text-[#6D7A77] mt-1">Kelola kalender kegiatan, alokasi tenaga kesehatan penanggung jawab, dan kuota pengunjung posyandu.</p>
        </div>

        <div class="flex items-center gap-3">
            @can('create', App\Models\JadwalPosyandu::class)
            <a href="{{ route('jadwal.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#00685F] text-white text-xs font-bold hover:bg-[#00524B] shadow-xs transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah Jadwal</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Subnavigation Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 text-xs font-semibold text-slate-500 pb-px">
        <a href="{{ route('jadwal.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-t-xl text-[#00685F] font-bold border-b-2 border-[#00685F] bg-white transition-colors">
            <svg class="w-4 h-4 text-[#00685F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span>Kalender & Agenda</span>
        </a>

        @can('viewAny', App\Models\Presensi::class)
        <a href="{{ route('presensi.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-t-xl hover:text-slate-700 hover:bg-slate-50 transition-colors">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Presensi & Antrean Hari Ini</span>
        </a>
        @endcan

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

    <!-- Flash Notifications -->
    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-xs font-semibold flex items-center justify-between">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-bold">&times;</button>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs">
        <div class="font-bold mb-1">Terjadi kesalahan:</div>
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Calendar View Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 gap-4">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#00685F]"></span>
                <h2 class="text-base font-bold text-[#0B1C30]">Kalender Kegiatan Posyandu</h2>
            </div>
            <!-- Filter Nakes -->
            <div class="flex items-center gap-2">
                <label for="filterNakes" class="text-xs font-semibold text-slate-500">Filter Nakes:</label>
                <select id="filterNakes" class="text-xs rounded-xl border-slate-200 bg-slate-50 font-medium text-slate-800 py-2 px-3 focus:ring-teal-500 focus:border-teal-500">
                    <option value="">Semua Nakes</option>
                    @foreach($nakesList as $nakes)
                        <option value="{{ $nakes->id }}">{{ $nakes->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- FullCalendar Container -->
        <div id="posyanduCalendar" class="min-h-[550px] text-xs"></div>
    </div>

    <!-- Table View Section -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
        <div class="border-b border-slate-100 pb-4">
            <h2 class="text-base font-bold text-[#0B1C30]">Daftar Seluruh Jadwal Agenda</h2>
            <p class="text-xs text-slate-500 mt-1">Daftar agenda posyandu terurut dari jadwal kegiatan terbaru.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                        <th class="py-3 px-3">Kegiatan</th>
                        <th class="py-3 px-3">Tanggal & Waktu</th>
                        <th class="py-3 px-3">Lokasi</th>
                        <th class="py-3 px-3">Nakes Penanggung Jawab</th>
                        <th class="py-3 px-3 text-center">Kuota</th>
                        <th class="py-3 px-3 text-center">Status</th>
                        <th class="py-3 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($jadwalList as $item)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3.5 px-3 font-bold text-slate-800">
                            <a href="{{ route('jadwal.show', $item) }}" class="hover:text-teal-700 transition-colors">
                                {{ $item->judul }}
                            </a>
                        </td>
                        <td class="py-3.5 px-3 text-slate-600">
                            <div class="font-semibold text-slate-700">{{ $item->date ? $item->date->format('d M Y') : '-' }}</div>
                            <div class="text-[11px] text-slate-400">{{ substr($item->hour, 0, 5) }} - {{ substr($item->jam_selesai, 0, 5) }} WIB</div>
                        </td>
                        <td class="py-3.5 px-3 text-slate-600">
                            {{ $item->location }}
                        </td>
                        <td class="py-3.5 px-3 text-slate-700 font-medium">
                            {{ $item->penanggungJawab?->name ?? 'Belum ditentukan' }}
                        </td>
                        <td class="py-3.5 px-3 text-center font-bold text-slate-800">
                            {{ $item->kuota_jadwal }}
                        </td>
                        <td class="py-3.5 px-3 text-center">
                            @if($item->status === 'aktif')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700">
                                    Dibatalkan
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-3 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('jadwal.show', $item) }}" title="Lihat Detail" class="p-1.5 rounded-lg text-slate-600 hover:text-teal-600 hover:bg-teal-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>

                                @can('update', $item)
                                <a href="{{ route('jadwal.edit', $item) }}" title="Ubah Jadwal" class="p-1.5 rounded-lg text-slate-600 hover:text-amber-600 hover:bg-amber-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-10 text-center text-slate-400 text-xs">
                            Belum ada jadwal kegiatan posyandu.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($jadwalList->hasPages())
        <div class="pt-4 border-t border-slate-100">
            {{ $jadwalList->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Detail Event Kalender -->
<div id="eventDetailModal" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 animate-in fade-in duration-150 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <span id="modalStatusBadge" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold"></span>
            <button type="button" onclick="closeEventModal()" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>
        <div>
            <h3 id="modalEventTitle" class="text-base font-extrabold text-[#0B1C30]"></h3>
        </div>
        <div class="space-y-2.5 text-xs text-slate-600">
            <div class="flex items-start gap-2">
                <svg class="w-4 h-4 text-slate-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span id="modalEventTime"></span>
            </div>
            <div class="flex items-start gap-2">
                <svg class="w-4 h-4 text-slate-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span id="modalEventLocation"></span>
            </div>
            <div class="flex items-start gap-2">
                <svg class="w-4 h-4 text-slate-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span>Nakes: <strong id="modalEventNakes" class="text-slate-800"></strong></span>
            </div>
            <div class="flex items-start gap-2">
                <svg class="w-4 h-4 text-slate-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>Kuota: <strong id="modalEventKuota" class="text-slate-800"></strong> balita</span>
            </div>
        </div>
        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
            <button type="button" onclick="closeEventModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors">Tutup</button>
            <a id="modalDetailBtn" href="#" class="px-4 py-2 bg-[#00685F] hover:bg-[#00524B] text-white font-bold rounded-xl text-xs transition-colors">Buka Halaman Detail</a>
        </div>
    </div>
</div>

<!-- FullCalendar 6 Script -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('posyanduCalendar');
        var filterNakesEl = document.getElementById('filterNakes');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'id',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek'
            },
            buttonText: {
                today: 'Hari Ini',
                month: 'Bulan',
                week: 'Minggu'
            },
            events: function(info, successCallback, failureCallback) {
                var url = new URL("{{ route('jadwal.feed') }}", window.location.origin);
                url.searchParams.append('start', info.startStr);
                url.searchParams.append('end', info.endStr);
                if (filterNakesEl.value) {
                    url.searchParams.append('nakes_id', filterNakesEl.value);
                }

                fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Gagal memuat jadwal.');
                    return response.json();
                })
                .then(data => successCallback(data))
                .catch(error => {
                    console.error('Error fetching calendar events:', error);
                    failureCallback(error);
                });
            },
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                var props = info.event.extendedProps;

                document.getElementById('modalEventTitle').textContent = info.event.title;
                document.getElementById('modalEventTime').textContent = 
                    info.event.start.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) +
                    ' (' + info.event.start.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' - ' +
                    (info.event.end ? info.event.end.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) : '') + ' WIB)';
                document.getElementById('modalEventLocation').textContent = props.location || '-';
                document.getElementById('modalEventNakes').textContent = props.nakes_nama || '-';
                document.getElementById('modalEventKuota').textContent = props.kuota || '-';
                
                var badge = document.getElementById('modalStatusBadge');
                if (props.status === 'aktif') {
                    badge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800';
                    badge.textContent = 'Jadwal Aktif';
                } else {
                    badge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700';
                    badge.textContent = 'Dibatalkan';
                }

                document.getElementById('modalDetailBtn').href = "/operasional/jadwal/" + info.event.id;
                
                var modal = document.getElementById('eventDetailModal');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        });

        calendar.render();

        filterNakesEl.addEventListener('change', function() {
            calendar.refetchEvents();
        });
    });

    function closeEventModal() {
        var modal = document.getElementById('eventDetailModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endsection
