<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'role_id' => ['nullable', 'integer'],
            'status' => ['nullable', Rule::in(['0', '1'])],
        ]);
        $roles = $this->staffRoles();
        $accounts = User::with('role')
            ->whereIn('role_id', $roles->modelKeys())
            ->whereDoesntHave('orangTua')
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(fn ($match) => $match
                ->where('name', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%")
                ->orWhere('nip', 'like', "%{$search}%")))
            ->when(isset($filters['role_id']), fn ($query) => $query->where('role_id', $filters['role_id']))
            ->when(isset($filters['status']), fn ($query) => $query->where('active_status', (bool) $filters['status']))
            ->orderBy('name')->orderBy('id')->paginate(7)->withQueryString();

        return view('settings.accounts.index', compact('accounts', 'roles'));
    }

    public function create(): View
    {
        return view('settings.accounts.form', ['account' => new User(['active_status' => true]), 'roles' => $this->staffRoles()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $values = $this->validateAccount($request);
        $values['password'] = Hash::make($values['password']);
        User::create($values);

        return to_route('settings.accounts.index')->with('success', 'Akun petugas ditambahkan.');
    }

    public function show(User $account): View
    {
        $this->requireStaff($account);

        return view('settings.accounts.show', ['account' => $account]);
    }

    public function edit(User $account): View
    {
        $this->requireStaff($account);

        return view('settings.accounts.form', ['account' => $account, 'roles' => $this->staffRoles()]);
    }

    public function update(Request $request, User $account): RedirectResponse
    {
        $this->requireStaff($account);
        $values = $this->validateAccount($request, $account);

        if ($request->user()->is($account)
            && ((int) $values['role_id'] !== (int) $account->role_id || ! $values['active_status'])) {
            return back()->withInput($request->except('password'))->withErrors([
                'role_id' => 'Role dan status aktif akun yang sedang digunakan harus dipertahankan.',
            ]);
        }

        if (($values['password'] ?? null) === null) {
            unset($values['password']);
        } else {
            $values['password'] = Hash::make($values['password']);
        }

        $account->update($values);

        return to_route('settings.accounts.index')->with('success', 'Akun petugas diperbarui.');
    }

    public function destroy(Request $request, User $account): RedirectResponse
    {
        $this->requireStaff($account);

        if ($request->user()->is($account)) {
            return back()->with('warning', 'Akun yang sedang digunakan tidak dapat dihapus.');
        }

        try {
            DB::transaction(function () use ($account) {
                DB::table('sessions')->where('user_id', $account->id)->delete();
                $account->delete();
            });
        } catch (QueryException $exception) {
            if ((int) ($exception->errorInfo[1] ?? 0) !== 1451
                && ! str_contains($exception->getMessage(), 'FOREIGN KEY constraint failed')) {
                throw $exception;
            }

            return back()->with('warning', 'Akun terhubung dengan catatan lain. Nonaktifkan akun untuk mempertahankan riwayat.');
        }

        return to_route('settings.accounts.index')->with('success', 'Akun petugas dihapus.');
    }

    private function validateAccount(Request $request, ?User $account = null): array
    {
        $username = Rule::unique('users');
        $email = Rule::unique('users');
        if ($account) {
            $username->ignore($account);
            $email->ignore($account);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9._-]+$/', $username],
            'email' => ['required', 'email', 'max:255', $email],
            'password' => [$account ? 'nullable' : 'required', 'string', 'min:8', function ($attribute, $value, $fail) {
                if (strlen($value) > 72 || str_contains($value, "\0")) {
                    $fail('Password maksimal 72 byte dan tidak boleh memuat karakter kosong (NUL).');
                }
            }],
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')->whereNot('name_role', Role::RESIDENT)],
            'active_status' => ['required', 'boolean'],
            'nip' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function staffRoles(): Collection
    {
        return Role::where('name_role', '!=', Role::RESIDENT)->orderBy('name_role')->get();
    }

    private function requireStaff(User $account): void
    {
        abort_if($account->role?->name_role === Role::RESIDENT || $account->orangTua()->exists(), 404);
    }
}
