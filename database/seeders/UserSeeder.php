<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'Administrator')->first();
        $managerRole = Role::where('name', 'Manager')->first();
        $staffRole = Role::where('name', 'Staff')->first();

        User::create([
            'name' => 'Admin User',
            'email' => 'admin@ims.test',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
        ]);

        User::create([
            'name' => 'Manager User',
            'email' => 'manager@ims.test',
            'password' => Hash::make('password'),
            'role_id' => $managerRole->id,
        ]);

        User::create([
            'name' => 'Staff User',
            'email' => 'staff@ims.test',
            'password' => Hash::make('password'),
            'role_id' => $staffRole->id,
        ]);
    }
}
