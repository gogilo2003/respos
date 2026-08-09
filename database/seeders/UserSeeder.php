<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'System Administrator',
                'username' => 'admin',
                'email' => 'admin@respos.com',
                'role' => 'admin',
            ],
            [
                'name' => 'Restaurant Manager',
                'username' => 'manager',
                'email' => 'manager@respos.com',
                'role' => 'manager',
            ],
            [
                'name' => 'Head Cashier',
                'username' => 'cashier',
                'email' => 'cashier@respos.com',
                'role' => 'cashier',
            ],
            [
                'name' => 'Floor Waiter',
                'username' => 'waiter',
                'email' => 'waiter@respos.com',
                'role' => 'waiter',
            ],
            [
                'name' => 'Chef Kitchen',
                'username' => 'kitchen',
                'email' => 'kitchen@respos.com',
                'role' => 'kitchen',
            ],
            [
                'name' => 'Test Customer',
                'username' => 'customer',
                'email' => 'customer@respos.com',
                'role' => 'customer',
            ],
        ];

        foreach ($users as $userData) {
            $role = Role::firstOrCreate(['name' => $userData['role']]);

            User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'username' => $userData['username'],
                    'password' => Hash::make('password'),
                    'role_id' => $role->id,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
