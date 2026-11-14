@props([
    'placeholder' => 'Cari…',
    'label' => null,
    'name' => 'search',
    'wrapperClass' => 'w-full sm:w-80',
])

<div class="relative {{ $wrapperClass }}">
    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400" aria-hidden="true">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/>
            <path d="m21 21-4.3-4.3"/>
        </svg>
    </span>
    <input
        type="search"
        name="{{ $name }}"
        placeholder="{{ $placeholder }}"
        aria-label="{{ $label ?? $placeholder }}"
        data-debounce-search
        {{ $attributes->class(['w-full rounded-lg border border-slate-200 bg-white py-2 pl-10 pr-4 text-sm text-slate-800 placeholder-slate-400 transition-colors focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500']) }}
    >
</div>
