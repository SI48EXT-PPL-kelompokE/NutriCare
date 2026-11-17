<?php

namespace Tests;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function userWithPermissions(string $roleName, array $permissions): User
    {
        $role = Role::firstOrCreate(['name_role' => $roleName]);

        foreach ($permissions as $module => $actions) {
            $role->permissions()->updateOrCreate(
                ['module_name' => $module],
                ['name' => $module] + array_fill_keys($actions, true),
            );
        }

        return User::factory()->create(['role_id' => $role->id]);
    }
}
