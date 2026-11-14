<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk ke Akun - NutriCare</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-linear-to-b from-[#cdf2f5] to-white font-sans text-slate-900 antialiased">
    <main class="mx-auto grid min-h-screen max-w-6xl items-center px-4 py-6 sm:px-8 lg:px-16">
        <div class="grid overflow-hidden rounded-2xl shadow-2xl shadow-slate-300/55 md:min-h-[min(800px,calc(100vh-3rem))] md:grid-cols-[2fr_3fr]">
            <aside class="flex flex-col justify-center bg-linear-to-br from-[#baf2f1] via-[#e3f8fa] to-[#e8f2fc] p-7 md:px-10 md:pt-28">
                <div class="grid size-16 place-items-center rounded-[20px] bg-linear-to-br from-teal-700 to-teal-500 shadow-md md:size-[84px]">
                    <svg class="size-11 md:size-14" viewBox="0 0 56 56" aria-hidden="true">
                        <path fill="#30c79f" d="M28 35C13 29 9 16 12 8c9-1 17 6 19 19z"/>
                        <path fill="#f0fdfa" d="M29 37C23 21 28 8 39 4c9 10 4 25-10 33z"/>
                        <path fill="none" stroke="#99f6e4" stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M23 44h4l2-5 3 9 3-4h4"/>
                        <path fill="none" stroke="#fff" stroke-linecap="round" stroke-width="3.5" d="M10 43c9 15 27 16 37 0"/>
                    </svg>
                </div>
                <p class="mt-2 text-[28px] font-black tracking-tight">Nutri<span class="text-teal-600">Care</span></p>
                <p class="mt-2 max-w-sm text-sm leading-relaxed text-slate-600 md:text-[15px]">
                    Platform terpadu untuk pemantauan gizi dan intervensi stunting balita. Membantu Posyandu dan Puskesmas dalam memantau pertumbuhan anak secara presisi dan berkelanjutan.
                </p>
            </aside>

            <section class="flex items-center justify-center bg-white px-6 py-9 sm:px-10 md:py-14" aria-labelledby="login-heading">
                <div class="w-full max-w-[420px]">
                    <p class="text-[11px] font-bold uppercase tracking-[.15em] text-teal-800">Akses Portal NutriCare</p>
                    <h1 id="login-heading" class="mt-4 text-3xl font-bold tracking-tight sm:text-4xl">Masuk ke Akun</h1>
                    <p class="mt-4 text-[15px] leading-relaxed text-slate-600">Silakan masukkan kredensial resmi terdaftar Anda untuk verifikasi.</p>

                    @if (session('session_status'))
                        <p role="status" class="mt-5 rounded-lg bg-teal-50 p-3 text-sm text-teal-900">{{ session('session_status') }}</p>
                    @endif

                    <form method="POST" action="{{ route('login.store', absolute: false) }}" class="mt-8 space-y-3 md:mt-10">
                        @csrf

                        <div>
                            <label for="username" class="mb-2 block text-sm font-semibold">Username</label>
                            <div class="relative">
                                <svg class="pointer-events-none absolute top-1/2 left-4 size-4 -translate-y-1/2 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="11" r="2"/><path d="M6 16c0-3 6-3 6 0m3-5h3m-3 4h3"/></svg>
                                <input id="username" name="username" type="text" value="{{ old('username') }}" autocomplete="username" autocapitalize="none" spellcheck="false" required
                                    @error('username') aria-invalid="true" aria-describedby="username-error" @enderror
                                    class="h-[53px] w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pr-4 pl-11 text-[15px] focus:border-teal-600 focus:ring-2 focus:ring-teal-700/25 focus:outline-none">
                            </div>
                            @error('username') <p id="username-error" class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                        </div>

                        <div x-data="{ visible: false }">
                            <label for="password" class="mb-2 block text-sm font-semibold">Password</label>
                            <div class="relative">
                                <svg class="pointer-events-none absolute top-1/2 left-4 size-4 -translate-y-1/2 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3m-4 4v3"/></svg>
                                <input id="password" name="password" type="password" x-bind:type="visible ? 'text' : 'password'" autocomplete="current-password" required
                                    @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                                    class="h-[53px] w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pr-12 pl-11 text-[15px] focus:border-teal-600 focus:ring-2 focus:ring-teal-700/25 focus:outline-none">
                                <button type="button" x-on:click="visible = !visible" aria-controls="password" x-bind:aria-pressed="visible" aria-label="Tampilkan password"
                                    class="absolute top-1/2 right-2 grid size-9 -translate-y-1/2 place-items-center rounded-md text-slate-500 hover:bg-slate-200 focus-visible:outline-2 focus-visible:outline-teal-700">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2 12s4-6 10-6 10 6 10 6-4 6-10 6-10-6-10-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                                </button>
                            </div>
                            @error('password') <p id="password-error" class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                        </div>

                        @error('login')
                            <div role="alert" class="flex items-start gap-3 px-3 pt-3 text-red-700">
                                <svg class="mt-0.5 size-4 shrink-0" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10" fill="currentColor"/><path d="M12 7v6m0 3v1" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
                                <div><p class="text-sm font-bold">Autentikasi Gagal</p><p class="mt-1 text-sm leading-relaxed">{{ $message }}</p></div>
                            </div>
                        @enderror

                        <button type="submit" class="mt-3 inline-flex h-14 w-full items-center justify-center gap-2 rounded-xl bg-teal-800 px-4 font-bold text-white shadow-lg shadow-teal-900/20 transition-colors hover:bg-teal-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-700">
                            Masuk ke Sistem
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14m-7-7 7 7-7 7"/></svg>
                        </button>
                    </form>

                    <p class="mt-8 text-center text-[13px] leading-relaxed text-slate-600 md:mt-10">
                        Belum terdaftar? <strong class="font-semibold text-teal-800">Hubungi Kader Posyandu</strong> atau Tim IT Puskesmas wilayah Anda.
                    </p>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
