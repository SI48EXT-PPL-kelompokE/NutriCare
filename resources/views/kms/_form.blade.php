<div x-data="{ 
    balitaId: '{{ old('balita_id', $kms->balita_id ?? $selectedBalitaId ?? '') }}',
    tanggalUkur: '{{ old('date', isset($kms) ? $kms->date->format('Y-m-d') : now()->format('Y-m-d')) }}',
    birthDate: null,
    usiaBulan: null,
    usiaLabel: '',
    petunjukUkur: '',
    
    init() {
        this.updateUsia();
        this.$watch('balitaId', () => this.updateBalita());
        this.$watch('tanggalUkur', () => this.updateUsia());
    },
    
    updateBalita() {
        const select = document.querySelector('select[name=balita_id]');
        if (!select) return;
        const option = select.options[select.selectedIndex];
        this.birthDate = option ? option.dataset.birthDate : null;
        this.updateUsia();
    },
    
    updateUsia() {
        if (!this.birthDate || !this.tanggalUkur) {
            this.usiaBulan = null;
            this.usiaLabel = '';
            this.petunjukUkur = '';
            return;
        }
        
        const lahir = new Date(this.birthDate);
        const ukur = new Date(this.tanggalUkur);
        
        if (ukur < lahir) {
            this.usiaBulan = null;
            this.usiaLabel = 'Tanggal ukur tidak boleh sebelum tanggal lahir';
            this.petunjukUkur = '';
            return;
        }
        
        // Hitung bulan kalender penuh (sama dengan server)
        let months = (ukur.getFullYear() - lahir.getFullYear()) * 12;
        months += ukur.getMonth() - lahir.getMonth();
        if (ukur.getDate() < lahir.getDate()) {
            months--;
        }
        months = Math.max(0, Math.floor(months));
        
        this.usiaBulan = months;
        
        // Format label usia
        if (months < 12) {
            this.usiaLabel = months + ' bulan';
        } else {
            const years = Math.floor(months / 12);
            const remainingMonths = months % 12;
            if (remainingMonths === 0) {
                this.usiaLabel = months + ' bulan (' + years + ' th)';
            } else {
                this.usiaLabel = months + ' bulan (' + years + ' th ' + remainingMonths + ' bln)';
            }
        }
        
        // Petunjuk pengukuran
        if (months < 24) {
            this.petunjukUkur = '💡 Ukur panjang badan (terlentang)';
        } else {
            this.petunjukUkur = '💡 Ukur tinggi badan (berdiri)';
        }
        
        if (months > 60) {
            this.usiaLabel += ' ⚠️ Usia melebihi 60 bulan';
        }
    }
}">
    <!-- Pilih Balita -->
    <x-form.group>
        <x-form.label for="balita_id" required>
            Balita
        </x-form.label>
        
        <select id="balita_id" name="balita_id" x-model="balitaId" required
                class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 @error('balita_id') border-rose-500 @enderror">
            <option value="">Pilih Balita</option>
            @foreach($balitas as $balita)
                <option value="{{ $balita->id }}" 
                        data-birth-date="{{ $balita->birth_date->format('Y-m-d') }}"
                        {{ old('balita_id', $kms->balita_id ?? $selectedBalitaId ?? '') == $balita->id ? 'selected' : '' }}>
                    {{ $balita->name }} - {{ $balita->orangTua->name ?? 'Orang tua tidak diketahui' }} 
                    ({{ $balita->jenis_kelamin }}, {{ $balita->birth_date->isoFormat('D MMM YYYY') }})
                </option>
            @endforeach
        </select>
        
        <x-form.error name="balita_id" />
    </x-form.group>

    <!-- Tanggal Ukur -->
    <x-form.group>
        <x-form.label for="date" required>
            Tanggal Ukur
        </x-form.label>
        
        <input type="date" id="date" name="date" x-model="tanggalUkur" required
               value="{{ old('date', isset($kms) ? $kms->date->format('Y-m-d') : now()->format('Y-m-d')) }}"
               max="{{ now()->format('Y-m-d') }}"
               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 @error('date') border-rose-500 @enderror">
        
        <x-form.error name="date" />
        
        <!-- Pratinjau Usia -->
        <div x-show="usiaLabel" class="mt-2 p-3 bg-teal-50 border border-teal-200 rounded-lg">
            <div class="text-sm font-semibold text-teal-800">
                <span class="text-xs text-teal-600 uppercase tracking-wide">Usia saat ukur:</span>
                <span x-text="usiaLabel"></span>
            </div>
            <div x-show="petunjukUkur" class="text-xs text-teal-700 mt-1" x-text="petunjukUkur"></div>
        </div>
    </x-form.group>

    <!-- Berat Badan -->
    <x-form.group>
        <x-form.label for="berat_badan" required>
            Berat Badan (kg)
        </x-form.label>
        
        <div class="relative">
            <input type="text" id="berat_badan" name="berat_badan" inputmode="decimal" required
                   value="{{ old('berat_badan', isset($kms) ? str_replace('.', ',', number_format($kms->berat_badan, 1)) : '') }}"
                   placeholder="Contoh: 9,5 atau 10,2"
                   class="w-full px-3 py-2 pr-12 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 @error('berat_badan') border-rose-500 @enderror">
            <span class="absolute right-3 top-2.5 text-sm text-slate-500">kg</span>
        </div>
        
        <p class="mt-1 text-xs text-slate-500">Rentang: 0,5 - 30 kg</p>
        <x-form.error name="berat_badan" />
    </x-form.group>

    <!-- Tinggi/Panjang Badan -->
    <x-form.group>
        <x-form.label for="tinggi_badan" required>
            Tinggi/Panjang Badan (cm)
        </x-form.label>
        
        <div class="relative">
            <input type="text" id="tinggi_badan" name="tinggi_badan" inputmode="decimal" required
                   value="{{ old('tinggi_badan', isset($kms) ? str_replace('.', ',', number_format($kms->tinggi_badan, 1)) : '') }}"
                   placeholder="Contoh: 75,5 atau 80,0"
                   class="w-full px-3 py-2 pr-12 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 @error('tinggi_badan') border-rose-500 @enderror">
            <span class="absolute right-3 top-2.5 text-sm text-slate-500">cm</span>
        </div>
        
        <p class="mt-1 text-xs text-slate-500">Rentang: 30 - 130 cm</p>
        <x-form.error name="tinggi_badan" />
    </x-form.group>

    <!-- Lingkar Lengan Atas (Opsional) -->
    <x-form.group>
        <x-form.label for="lingkar_lengan_atas">
            Lingkar Lengan Atas / LILA (cm) - Opsional
        </x-form.label>
        
        <div class="relative">
            <input type="text" id="lingkar_lengan_atas" name="lingkar_lengan_atas" inputmode="decimal"
                   value="{{ old('lingkar_lengan_atas', isset($kms) && $kms->lingkar_lengan_atas ? str_replace('.', ',', number_format($kms->lingkar_lengan_atas, 1)) : '') }}"
                   placeholder="Contoh: 12,5 (kosongkan jika tidak diukur)"
                   class="w-full px-3 py-2 pr-12 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 @error('lingkar_lengan_atas') border-rose-500 @enderror">
            <span class="absolute right-3 top-2.5 text-sm text-slate-500">cm</span>
        </div>
        
        <p class="mt-1 text-xs text-slate-500">Rentang: 5 - 30 cm</p>
        <x-form.error name="lingkar_lengan_atas" />
    </x-form.group>

    <!-- Tombol Aksi -->
    <div class="flex items-center gap-3 pt-4">
        <button type="submit" class="px-6 py-2.5 bg-teal-600 text-white text-sm font-semibold rounded-lg hover:bg-teal-700 transition-colors">
            <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Simpan Pengukuran
        </button>
        <a href="{{ route('kms.index') }}" class="px-6 py-2.5 bg-slate-100 text-slate-700 text-sm font-semibold rounded-lg hover:bg-slate-200 transition-colors">
            Batal
        </a>
    </div>
</div>
