@extends('layouts.app')

@section('title', 'Buat Paket PMT Baru')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header & Back Navigation -->
    <div class="flex items-center justify-between">
        <a href="{{ route('pmt.paket.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-emerald-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali ke Master Paket PMT</span>
        </a>
        <span class="text-xs font-semibold px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-full border border-emerald-200">
            Form Pembuatan Paket
        </span>
    </div>

    <!-- Error Validation Alert -->
    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm space-y-1">
            <div class="font-bold flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Mohon periksa kembali input formulir Anda:</span>
            </div>
            <ul class="list-disc list-inside text-xs space-y-0.5 pl-6">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('pmt.paket.store') }}" id="paketForm" class="space-y-6">
        @csrf

        <!-- 1. Metadata Paket Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-5">
            <div>
                <h2 class="text-base font-bold text-slate-800">1. Informasi Master Paket</h2>
                <p class="text-xs text-slate-500">Definisikan identitas paket makanan tambahan dan batasan usia balita sasaran.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Nama Paket -->
                <div class="md:col-span-2">
                    <label for="nama_paket_pmt" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Nama Paket PMT <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="nama_paket_pmt" name="nama_paket_pmt" value="{{ old('nama_paket_pmt') }}" required
                           placeholder="Contoh: PMT Pemulihan Tinggi Protein 6-23 Bulan"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                </div>

                <!-- Satuan Paket -->
                <div>
                    <label for="unit_type" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Satuan Penyaluran <span class="text-rose-500">*</span>
                    </label>
                    <select id="unit_type" name="unit_type" required
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                        <option value="paket" {{ old('unit_type') == 'paket' ? 'selected' : '' }}>Paket</option>
                        <option value="porsi" {{ old('unit_type') == 'porsi' ? 'selected' : '' }}>Porsi</option>
                        <option value="kotak" {{ old('unit_type') == 'kotak' ? 'selected' : '' }}>Kotak</option>
                        <option value="bungkus" {{ old('unit_type') == 'bungkus' ? 'selected' : '' }}>Bungkus</option>
                    </select>
                </div>

                <!-- Status Aktif -->
                <div class="flex items-center pt-6">
                    <label class="relative flex items-center gap-3 cursor-pointer select-none">
                        <input type="checkbox" name="status" value="1" {{ old('status', '1') == '1' ? 'checked' : '' }} class="w-5 h-5 rounded-md text-emerald-600 focus:ring-emerald-500 border-slate-300">
                        <div>
                            <span class="text-sm font-bold text-slate-800">Status Paket Aktif</span>
                            <span class="block text-xs text-slate-500">Paket dapat langsung dipilih saat proses distribusi posyandu.</span>
                        </div>
                    </label>
                </div>

                <!-- Rentang Usia Sasaran (KF-26) -->
                <div class="md:col-span-2 p-4 rounded-xl bg-emerald-50/50 border border-emerald-100 space-y-3">
                    <div class="flex items-start gap-2 text-xs text-emerald-800">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>
                            <strong>Standar Intervensi Gizi:</strong> Sasaran PMT Balita berada pada rentang <strong>6 s.d. 59 bulan</strong>. Bayi usia 0-5 bulan wajib mendapatkan ASI eksklusif.
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="min_age_target" class="block text-xs font-bold text-slate-700 mb-1">
                                Usia Minimal Sasaran (Bulan) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" id="min_age_target" name="min_age_target" value="{{ old('min_age_target', 6) }}" min="6" max="59" required
                                   class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all">
                        </div>
                        <div>
                            <label for="max_age_target" class="block text-xs font-bold text-slate-700 mb-1">
                                Usia Maksimal Sasaran (Bulan) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" id="max_age_target" name="max_age_target" value="{{ old('max_age_target', 23) }}" min="6" max="59" required
                                   class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all">
                        </div>
                    </div>
                </div>

                <!-- Deskripsi -->
                <div class="md:col-span-2">
                    <label for="deskripsi" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Deskripsi / Petunjuk Penggunaan
                    </label>
                    <textarea id="deskripsi" name="deskripsi" rows="2"
                              placeholder="Keterangan tambahan mengenai paket makanan tambahan ini..."
                              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">{{ old('deskripsi') }}</textarea>
                </div>
            </div>
        </div>

        <!-- 2. Komposisi Bahan Makanan (KF-24 & KF-25) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-800">2. Komposisi Bahan Makanan</h2>
                    <p class="text-xs text-slate-500">Tambahkan bahan-bahan makanan penyusun. Nilai gizi paket akan dihitung otomatis.</p>
                </div>
                <button type="button" id="btnAddRow" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold text-xs rounded-xl border border-emerald-200 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>+ Tambah Bahan</span>
                </button>
            </div>

            <!-- Table of Dynamic Items -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm" id="komposisiTable">
                    <thead class="bg-slate-50 text-slate-600 text-xs font-semibold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-3">Bahan Makanan</th>
                            <th class="py-2.5 px-3 w-36">Takaran / Jumlah</th>
                            <th class="py-2.5 px-3 w-28">Satuan</th>
                            <th class="py-2.5 px-3 text-right w-28">Subtotal Energi</th>
                            <th class="py-2.5 px-3 w-12 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" id="komposisiBody">
                        <!-- Rows injected via JS or old inputs -->
                    </tbody>
                </table>
            </div>

            <!-- Anti-duplicate warning -->
            <div id="duplicateWarning" class="hidden p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-medium">
                Peringatan: Terdapat bahan makanan yang dipilih lebih dari satu kali. Pastikan setiap bahan bersifat unik dalam satu paket.
            </div>
        </div>

        <!-- 3. Live Nutrition Calculator Summary Card (KF-25) -->
        <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-2xl p-6 shadow-md space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Perhitungan Otomatis Real-Time (KF-25)</span>
                    <h3 class="text-lg font-bold text-white mt-0.5">Estimasi Akumulasi Nilai Nutrisi</h3>
                </div>
                <div class="text-right">
                    <span class="text-xs text-slate-400 block">Total Energi Paket:</span>
                    <span class="text-2xl font-black text-amber-400" id="liveTotalEnergi">0.00</span>
                    <span class="text-xs text-slate-300">Kcal</span>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3 pt-2 border-t border-slate-700/80">
                <div class="bg-slate-800/80 border border-slate-700 rounded-xl p-3 text-center">
                    <span class="block text-[11px] text-blue-400 font-semibold uppercase">Protein</span>
                    <span class="text-lg font-bold text-white" id="liveTotalProtein">0.00</span>
                    <span class="text-[10px] text-slate-400">gram</span>
                </div>
                <div class="bg-slate-800/80 border border-slate-700 rounded-xl p-3 text-center">
                    <span class="block text-[11px] text-rose-400 font-semibold uppercase">Lemak</span>
                    <span class="text-lg font-bold text-white" id="liveTotalLemak">0.00</span>
                    <span class="text-[10px] text-slate-400">gram</span>
                </div>
                <div class="bg-slate-800/80 border border-slate-700 rounded-xl p-3 text-center">
                    <span class="block text-[11px] text-violet-400 font-semibold uppercase">Karbohidrat</span>
                    <span class="text-lg font-bold text-white" id="liveTotalKarbo">0.00</span>
                    <span class="text-[10px] text-slate-400">gram</span>
                </div>
            </div>
        </div>

        <!-- Submit & Actions -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('pmt.paket.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 text-sm font-semibold transition-all">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-md shadow-emerald-600/20 transition-all hover:shadow-lg">
                Simpan Paket PMT
            </button>
        </div>

    </form>

</div>

<!-- Template Row Komposisi -->
<template id="komposisiRowTemplate">
    <tr class="komposisi-row hover:bg-slate-50/50 transition-colors">
        <td class="py-2.5 px-3">
            <select name="komposisi[INDEX][barang_id]" required class="select-barang w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                <option value="">-- Pilih Bahan Makanan --</option>
                @foreach ($barangs as $barang)
                    <option value="{{ $barang->id }}"
                            data-unit="{{ $barang->unit_type }}"
                            data-energi="{{ $barang->energi_kcal }}"
                            data-protein="{{ $barang->protein }}"
                            data-lemak="{{ $barang->lemak }}"
                            data-karbo="{{ $barang->karbohidrat }}">
                        {{ $barang->nama }} ({{ $barang->kategori }})
                    </option>
                @endforeach
            </select>
        </td>
        <td class="py-2.5 px-3">
            <input type="number" step="0.01" min="0.01" name="komposisi[INDEX][jumlah]" required placeholder="Contoh: 100"
                   class="input-jumlah w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
        </td>
        <td class="py-2.5 px-3">
            <input type="text" name="komposisi[INDEX][unit_type]" required value="gram"
                   class="input-unit w-full px-3 py-2 bg-slate-100 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-600 focus:outline-none transition-all">
        </td>
        <td class="py-2.5 px-3 text-right font-medium text-amber-700 text-xs sm:text-sm subtotal-energi">
            0 Kcal
        </td>
        <td class="py-2.5 px-3 text-center">
            <button type="button" class="btn-remove-row p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus baris">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>
        </td>
    </tr>
</template>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const komposisiBody = document.getElementById('komposisiBody');
        const template = document.getElementById('komposisiRowTemplate');
        const btnAddRow = document.getElementById('btnAddRow');
        const duplicateWarning = document.getElementById('duplicateWarning');

        let rowIndex = 0;

        function addRow(defaultBarangId = '', defaultJumlah = '', defaultUnit = 'gram') {
            const clone = template.content.cloneNode(true);
            const row = clone.querySelector('tr');

            // Replace INDEX placeholder
            row.innerHTML = row.innerHTML.replaceAll('INDEX', rowIndex);

            komposisiBody.appendChild(row);

            const selectBarang = row.querySelector('.select-barang');
            const inputJumlah = row.querySelector('.input-jumlah');
            const inputUnit = row.querySelector('.input-unit');

            if (defaultBarangId) {
                selectBarang.value = defaultBarangId;
            }
            if (defaultJumlah) {
                inputJumlah.value = defaultJumlah;
            }
            if (defaultUnit) {
                inputUnit.value = defaultUnit;
            }

            selectBarang.addEventListener('change', function () {
                const selectedOption = selectBarang.options[selectBarang.selectedIndex];
                if (selectedOption && selectedOption.dataset.unit) {
                    inputUnit.value = selectedOption.dataset.unit;
                }
                calculateTotals();
            });

            inputJumlah.addEventListener('input', calculateTotals);

            row.querySelector('.btn-remove-row').addEventListener('click', function () {
                if (komposisiBody.querySelectorAll('.komposisi-row').length > 1) {
                    row.remove();
                    calculateTotals();
                } else {
                    alert('Paket minimal harus memiliki 1 komposisi bahan makanan.');
                }
            });

            rowIndex++;
            calculateTotals();
        }

        function calculateTotals() {
            let totalEnergi = 0;
            let totalProtein = 0;
            let totalLemak = 0;
            let totalKarbo = 0;

            const selectedIds = [];
            let hasDuplicate = false;

            const rows = komposisiBody.querySelectorAll('.komposisi-row');
            rows.forEach(row => {
                const select = row.querySelector('.select-barang');
                const inputJumlah = row.querySelector('.input-jumlah');
                const subtotalEl = row.querySelector('.subtotal-energi');

                const barangId = select.value;
                const jumlah = parseFloat(inputJumlah.value) || 0;

                if (barangId) {
                    if (selectedIds.includes(barangId)) {
                        hasDuplicate = true;
                    } else {
                        selectedIds.push(barangId);
                    }

                    const opt = select.options[select.selectedIndex];
                    const energi = parseFloat(opt.dataset.energi) || 0;
                    const protein = parseFloat(opt.dataset.protein) || 0;
                    const lemak = parseFloat(opt.dataset.lemak) || 0;
                    const karbo = parseFloat(opt.dataset.karbo) || 0;

                    // Faktor per 100 gram/satuan
                    const faktor = jumlah / 100;
                    const subtotalKcal = energi * faktor;

                    totalEnergi += subtotalKcal;
                    totalProtein += protein * faktor;
                    totalLemak += lemak * faktor;
                    totalKarbo += karbo * faktor;

                    subtotalEl.textContent = subtotalKcal.toFixed(1) + ' Kcal';
                } else {
                    subtotalEl.textContent = '0 Kcal';
                }
            });

            document.getElementById('liveTotalEnergi').textContent = totalEnergi.toFixed(2);
            document.getElementById('liveTotalProtein').textContent = totalProtein.toFixed(2);
            document.getElementById('liveTotalLemak').textContent = totalLemak.toFixed(2);
            document.getElementById('liveTotalKarbo').textContent = totalKarbo.toFixed(2);

            if (hasDuplicate) {
                duplicateWarning.classList.remove('hidden');
            } else {
                duplicateWarning.classList.add('hidden');
            }
        }

        btnAddRow.addEventListener('click', () => addRow());

        // Inisialisasi baris pertama (atau jika ada old input)
        @if (old('komposisi'))
            @foreach (old('komposisi') as $oldItem)
                addRow('{{ $oldItem['barang_id'] ?? '' }}', '{{ $oldItem['jumlah'] ?? '' }}', '{{ $oldItem['unit_type'] ?? 'gram' }}');
            @endforeach
        @else
            addRow();
        @endif
    });
</script>
@endpush
@endsection
