@extends('layouts.app')

@section('title', 'Jadwal Agenda Posyandu')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-teal-700 uppercase tracking-wider mb-1">
                <span>Operasional</span>
                <span>•</span>
                <span>Posyandu</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-800 tracking-tight">Jadwal Agenda Posyandu</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Kelola kalender kegiatan, alokasi nakes penanggung jawab, dan kuota pengunjung.</p>
        </div>

        <div class="flex items-center gap-3">
            @can('create', App\Models\JadwalPosyandu::class)
            <a href="{{ route('jadwal.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-md shadow-teal-600/20 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah Jadwal</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Flash Notifications -->
    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-xs sm:text-sm flex items-center justify-between">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-bold">&times;</button>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs sm:text-sm">
        <div class="font-bold mb-1">Terjadi kesalahan:</div>
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Calendar View Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-4 border-b border-slate-100 gap-4">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-teal-600"></span>
                <h2 class="text-sm sm:text-base font-bold text-slate-800">Kalender Kegiatan Posyandu</h2>
            </div>
            <!-- Filter Nakes -->
            <div class="flex items-center gap-3">
                <label for="filterNakes" class="text-xs font-semibold text-slate-500">Filter Nakes:</label>
                <select id="filterNakes" class="px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500">
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
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm sm:text-base font-bold text-slate-800">Daftar Seluruh Jadwal</h3>
                <p class="text-xs text-slate-500">Daftar agenda posyandu terurut dari tanggal terbaru</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-200">
                        <th class="py-3 px-6">Kegiatan</th>
                        <th class="py-3 px-6">Tanggal & Waktu</th>
                        <th class="py-3 px-6">Lokasi</th>
                        <th class="py-3 px-6">Nakes Penanggung Jawab</th>
                        <th class="py-3 px-6 text-center">Kuota</th>
                        <th class="py-3 px-6 text-center">Status</th>
                        <th class="py-3 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($jadwalList as $item)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-4 px-6 font-bold text-slate-800">
                            <a href="{{ route('jadwal.show', $item) }}" class="hover:text-teal-600 transition-colors">
                                {{ $item->judul }}
                            </a>
                        </td>
                        <td class="py-4 px-6 text-slate-600">
                            <div class="font-semibold">{{ $item->date ? $item->date->format('d M Y') : '-' }}</div>
                            <div class="text-[11px] text-slate-400">{{ substr($item->hour, 0, 5) }} - {{ substr($item->jam_selesai, 0, 5) }} WIB</div>
                        </td>
                        <td class="py-4 px-6 text-slate-600">
                            {{ $item->location }}
                        </td>
                        <td class="py-4 px-6 text-slate-700 font-medium">
                            {{ $item->penanggungJawab?->name ?? 'Belum ditentukan' }}
                        </td>
                        <td class="py-4 px-6 text-center font-bold text-slate-700">
                            {{ $item->kuota_jadwal }}
                        </td>
                        <td class="py-4 px-6 text-center">
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
                        <td class="py-4 px-6 text-right space-x-2">
                            <a href="{{ route('jadwal.show', $item) }}" class="inline-flex items-center px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-[11px] transition-colors">
                                Detail
                            </a>
                            @can('update', $item)
                            <a href="{{ route('jadwal.edit', $item) }}" class="inline-flex items-center px-2.5 py-1.5 bg-teal-50 hover:bg-teal-100 text-teal-700 font-semibold rounded-lg text-[11px] transition-colors">
                                Ubah
                            </a>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                            Belum ada jadwal kegiatan posyandu.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($jadwalList->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $jadwalList->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Detail Event Kalender -->
<div id="eventDetailModal" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 animate-in fade-in duration-150">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <span id="modalStatusBadge" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold"></span>
            <button type="button" onclick="closeEventModal()" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
        </div>
        <h3 id="modalEventTitle" class="text-base font-extrabold text-slate-800 mb-3"></h3>
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
                <span>Nakes: <strong id="modalEventNakes"></strong></span>
            </div>
            <div class="flex items-start gap-2">
                <svg class="w-4 h-4 text-slate-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>Kuota Pengunjung: <strong id="modalEventKuota"></strong> balita</span>
            </div>
        </div>
        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
            <button type="button" onclick="closeEventModal()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors">Tutup</button>
            <a id="modalDetailBtn" href="#" class="px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-xl text-xs transition-colors">Buka Halaman Detail</a>
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

