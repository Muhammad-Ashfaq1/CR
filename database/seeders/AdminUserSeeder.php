<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@construction.test'],
            [
                'name' => 'System Admin',
                'password' => bcrypt('password'),
                'role' => UserRole::Admin,
                'is_active' => true,
            ]
        );
        $admin->syncRoles([UserRole::Admin->value]);

        $owner = User::firstOrCreate(
            ['email' => 'owner@construction.test'],
            [
                'name' => 'Project Owner',
                'password' => bcrypt('password'),
                'role' => UserRole::Owner,
                'is_active' => true,
            ]
        );
        $owner->syncRoles([UserRole::Owner->value]);

        $contractor = User::firstOrCreate(
            ['email' => 'contractor@construction.test'],
            [
                'name' => 'Main Contractor',
                'password' => bcrypt('password'),
                'role' => UserRole::Contractor,
                'is_active' => true,
            ]
        );
        $contractor->syncRoles([UserRole::Contractor->value]);
    }
}
