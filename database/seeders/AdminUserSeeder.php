<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Default admin credentials (change after first login in production):
 *   email:    admin@example.com
 *   password: password
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        $adminRoleId = Role::query()->where('name', 'admin')->value('id');

        if ($adminRoleId) {
            $admin->roles()->syncWithPivotValues(
                [$adminRoleId],
                ['project_id' => null],
            );
        }
    }
}
