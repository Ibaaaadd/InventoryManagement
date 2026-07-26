<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::create([
            'name' => 'Administrator',
            'description' => 'Full system access with all permissions',
        ]);

        Role::create([
            'name' => 'Manager',
            'description' => 'Can manage items, approve stock mutations, and view reports',
        ]);

        Role::create([
            'name' => 'Staff',
            'description' => 'Can create and view stock mutations',
        ]);
    }
}
