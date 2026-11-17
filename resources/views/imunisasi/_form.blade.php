<div class="space-y-4">
    <!-- Pilih Balita -->
    <x-form.group>
        <x-form.label for="balita_id" required>Balita</x-form.label>
        <x-form.select id="balita_id" name="balita_id" required>
            <option value="">-- Pilih Balita --</option>
            @foreach($balita as $b)
                <option value="{{ $b->id }}" 
                    {{ old('balita_id', $imunisasi->balita_id ?? $balitaIdTerpilih ?? '') == $b->id ? 'selected' : '' }}>
                    {{ $b->name }} - {{ $b->orangTua->name ?? 'Orang Tua Tidak Diketahui' }} ({{ $b->usia_bulan }} bulan)
                </option>
            @endforeach
        </x-form.select>
        <x-form.error name="balita_id" />
    </x-form.group>

    <!-- Jenis Vaksin -->
    <x-form.group>
        <x-form.label for="jenis_vaksin" required>Jenis Vaksin</x-form.label>
        <x-form.select id="jenis_vaksin" name="jenis_vaksin" required>
            <option value="">-- Pilih Jenis Vaksin --</option>
            @foreach($jenisVaksinList as $vaksin)
                <option value="{{ $vaksin }}" {{ old('jenis_vaksin', $imunisasi->jenis_vaksin ?? '') == $vaksin ? 'selected' : '' }}>
                    {{ $vaksin }}
                </option>
            @endforeach
        </x-form.select>
        <x-form.error name="jenis_vaksin" />
    </x-form.group>

    <!-- Tanggal Pemberian -->
    <x-form.group>
        <x-form.label for="tanggal" required>Tanggal Pemberian</x-form.label>
        <x-form.input 
            type="date" 
            id="tanggal" 
            name="tanggal" 
            :value="old('tanggal', isset($imunisasi) ? $imunisasi->tanggal->format('Y-m-d') : '')"
            max="{{ date('Y-m-d') }}"
            required />
        <x-form.error name="tanggal" />
    </x-form.group>

    <!-- Nomor Batch -->
    <x-form.group>
        <x-form.label for="no_batch">Nomor Batch (Opsional)</x-form.label>
        <x-form.input 
            type="text" 
            id="no_batch" 
            name="no_batch" 
            :value="old('no_batch', $imunisasi->no_batch ?? '')"
            placeholder="Contoh: BTC-20261110"
            maxlength="50" />
        <x-form.error name="no_batch" />
    </x-form.group>

    @if(isset($imunisasi))
        <!-- Info Petugas pada Edit (read-only) -->
        <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg">
            <div class="text-xs font-semibold text-slate-600 mb-1">Dicatat Oleh</div>
            <div class="text-sm text-slate-800">{{ $imunisasi->petugas->name ?? 'Tidak diketahui' }}</div>
            <div class="text-xs text-slate-500 mt-0.5">Pada {{ $imunisasi->created_at->isoFormat('D MMMM YYYY, HH:mm') }}</div>
        </div>
    @endif

    <!-- Tombol Aksi -->
    <div class="flex items-center gap-3 pt-4">
        <button type="submit" class="px-6 py-2.5 bg-teal-600 text-white text-sm font-semibold rounded-lg hover:bg-teal-700 transition-colors">
            Simpan
        </button>
        <a href="{{ route('imunisasi.index') }}" class="px-6 py-2.5 bg-slate-100 text-slate-700 text-sm font-semibold rounded-lg hover:bg-slate-200 transition-colors">
            Batal
        </a>
    </div>
</div>
