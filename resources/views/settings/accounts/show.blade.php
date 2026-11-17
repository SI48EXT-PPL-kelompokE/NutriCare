@extends('settings.layout')

@section('settings')
    <section class="max-w-2xl rounded-xl border border-slate-200 bg-white p-6">
        <h2 class="text-xl font-bold">{{ $account->name }}</h2>
        <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
            @foreach (['Username' => $account->username, 'Email' => $account->email, 'NIP' => $account->nip ?: '—', 'Role' => $account->role->name_role, 'Status' => $account->active_status ? 'Aktif' : 'Nonaktif'] as $label => $value)
                <div><dt class="text-slate-500">{{ $label }}</dt><dd class="font-semibold">{{ $value }}</dd></div>
            @endforeach
            <div><dt class="text-slate-500">Terakhir Login</dt><dd class="font-semibold">@include('settings.accounts.last-login')</dd></div>
        </dl>
        <div class="mt-6 flex gap-4 text-sm font-semibold">
            <a href="{{ route('settings.accounts.index') }}" class="text-slate-600 hover:underline">Kembali</a>
            @can('access-module', ['akun', 'edit'])
                <a href="{{ route('settings.accounts.edit', $account) }}" class="text-teal-800 hover:underline">Edit Akun</a>
            @endcan
        </div>
    </section>
@endsection
