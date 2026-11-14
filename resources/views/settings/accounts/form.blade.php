@extends('settings.layout')

@section('settings')
    <form method="POST" action="{{ $account->exists ? route('settings.accounts.update', $account) : route('settings.accounts.store') }}"
        class="max-w-2xl space-y-4 rounded-xl border border-slate-200 bg-white p-6">
        @csrf
        @if ($account->exists)
            @method('PATCH')
        @endif
        <h2 class="text-xl font-bold">{{ $account->exists ? 'Edit Akun Petugas' : 'Tambah Akun Petugas' }}</h2>
        @include('settings.errors')
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach (['name' => 'Nama', 'username' => 'Username', 'email' => 'Email', 'nip' => 'NIP (opsional)'] as $field => $label)
                <label class="space-y-1 text-sm font-semibold">
                    <span>{{ $label }}</span>
                    <x-form.input :name="$field" :value="$account->{$field}" :type="$field === 'email' ? 'email' : 'text'" :required="$field !== 'nip'" />
                </label>
            @endforeach
            <label class="space-y-1 text-sm font-semibold">
                <span>Password</span>
                <input type="password" name="password" autocomplete="new-password" minlength="8" maxlength="72" @required(!$account->exists)
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @if ($account->exists)
                    <span class="text-xs font-normal text-slate-500">Kosongkan untuk mempertahankan password.</span>
                @endif
            </label>
            <label class="space-y-1 text-sm font-semibold">
                <span>Role</span>
                <x-form.select name="role_id" required>
                    <option value="">Pilih role</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" @selected((string) old('role_id', $account->role_id) === (string) $role->id)>{{ $role->name_role }}</option>
                    @endforeach
                </x-form.select>
            </label>
            <label class="space-y-1 text-sm font-semibold">
                <span>Status</span>
                <x-form.select name="active_status" required>
                    <option value="1" @selected(old('active_status', $account->active_status))>Aktif</option>
                    <option value="0" @selected(!old('active_status', $account->active_status))>Nonaktif</option>
                </x-form.select>
            </label>
        </div>
        <x-settings.form-actions :cancel="route('settings.accounts.index')" />
    </form>
@endsection
