@props(['status' => null, 'size' => 'sm'])

@php
    $tone = $status instanceof \App\Enums\StatusGizi ? $status->tone() : 'neutral';
    $label = $status instanceof \App\Enums\StatusGizi ? $status->label() : '-';
    $classes = match ($tone) {
        'success' => 'bg-emerald-100 text-emerald-800',
        'warning' => 'bg-amber-100 text-amber-800',
        'danger' => 'bg-rose-100 text-rose-800',
        'info' => 'bg-blue-100 text-blue-800',
        default => 'bg-slate-100 text-slate-600',
    };
    $dot = match ($tone) {
        'success' => 'bg-emerald-500',
        'warning' => 'bg-amber-500',
        'danger' => 'bg-rose-500',
        'info' => 'bg-blue-500',
        default => 'bg-slate-400',
    };
@endphp

@if($status)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full font-semibold ' . ($size === 'lg' ? 'px-3 py-1.5 text-sm' : 'px-2 py-1 text-xs') . ' ' . $classes]) }}>
        <span class="w-1.5 h-1.5 rounded-full {{ $dot }}"></span>
        {{ $label }}
    </span>
@else
    <span {{ $attributes->merge(['class' => 'text-sm text-slate-400']) }}>-</span>
@endif
