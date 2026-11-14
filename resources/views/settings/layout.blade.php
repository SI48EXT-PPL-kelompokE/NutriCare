@extends('layouts.app')

@section('title', 'Manajemen Akun & Hak Akses')

@section('content')
    <nav aria-label="Breadcrumb" class="flex flex-wrap gap-2 text-xs text-slate-500">
        <span>Pengaturan</span><span aria-hidden="true">›</span>
        <span class="font-semibold text-slate-800">Manajemen Akun & Hak Akses</span>
    </nav>
    <h1 class="text-2xl font-extrabold text-slate-900">Manajemen Akun & Hak Akses</h1>

    <nav aria-label="Manajemen akun" class="flex flex-wrap gap-x-6 border-b border-slate-200 text-sm">
        @foreach (['accounts' => ['akun', 'Daftar Akun']] as $tab => [$module, $label])
            @can('access-module', [$module, 'view'])
                <a href="{{ route("settings.{$tab}.index") }}"
                    @if (request()->routeIs("settings.{$tab}.*")) aria-current="page" @endif
                    @class([
                        'border-b-2 px-1 py-3',
                        'border-teal-700 font-semibold text-teal-800' => request()->routeIs("settings.{$tab}.*"),
                        'border-transparent text-slate-600 hover:text-teal-800' => !request()->routeIs("settings.{$tab}.*"),
                    ])>{{ $label }}</a>
            @endcan
        @endforeach
    </nav>

    @yield('settings')
@endsection
