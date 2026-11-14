@extends('settings.layout')

@section('settings')
    <div class="flex flex-wrap items-start gap-3 rounded-xl border border-slate-200 bg-white p-4">
        <x-search-form :action="route('settings.accounts.index')" results="account-results" class="flex min-w-0 grow flex-wrap gap-2">
            <x-search-input :value="request('search')" placeholder="Cari nama, NIP, atau username..." wrapper-class="w-full min-w-0 sm:min-w-52 sm:flex-1" />
            <x-form.select name="role_id" aria-label="Filter role" class="sm:w-52" data-search-filter>
                <option value="">Semua Role ({{ $roles->count() }})</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}" @selected((string) request('role_id') === (string) $role->id)>{{ $role->name_role }}</option>
                @endforeach
            </x-form.select>
            <x-form.select name="status" aria-label="Filter status" class="sm:w-40" data-search-filter>
                <option value="">Semua Status</option>
                <option value="1" @selected(request('status') === '1')>Aktif</option>
                <option value="0" @selected(request('status') === '0')>Nonaktif</option>
            </x-form.select>
        </x-search-form>
        @can('access-module', ['akun', 'create'])
            <a href="{{ route('settings.accounts.create') }}" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">+ Tambah Akun</a>
        @endcan
    </div>

    <x-table-card id="account-results">
        <x-data-table class="min-w-[850px]">
            <x-slot:head>
                <tr>
                    @foreach (['Nama Pengguna', 'Identitas / NIP', 'Username', 'Role', 'Status', 'Terakhir Login', 'Aksi'] as $heading)
                        <th scope="col" class="px-4 py-3">{{ $heading }}</th>
                    @endforeach
                </tr>
            </x-slot:head>
            @forelse ($accounts as $account)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-4">
                        <div class="flex items-center gap-3">
                            <span aria-hidden="true" class="grid size-9 shrink-0 place-items-center rounded-full bg-teal-100 text-xs font-semibold text-teal-800">{{ mb_strtoupper(mb_substr($account->name, 0, 2)) }}</span>
                            <div>
                                <a href="{{ route('settings.accounts.show', $account) }}" class="font-semibold text-slate-900 hover:underline">{{ $account->name }}</a>
                                <p class="text-xs text-slate-500">{{ $account->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-4 font-mono text-xs">{{ $account->nip ?: '—' }}</td>
                    <td class="px-4 py-4 font-mono text-xs text-teal-800">{{ $account->username }}</td>
                    <td class="px-4 py-4"><span class="rounded-full bg-sky-50 px-2 py-1 text-xs text-sky-900">{{ $account->role->name_role }}</span></td>
                    <td class="px-4 py-4"><span @class(['rounded-full px-2 py-1 text-xs', 'bg-teal-100 text-teal-900' => $account->active_status, 'bg-slate-100 text-slate-600' => !$account->active_status])>{{ $account->active_status ? 'Aktif' : 'Nonaktif' }}</span></td>
                    <td class="whitespace-nowrap px-4 py-4 text-xs">@include('settings.accounts.last-login')</td>
                    <td class="px-4 py-4">
                        <div class="flex items-center gap-3 whitespace-nowrap">
                            @can('access-module', ['akun', 'edit'])
                                <a href="{{ route('settings.accounts.edit', $account) }}" class="font-semibold text-teal-800 hover:underline">Edit</a>
                            @endcan
                            @can('access-module', ['akun', 'delete'])
                                @unless (auth()->user()->is($account))
                                    <x-settings.delete-dialog :action="route('settings.accounts.destroy', $account)" title="Hapus Akun Petugas" :name="$account->name" />
                                @endunless
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">Tidak ada akun petugas yang cocok.</td></tr>
            @endforelse
        </x-data-table>
        <x-pagination :paginator="$accounts" label="akun" />
    </x-table-card>
@endsection
