@props(['cancel', 'label' => 'Simpan'])

<div {{ $attributes->class(['flex flex-wrap justify-end gap-2 border-t border-slate-200 px-4 py-3']) }}>
    <a href="{{ $cancel }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm hover:bg-slate-50">Batal</a>
    <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">{{ $label }}</button>
</div>
