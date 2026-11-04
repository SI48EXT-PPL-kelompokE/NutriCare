<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Logistik PMT') - NutriCare</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN for guaranteed rendering -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        teal: {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            500: '#14b8a6',
                            600: '#0d9488',
                            700: '#0f766e',
                            800: '#115e59',
                            900: '#134e4a',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
        }
    </style>
</head>
<body class="min-h-screen flex antialiased text-slate-800">

    <!-- LEFT SIDEBAR (Sesuaikan dengan Mockup) -->
    <aside class="w-64 bg-white border-r border-slate-200 shrink-0 hidden md:flex flex-col justify-between sticky top-0 h-screen z-30">
        <div>
            <!-- Logo Section -->
            <div class="h-20 flex items-center px-6 border-b border-slate-100">
                <a href="{{ route('pmt.paket.index') }}" class="flex items-center gap-3">
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

            <!-- Navigation Links -->
            <div class="p-4 space-y-6">
                <div>
                    <span class="text-[10px] font-bold tracking-wider text-slate-400 uppercase px-3 block mb-2">Navigasi Klinis</span>
                    <nav class="space-y-1">
                        
                        <!-- Dashboard -->
                        <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-colors">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                            <span>Dashboard</span>
                        </a>

                        <!-- Data Balita -->
                        <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-colors">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <span>Data Balita</span>
                        </a>

                        <!-- KMS Digital -->
                        <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-colors">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                            </svg>
                            <span>KMS Digital</span>
                        </a>

                        <!-- Logistik PMT (ACTIVE SPRINT MENU) -->
                        <a href="{{ route('pmt.paket.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-bold text-teal-700 bg-teal-50/80 border-l-4 border-teal-600 transition-colors">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                <span>Logistik PMT</span>
                            </div>
                            <span class="w-1.5 h-1.5 rounded-full bg-teal-600"></span>
                        </a>

                        <!-- Jadwal & Rujukan -->
                        <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-colors">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>Jadwal & Rujukan</span>
                        </a>

                        <!-- Artikel & Pengaduan -->
                        <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-colors">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span>Artikel & Pengaduan</span>
                        </a>

                        <!-- Pengaturan -->
                        <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-colors">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>Pengaturan</span>
                        </a>

                    </nav>
                </div>
            </div>
        </div>

        <!-- Sidebar Footer -->
        <div class="p-4 border-t border-slate-100">
            <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-teal-100 text-teal-800 font-bold flex items-center justify-center text-xs">
                    FY
                </div>
                <div class="overflow-hidden">
                    <span class="text-xs font-bold text-slate-800 block truncate">Muhammad Fadilyas</span>
                    <span class="text-[10px] text-teal-700 font-semibold block">Kader Posyandu</span>
                </div>
            </div>
        </div>
    </aside>

    <!-- RIGHT CONTENT AREA -->
    <div class="flex-1 flex flex-col min-w-0">

        <!-- TOP BAR (Sesuaikan dengan Mockup) -->
        <header class="bg-white border-b border-slate-200 h-20 px-6 sm:px-8 flex items-center justify-between gap-4 sticky top-0 z-20">
            <!-- Global Search Bar -->
            <div class="flex-1 max-w-lg relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" placeholder="Cari NIK, nama balita, atau Posyandu..." 
                       class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white transition-all">
            </div>

            <!-- Top Right Action Controls -->
            <div class="flex items-center gap-4">
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
                        SR
                    </div>
                    <div class="hidden lg:block text-left">
                        <span class="text-xs font-bold text-slate-800 block">dr. Siti Rahmawati</span>
                        <span class="text-[10px] text-slate-400 block font-medium">Kepala Puskesmas</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- MAIN PAGE CONTAINER -->
        <main class="flex-1 p-6 sm:p-8 max-w-7xl w-full mx-auto space-y-6">

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

            @yield('content')

        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-slate-200 py-5 px-8 mt-auto text-xs text-slate-400 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                &copy; {{ date('Y') }} NutriCare Kelompok E &bull; PPL Ekstensi Telkom University
            </div>
            <div>
                Modul Logistik PMT &bull; Pengembang: <strong>Muhammad Fadilyas</strong>
            </div>
        </footer>

    </div>

    @stack('scripts')
</body>
</html>
