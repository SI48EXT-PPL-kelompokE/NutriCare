<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', Rule::in(['system', 'custom'])],
        ]);
        $roles = Role::withCount('users')
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(fn ($match) => $match
                ->where('name_role', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%")))
            ->when(($filters['type'] ?? null) === 'system', fn ($query) => $query->whereIn('name_role', Role::SYSTEM_NAMES))
            ->when(($filters['type'] ?? null) === 'custom', fn ($query) => $query->whereNotIn('name_role', Role::SYSTEM_NAMES))
            ->orderBy('id')->paginate(7)->withQueryString();

        return view('settings.roles.index', compact('roles'));
    }

    public function create(): View
    {
        return view('settings.roles.form', ['role' => new Role]);
    }

    public function store(Request $request): RedirectResponse
    {
        Role::create($this->validateRole($request));

        return to_route('settings.roles.index')->with('success', 'Role pengguna ditambahkan.');
    }

    public function edit(Role $role): View
    {
        return view('settings.roles.form', compact('role'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $role->update($this->validateRole($request, $role));

        return to_route('settings.roles.index')->with('success', 'Role pengguna diperbarui.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $warning = DB::transaction(function () use ($role) {
            $role = Role::query()->lockForUpdate()->findOrFail($role->id);

            if ($role->users()->exists()) {
                return 'Role masih digunakan oleh akun dan tidak dapat dihapus.';
            }
            if ($role->isSystem()) {
                return 'Role bawaan sistem tidak dapat dihapus.';
            }

            $role->permissions()->delete();
            $role->delete();

            return null;
        });

        return to_route('settings.roles.index')->with($warning ? 'warning' : 'success', $warning ?? 'Role pengguna dihapus.');
    }

    private function validateRole(Request $request, ?Role $role = null): array
    {
        $name = Rule::unique('roles', 'name_role');
        if ($role) {
            $name->ignore($role);
        }

        return $request->validate([
            'name_role' => $role?->isSystem()
                ? ['prohibited']
                : ['required', 'string', 'max:255', $name, Rule::notIn(Role::SYSTEM_NAMES)],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
