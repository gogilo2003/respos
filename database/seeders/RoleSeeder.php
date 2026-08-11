<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Services\PermissionRegistry;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $registry = new PermissionRegistry;

        $roles = [
            'customer',
            'waiter',
            'kitchen',
            'cashier',
            'manager',
            'admin',
        ];

        foreach ($roles as $roleName) {
            $permissions = $registry->getDefaultPermissionsForRole($roleName);

            $role = Role::firstOrNew(['name' => $roleName]);
            $role->permissions = $permissions;
            $role->save();
        }
    }
}
