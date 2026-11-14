@props(['variant' => 'admin', 'items' => null, 'label' => 'Navigasi Klinis'])

@php
    $compact = $variant === 'compact';
    $items ??= [
        ['label' => 'Dashboard', 'icon' => 'dashboard'],
        ['label' => 'Data Balita', 'icon' => 'balita', 'route' => 'kependudukan.balita.index', 'active' => ['kependudukan.*']],
        ['label' => 'KMS Digital', 'icon' => 'kms', 'route' => 'kms.index', 'active' => ['kms.*', 'imunisasi.*']],
        ['label' => 'Logistik PMT', 'icon' => 'package', 'route' => 'pmt.paket.index', 'active' => ['pmt.*']],
        ['label' => 'Jadwal & Rujukan', 'icon' => 'calendar'],
        ['label' => 'Konseling Gizi', 'icon' => 'chat', 'route' => 'konseling.index', 'active' => ['konseling.*']],
        ['label' => 'Artikel & Pengaduan', 'icon' => 'article', 'route' => 'edukasi.artikel.index', 'active' => ['edukasi.*']],
        ['label' => 'Pengaturan', 'icon' => 'settings', 'route' => 'settings.accounts.index', 'permission' => ['akun', 'view'], 'ability' => 'manage-settings', 'active' => ['settings.*']],
    ];

    $icons = [
        'dashboard' => 'M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z',
        'balita' => $compact
            ? 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'
            : 'M22 12a10 10 0 1 1-20 0 10 10 0 0 1 20 0ZM8 14c2 3 6 3 8 0M9 9h.01M15 9h.01',
        'kms' => $compact
            ? 'M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z'
            : 'M22 12h-4l-3 9-6-18-3 9H2',
        'package' => 'M20 7l-8-4-8 4v10l8 4 8-4V7ZM4 7l8 4 8-4M12 11v10',
        'calendar' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z',
        'chat' => 'M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2zM8 10h.01M12 10h.01M16 10h.01',
        'article' => 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6M8 13h8m-8 4h8',
        'settings' => 'M10.3 4.3a1.7 1.7 0 0 1 3.4 0 1.7 1.7 0 0 0 2.6 1.1 1.7 1.7 0 0 1 2.4 2.4 1.7 1.7 0 0 0 1.1 2.6 1.7 1.7 0 0 1 0 3.4 1.7 1.7 0 0 0-1.1 2.6 1.7 1.7 0 0 1-2.4 2.4 1.7 1.7 0 0 0-2.6 1.1 1.7 1.7 0 0 1-3.4 0 1.7 1.7 0 0 0-2.6-1.1 1.7 1.7 0 0 1-2.4-2.4 1.7 1.7 0 0 0-1.1-2.6 1.7 1.7 0 0 1 0-3.4 1.7 1.7 0 0 0 1.1-2.6 1.7 1.7 0 0 1 2.4-2.4 1.7 1.7 0 0 0 2.6-1.1zM15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z',
    ];
@endphp

<aside {{ $attributes->class([
    'w-64 bg-white border-r border-slate-200 shrink-0 flex-col justify-between sticky top-0 h-screen z-30',
    'hidden md:flex' => $compact,
    'flex' => !$compact,
]) }}>
    <div class="min-h-0 overflow-y-auto">
        {{ $brand ?? '' }}

        <nav aria-label="{{ $label }}" @class(['p-4' => $compact, 'px-4 py-5' => !$compact])>
            <p @class(['px-3 mb-2 font-bold uppercase tracking-wider text-slate-400', 'text-[10px]' => $compact, 'text-[11px]' => !$compact])>{{ $label }}</p>
            <ul class="space-y-1">
                @foreach ($items as $item)
                    @continue(isset($item['permission']) && ! auth()->user()?->can('access-module', $item['permission']))
                    @continue(isset($item['ability']) && ! auth()->user()?->can($item['ability']))
                    @php
                        $active = request()->routeIs(...($item['active'] ?? []));
                    @endphp
                    <li>
                        <a href="{{ isset($item['route']) ? route($item['route']) : ($item['href'] ?? '#') }}"
                            @if($active) aria-current="page" @endif
                            @class([
                                'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors',
                                'text-xs' => $compact,
                                'text-sm' => !$compact,
                                'font-bold text-teal-700 bg-teal-50/80 border-l-4 border-teal-600' => $compact && $active,
                                'font-semibold text-[#008f7a] bg-[#ecfdf5]' => !$compact && $active,
                                'font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50' => $compact && !$active,
                                'font-medium text-slate-600 hover:bg-slate-50' => !$compact && !$active,
                            ])>
                            @if(isset($icons[$item['icon'] ?? '']))
                                <svg aria-hidden="true" @class([
                                    'shrink-0',
                                    'w-4 h-4' => $compact,
                                    'w-5 h-5' => !$compact,
                                    'text-teal-600' => $compact && $active,
                                    'text-[#008f7a]' => !$compact && $active,
                                    'text-slate-400' => !$active,
                                ]) viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="{{ $icons[$item['icon']] }}"/>
                                </svg>
                            @endif
                            <span>{{ $item['label'] }}</span>
                            @if($compact && $active)
                                <span class="ml-auto w-1.5 h-1.5 rounded-full bg-teal-600"></span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    </div>

    {{ $footer ?? '' }}
</aside>
