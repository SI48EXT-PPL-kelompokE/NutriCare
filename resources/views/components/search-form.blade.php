@props(['delay' => 350, 'results' => null])

<form
    method="GET"
    x-data="debouncedSearch(@js($delay), @js($results))"
    x-on:input="onSearchInput"
    x-on:compositionend="onSearchInput"
    x-on:change="onFilterChange"
    x-on:submit="onSubmit"
    {{ $attributes }}
>
    {{ $slot }}
    @if ($results)
        <p data-search-error role="alert" hidden class="w-full text-sm text-rose-700"></p>
    @endif
</form>
