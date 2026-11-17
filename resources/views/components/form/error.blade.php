@props(['name'])

@error($name)
    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
@enderror
