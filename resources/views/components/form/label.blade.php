@props(['for' => null, 'required' => false])

<label {{ $attributes->merge(['class' => 'block text-xs font-semibold text-slate-700 mb-1.5']) }} @if($for) for="{{ $for }}" @endif>
    {{ $slot }}
    @if($required)
        <span class="text-rose-500">*</span>
    @endif
</label>
