@extends('settings.layout')

@section('settings')
    <div class="flex flex-wrap items-start gap-3 rounded-xl border border-slate-200 bg-white p-4">
        <x-search-form :action="route('settings.roles.index')" results="role-results" class="flex min-w-0 grow flex-wrap gap-2">
            <x-search-input :value="request('search')" placeholder="Cari nama role atau deskripsi hak akses..." wrapper-class="w-full min-w-0 sm:min-w-52 sm:flex-1" />
            <x-form.select name="type" aria-label="Filter tipe role" class="sm:w-44" data-search-filter>
                <option value="">Semua Tipe</option>
                <option value="system" @selected(request('type') === 'system')>Bawaan Sistem</option>
                <option value="custom" @selected(request('type') === 'custom')>Kustom</option>
            </x-form.select>
        </x-search-form>
        @can('access-module', ['role', 'create'])
            <a href="{{ route('settings.roles.create') }}" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">+ Tambah Role Baru</a>
        @endcan
    </div>

    <x-table-card id="role-results">
        <x-data-table class="min-w-[640px]">
            <x-slot:head>
                <tr>
                    @foreach (['Nama Role', 'Deskripsi Akses', 'Jumlah Akun', 'Tipe', 'Aksi'] as $heading)
                        <th scope="col" class="px-4 py-3">{{ $heading }}</th>
                    @endforeach
                </tr>
            </x-slot:head>
            @forelse ($roles as $role)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-4 font-semibold text-slate-900">{{ $role->name_role }}</td>
                    <td class="max-w-xs px-4 py-4">{{ $role->description ?: '—' }}</td>
                    <td class="whitespace-nowrap px-4 py-4">{{ $role->users_count }} akun</td>
                    <td class="px-4 py-4"><span @class(['rounded-full px-2 py-1 text-xs', 'bg-sky-100 text-sky-900' => $role->isSystem(), 'bg-teal-50 text-teal-900' => !$role->isSystem()])>{{ $role->isSystem() ? 'Bawaan Sistem' : 'Kustom' }}</span></td>
                    <td class="px-4 py-4">
                        <div class="flex items-center gap-3">
                            @can('access-module', ['role', 'edit'])
                                <a href="{{ route('settings.roles.edit', $role) }}" class="font-semibold text-teal-800 hover:underline">Edit</a>
                            @endcan
                            @can('access-module', ['role', 'delete'])
                                @unless ($role->name_role === \App\Models\Role::ADMIN)
                                    <x-settings.delete-dialog :action="route('settings.roles.destroy', $role)" title="Hapus Role Pengguna"
                                        :name="$role->name_role"
                                        :blocked="$role->users_count ? 'Role masih digunakan oleh '.$role->users_count.' akun. Pindahkan akun ke role lain sebelum menghapus.' : ($role->isSystem() ? 'Role bawaan sistem tidak dapat dihapus.' : null)" />
                                @endunless
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Tidak ada role yang cocok.</td></tr>
            @endforelse
        </x-data-table>
        <x-pagination :paginator="$roles" label="role" />
    </x-table-card>
@endsection
