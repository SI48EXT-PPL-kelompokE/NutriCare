@props(['paginator', 'label' => 'data'])

@php
    $button = 'inline-flex h-8 min-w-8 items-center justify-center rounded-lg border px-2 text-xs font-semibold';
    $link = 'border-slate-200 bg-white text-slate-700 transition-colors hover:border-teal-500 hover:text-teal-700';
    $disabled = 'border-slate-200 text-slate-400';
@endphp

<div {{ $attributes->class(['flex flex-col gap-3 border-t border-slate-200 bg-slate-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between']) }}>
    <p class="text-xs text-slate-600">
        Menampilkan <strong>{{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }}</strong>
        dari <strong>{{ $paginator->total() }}</strong> {{ $label }}
    </p>

    @if ($paginator->hasPages())
        <nav aria-label="Paginasi {{ $label }}" class="flex flex-wrap items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="{{ $button }} {{ $disabled }}" aria-disabled="true" aria-label="Sebelumnya">&lsaquo;</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya" class="{{ $button }} {{ $link }}">&lsaquo;</a>
            @endif

            @foreach (array_filter(\Illuminate\Pagination\UrlWindow::make($paginator)) as $pages)
                @unless ($loop->first)
                    <span class="px-1 text-xs text-slate-500" aria-hidden="true">…</span>
                @endunless
                @foreach ($pages as $page => $url)
                    @if ($page === $paginator->currentPage())
                        <span aria-current="page" class="{{ $button }} border-teal-700 bg-teal-700 text-white">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="{{ $button }} {{ $link }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Berikutnya" class="{{ $button }} {{ $link }}">&rsaquo;</a>
            @else
                <span class="{{ $button }} {{ $disabled }}" aria-disabled="true" aria-label="Berikutnya">&rsaquo;</span>
            @endif
        </nav>
    @endif
</div>
