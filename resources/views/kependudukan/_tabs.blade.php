@php
    $tabs = [
        ['route' => 'kependudukan.balita.index', 'module' => 'balita', 'label' => 'Daftar Balita', 'count' => $totalBalita],
        ['route' => 'kependudukan.orang-tua.index', 'module' => 'orang_tua', 'label' => 'Data Orang Tua / Ibu Hamil', 'count' => $totalOrangTua],
    ];
@endphp

<nav aria-label="Kependudukan" class="flex flex-wrap items-end gap-2 border-b border-slate-200 pb-px text-xs font-semibold">
    @foreach ($tabs as $tab)
        @can('access-module', [$tab['module'], 'view'])
        @php
            $active = request()->routeIs($tab['route']);
        @endphp
        <a href="{{ route($tab['route']) }}"
            @if ($active) aria-current="page" @endif
            @class([
                'inline-flex items-center gap-2 px-4 py-2.5 rounded-t-xl transition-colors',
                'text-teal-700 font-bold border-b-2 border-teal-600 bg-white' => $active,
                'text-slate-600 hover:text-slate-900 hover:bg-slate-50' => !$active,
            ])>
            {{ $tab['label'] }}
            <span @class(['px-2 py-0.5 rounded-full', 'bg-teal-50' => $active, 'bg-slate-100' => !$active])>{{ $tab['count'] }}</span>
        </a>
        @endcan
    @endforeach
</nav>
