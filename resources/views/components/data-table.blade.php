@props(['bodyId' => null])

<div class="overflow-x-auto">
    <table {{ $attributes->class(['w-full border-collapse text-left text-sm text-slate-700']) }}>
        <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-600">
            {{ $head }}
        </thead>
        <tbody @if ($bodyId) id="{{ $bodyId }}" @endif class="divide-y divide-slate-100">
            {{ $slot }}
        </tbody>
        {{ $foot ?? '' }}
    </table>
</div>
