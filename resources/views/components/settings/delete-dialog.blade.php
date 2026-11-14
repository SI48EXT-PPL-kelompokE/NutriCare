@props(['action', 'title', 'name', 'blocked' => null])

<div x-data>
    <button type="button" x-on:click="$refs.confirmation.showModal()" class="font-semibold text-rose-700 hover:underline">Hapus</button>
    <dialog x-ref="confirmation" aria-label="{{ $title }}"
        class="m-auto w-[calc(100%-2rem)] max-w-lg rounded-xl border border-slate-200 bg-white p-6 shadow-xl backdrop:bg-slate-900/40 backdrop:backdrop-blur-xs">
        <h2 class="border-b border-slate-200 pb-3 text-xl font-bold text-slate-900">{{ $title }}</h2>
        <p class="mt-4 text-sm">Anda memilih <strong>{{ $name }}</strong>.</p>
        @if ($blocked)
            <p class="mt-4 rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-800">{{ $blocked }}</p>
            <form method="dialog" class="mt-5 flex justify-end">
                <button autofocus class="rounded-lg border border-slate-300 px-3 py-2 text-sm">Mengerti</button>
            </form>
        @else
            <form method="POST" action="{{ $action }}" class="mt-5 flex justify-end gap-2">
                @csrf
                @method('DELETE')
                <button type="button" autofocus x-on:click="$refs.confirmation.close()" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">Batal</button>
                <button type="submit" class="rounded-lg bg-rose-700 px-3 py-2 text-sm font-semibold text-white">Hapus</button>
            </form>
        @endif
    </dialog>
</div>
