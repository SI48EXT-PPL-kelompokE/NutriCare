@extends('settings.layout')

@section('settings')
    <form method="POST" action="{{ $role->exists ? route('settings.roles.update', $role) : route('settings.roles.store') }}"
        class="max-w-2xl space-y-4 rounded-xl border border-slate-200 bg-white p-6">
        @csrf
        @if ($role->exists)
            @method('PATCH')
        @endif
        <h2 class="text-xl font-bold">{{ $role->exists ? 'Edit Role Pengguna' : 'Tambah Role Baru' }}</h2>
        @include('settings.errors')
        <label class="block space-y-1 text-sm font-semibold">
            <span>Nama Role</span>
            @if ($role->isSystem())
                <input type="text" value="{{ $role->name_role }}" readonly class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2">
            @else
                <x-form.input name="name_role" :value="$role->name_role" required maxlength="255" />
            @endif
        </label>
        <label class="block space-y-1 text-sm font-semibold">
            <span>Deskripsi Akses</span>
            <textarea name="description" rows="4" maxlength="1000" class="w-full rounded-lg border border-slate-300 px-3 py-2">{{ old('description', $role->description) }}</textarea>
        </label>
        <x-settings.form-actions :cancel="route('settings.roles.index')" />
    </form>
@endsection
