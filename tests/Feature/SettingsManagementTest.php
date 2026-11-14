<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SettingsManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_staff_accounts_and_last_login_is_shown(): void
    {
        $admin = $this->administrator();
        $role = Role::create(['name_role' => 'Petugas Gizi']);
        $role->permissions()->create(['name' => 'Artikel', 'module_name' => 'artikel', 'can_view' => true]);
        $data = [
            'name' => 'Petugas Uji',
            'username' => 'petugas.uji',
            'email' => 'petugas@example.test',
            'password' => 'Password-Uji-Akun!',
            'role_id' => $role->id,
            'active_status' => '1',
        ];

        $this->actingAs($admin)->get(route('settings.accounts.create'))->assertOk();
        $this->post(route('settings.accounts.store'), $data)->assertRedirect(route('settings.accounts.index'));
        $account = User::where('username', $data['username'])->firstOrFail();
        $hash = $account->password;
        $this->assertTrue(Hash::check($data['password'], $hash));
        $this->assertNull($account->last_login);
        $this->get(route('settings.accounts.show', $account))->assertOk()->assertSee('Belum masuk')->assertDontSee($hash);
        $this->get(route('settings.accounts.edit', $account))->assertOk()->assertDontSee($hash);
        $this->patch(route('settings.accounts.update', $account), array_replace($data, [
            'name' => 'Petugas Gizi Uji',
            'password' => '',
        ]))->assertRedirect(route('settings.accounts.index'));
        $this->assertSame($hash, $account->fresh()->password);

        $this->post(route('logout'));
        $this->post(route('login.store'), ['username' => $data['username'], 'password' => $data['password']])
            ->assertRedirect(route('edukasi.artikel.index'));
        $this->assertNotNull($account->fresh()->last_login);
        $this->actingAs($admin)->get(route('settings.accounts.index', ['search' => 'Petugas Gizi']))
            ->assertOk()->assertSee('Petugas Gizi Uji')->assertSee('WIB');
        $this->delete(route('settings.accounts.destroy', $account))->assertRedirect(route('settings.accounts.index'));
        $this->assertModelMissing($account);
    }

    public function test_roles_can_be_edited_and_only_unassigned_custom_roles_can_be_deleted(): void
    {
        $this->actingAs($this->administrator());
        $this->get(route('settings.roles.create'))->assertOk();
        $this->post(route('settings.roles.store'), ['name_role' => 'Petugas Baru', 'description' => 'Deskripsi awal'])
            ->assertRedirect(route('settings.roles.index'));
        $role = Role::whereNameRole('Petugas Baru')->firstOrFail();
        $this->get(route('settings.roles.edit', $role))->assertOk()->assertSee('Deskripsi awal');
        $this->patch(route('settings.roles.update', $role), ['name_role' => 'Tenaga Gizi', 'description' => 'Deskripsi baru'])
            ->assertRedirect(route('settings.roles.index'));
        $this->get(route('settings.roles.index', ['search' => 'Deskripsi baru']))->assertOk()->assertSee('Tenaga Gizi');

        $member = User::factory()->create(['role_id' => $role->id, 'active_status' => false]);
        $this->delete(route('settings.roles.destroy', $role))->assertSessionHas('warning');
        $this->assertModelExists($role);
        $member->delete();
        $role->permissions()->create(['name' => 'Artikel', 'module_name' => 'artikel', 'can_view' => true]);
        $this->delete(route('settings.roles.destroy', $role))->assertSessionHas('success');
        $this->assertModelMissing($role);
        $this->assertDatabaseMissing('permissions', ['role_id' => $role->id]);
    }

    private function administrator(): User
    {
        $crud = ['can_view', 'can_create', 'can_edit', 'can_delete'];

        return $this->userWithPermissions(Role::ADMIN, ['akun' => $crud, 'role' => $crud, 'hak_akses' => $crud]);
    }
}
