@props(['value' => ''])

<select {{ $attributes->merge([
    'class' => 'w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500'
]) }}>
    {{ $slot }}
</select>
