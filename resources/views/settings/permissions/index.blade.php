@extends('settings.layout')

@section('settings')
    @include('settings.errors')
    <form method="POST" action="{{ route('settings.permissions.update') }}" id="permission-matrix">
        @csrf
        @method('PUT')
        <x-table-card>
            <x-data-table>
                <x-slot:head>
                    <tr>
                        <th scope="col" class="min-w-48 px-4 py-4">Modul Sistem</th>
                        @foreach ($roles as $role)
                            <th scope="col" class="min-w-44 px-4 py-4">{{ $role->name_role }}</th>
                        @endforeach
                    </tr>
                </x-slot:head>
                @foreach ($modules as $module => $label)
                    <tr>
                        <th scope="row" class="bg-slate-50/60 px-4 py-4 text-left align-top font-semibold text-slate-900">{{ $label }}</th>
                        @foreach ($roles as $role)
                            @php
                                $permission = $role->permissions->firstWhere('module_name', $module);
                            @endphp
                            <td class="px-4 py-4 align-top">
                                <div class="grid grid-cols-2 gap-x-3 gap-y-2 text-xs">
                                    @foreach ($actions as $action => $actionLabel)
                                        @php
                                            $restricted = $role->name_role === \App\Models\Role::RESIDENT && ($residentActions[$module] ?? null) !== $action;
                                        @endphp
                                        <label @class(['flex items-center gap-1.5', 'text-slate-400' => $restricted])>
                                            <input type="checkbox" class="size-4 shrink-0 accent-teal-700"
                                                aria-label="{{ $role->name_role }} · {{ $label }} · {{ $actionLabel }}"
                                                data-role="{{ $role->id }}" data-module="{{ $module }}" data-action="{{ $action }}"
                                                @checked(!$restricted && $permission?->{$action})
                                                @disabled($restricted || !auth()->user()->can('access-module', ['hak_akses', 'edit']))>
                                            {{ $actionLabel }}
                                        </label>
                                    @endforeach
                                </div>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </x-data-table>
            @can('access-module', ['hak_akses', 'edit'])
                <x-settings.form-actions :cancel="route('settings.permissions.index')" label="Simpan Matriks Hak Akses" class="bg-slate-50" />
            @endcan
        </x-table-card>
    </form>
@endsection

@push('scripts')
    <script>
        document.getElementById('permission-matrix').addEventListener('formdata', ({ target, formData }) => {
            const changed = [...target.querySelectorAll('input[type=checkbox]:enabled')]
                .filter(input => input.checked !== input.defaultChecked);

            changed.forEach(({ dataset, checked }, index) => {
                formData.append(`changes[${index}][role_id]`, dataset.role);
                formData.append(`changes[${index}][module]`, dataset.module);
                formData.append(`changes[${index}][action]`, dataset.action);
                formData.append(`changes[${index}][enabled]`, checked ? '1' : '0');
            });
        });
    </script>
@endpush
