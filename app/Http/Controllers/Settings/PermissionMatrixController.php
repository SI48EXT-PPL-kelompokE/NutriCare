<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PermissionMatrixController extends Controller
{
    private const MODULES = [
        'balita' => 'Data Balita',
        'orang_tua' => 'Data Orang Tua',
        'kms' => 'KMS Digital',
        'imunisasi' => 'Imunisasi',
        'paket_pmt' => 'Paket PMT',
        'artikel' => 'Artikel Edukasi',
        'konseling' => 'Konseling Gizi',
    ];

    private const ACTIONS = [
        'can_view' => 'Lihat',
        'can_create' => 'Tambah',
        'can_edit' => 'Ubah',
        'can_delete' => 'Hapus',
    ];

    private const RESIDENT_ACTIONS = ['artikel' => 'can_view', 'konseling' => 'can_create'];

    public function index(): View
    {
        return view('settings.permissions.index', [
            'roles' => Role::with('permissions')->orderBy('id')->get(),
            'modules' => self::MODULES,
            'actions' => self::ACTIONS,
            'residentActions' => self::RESIDENT_ACTIONS,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'changes' => ['sometimes', 'array'],
            'changes.*' => ['required', 'array:role_id,module,action,enabled'],
            'changes.*.role_id' => ['required', 'integer'],
            'changes.*.module' => ['required', Rule::in(array_keys(self::MODULES))],
            'changes.*.action' => ['required', Rule::in(array_keys(self::ACTIONS))],
            'changes.*.enabled' => ['required', 'boolean'],
        ]);

        DB::transaction(function () use ($data) {
            $changes = collect($data['changes'] ?? []);
            $roles = Role::whereKey($changes->pluck('role_id')->unique()->all())
                ->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            foreach ($changes as $change) {
                $role = $roles->get($change['role_id']);
                abort_unless($role, 409, 'Role sudah berubah. Muat ulang matriks hak akses.');
                abort_if($role->name_role === Role::RESIDENT
                    && (self::RESIDENT_ACTIONS[$change['module']] ?? null) !== $change['action'], 403);

                Permission::upsert(
                    [[
                        'role_id' => $role->id,
                        'module_name' => $change['module'],
                        'name' => self::MODULES[$change['module']],
                        $change['action'] => (bool) $change['enabled'],
                    ]],
                    ['role_id', 'module_name'],
                    [$change['action']],
                );
            }
        });

        return to_route('settings.permissions.index')->with('success', 'Matriks hak akses disimpan.');
    }
}
