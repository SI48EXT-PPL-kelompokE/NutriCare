<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Logistik PMT') - NutriCare</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    @stack('head')
</head>
<body class="min-h-screen flex antialiased text-slate-800">

    <!-- LEFT SIDEBAR (Sesuaikan dengan Mockup) -->
    <x-sidebar variant="compact">
        <x-slot:brand>
            <div class="h-20 flex items-center px-6 border-b border-slate-100">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-teal-600 flex items-center justify-center text-white shadow-md shadow-teal-600/20">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-lg font-extrabold tracking-tight text-teal-800 block">NutriCare</span>
                        <span class="text-[9px] font-bold text-slate-400 tracking-wider uppercase block -mt-1">Monitoring & Intervensi</span>
                    </div>
                </a>
            </div>
        </x-slot:brand>
        <x-slot:footer>
            <div class="p-4 border-t border-slate-100">
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-teal-100 text-teal-800 font-bold flex items-center justify-center text-xs">
                        {{ mb_strtoupper(mb_substr(auth()->user()?->name ?? 'NutriCare', 0, 2)) }}
                    </div>
                    <div class="overflow-hidden">
                        <span class="text-xs font-bold text-slate-800 block truncate">{{ auth()->user()?->name ?? 'NutriCare' }}</span>
                        <span class="text-[10px] text-teal-700 font-semibold block">{{ auth()->user()?->role?->name_role ?? 'Portal Edukasi' }}</span>
                    </div>
                </div>
            </div>
        </x-slot:footer>
    </x-sidebar>

    <!-- RIGHT CONTENT AREA -->
    <div class="flex-1 flex flex-col min-w-0">

        <!-- TOP BAR (Sesuaikan dengan Mockup) -->
        <header class="bg-white border-b border-slate-200 h-20 px-6 sm:px-8 flex items-center justify-between gap-4 sticky top-0 z-20">
            <x-search-form :action="route('kependudukan.balita.index')" role="search" aria-label="Pencarian Balita dari header" class="flex min-w-0 max-w-lg flex-1 items-center gap-2">
                <x-search-input placeholder="Cari nama balita atau NIK orang tua..." wrapper-class="min-w-0 flex-1" />
                <button type="submit" class="hidden rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 sm:inline-flex">Cari</button>
            </x-search-form>

            <!-- Top Right Action Controls -->
            <div class="ml-auto flex items-center gap-4">
                <!-- Ekspor Dropdown Button -->
                <button type="button" class="hidden sm:inline-flex items-center gap-2 px-3.5 py-2 bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span>Ekspor</span>
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Notification Bell -->
                <div class="relative">
                    <button type="button" class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </button>
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-rose-500"></span>
                </div>

                <!-- User Profile Header -->
                <div class="flex items-center gap-3 pl-2 border-l border-slate-200">
                    <div class="w-9 h-9 rounded-full bg-teal-600 text-white font-bold flex items-center justify-center text-xs shadow-xs">
                        {{ mb_strtoupper(mb_substr(auth()->user()?->name ?? 'NutriCare', 0, 2)) }}
                    </div>
                    <div class="hidden lg:block text-left">
                        <span class="text-xs font-bold text-slate-800 block">{{ auth()->user()?->name ?? 'NutriCare' }}</span>
                        <span class="text-[10px] text-slate-400 block font-medium">{{ auth()->user()?->role?->name_role ?? 'Portal Edukasi' }}</span>
                    </div>
                </div>
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg px-3 py-2 text-sm font-semibold text-teal-800 hover:bg-teal-50">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-teal-800 hover:bg-teal-50">Masuk</a>
                @endauth
            </div>
        </header>

        <!-- MAIN PAGE CONTAINER -->
        <main class="flex-1 p-6 sm:p-8 max-w-7xl w-full mx-auto space-y-6">

            @if (request()->routeIs('pmt.*'))
            <!-- Breadcrumbs (Sesuai Mockup) -->
            <div class="flex items-center gap-2 text-xs font-medium text-slate-400">
                <a href="#" class="hover:text-slate-600">Data Posyandu</a>
                <span>/</span>
                <a href="#" class="hover:text-slate-600">Logistik & Farmasi</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">Logistik PMT</span>
            </div>

            <!-- Header Section (Sesuai Mockup) -->
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Logistik PMT</h1>
                <p class="text-xs text-slate-500 mt-1">Puskesmas Kecamatan Sukajadi - Gudang Farmasi & Distribusi Posyandu</p>
            </div>

            <!-- Subnavigation Tabs (Sesuai Mockup) -->
            <div class="flex items-center gap-2 border-b border-slate-200 text-xs font-semibold text-slate-500 pb-px">
                <!-- Tab Stok Gudang (Sprint 2) -->
                <span class="inline-flex items-center gap-2 px-4 py-2.5 rounded-t-xl text-slate-400 cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span>Stok Gudang</span>
                </span>

                <!-- Tab Master Paket PMT (ACTIVE - PBI 8) -->
                <a href="{{ route('pmt.paket.index') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-t-xl text-teal-700 font-bold border-b-2 border-teal-600 bg-white transition-colors">
                    <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <span>Master Paket (Aktif)</span>
                </a>

                <!-- Tab Riwayat Distribusi (Sprint 3) -->
                <span class="inline-flex items-center gap-2 px-4 py-2.5 rounded-t-xl text-slate-400 cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <span>Riwayat Distribusi</span>
                </span>
            </div>
            @endif

            <!-- Flash Notifications -->
            @if (session('success'))
                <div class="p-4 rounded-xl bg-teal-50 border border-teal-200 text-teal-800 text-xs font-semibold flex items-center gap-3">
                    <svg class="w-4 h-4 text-teal-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if (session('warning'))
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-semibold flex items-center gap-3">
                    <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>{{ session('warning') }}</span>
                </div>
            @endif

            @if (session('session_status'))
                <p role="status" class="rounded-xl border border-teal-200 bg-teal-50 p-4 text-sm text-teal-800">{{ session('session_status') }}</p>
            @endif

            @yield('content')

        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-slate-200 py-5 px-8 mt-auto text-xs text-slate-400 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                &copy; {{ date('Y') }} NutriCare Kelompok E &bull; PPL Ekstensi Telkom University
            </div>
            @if (request()->routeIs('pmt.*'))
            <div>
                Modul Logistik PMT &bull; Pengembang: <strong>Muhammad Fadilyas</strong>
            </div>
            @endif
        </footer>

    </div>

    @stack('scripts')
</body>
</html>
