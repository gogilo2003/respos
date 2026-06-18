<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $adminRole = Role::where('name', 'admin')->first();

        User::factory()->create([
            'name' => 'System Administrator',
            'username' => 'admin',
            'email' => 'admin@respos.com',
            'password_hash' => Hash::make('password'),
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);
    }
}
