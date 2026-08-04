<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'dashboard.view', 'group' => 'dashboard', 'label' => 'View dashboard'],
            ['name' => 'users.view', 'group' => 'users', 'label' => 'View users'],
            ['name' => 'users.create', 'group' => 'users', 'label' => 'Create users'],
            ['name' => 'users.update', 'group' => 'users', 'label' => 'Update users'],
            ['name' => 'users.deactivate', 'group' => 'users', 'label' => 'Deactivate users'],
            ['name' => 'settings.view', 'group' => 'settings', 'label' => 'View settings'],
            ['name' => 'settings.update', 'group' => 'settings', 'label' => 'Update settings'],
        ];

        foreach ($permissions as $permission) {
            Permission::query()->updateOrCreate(
                ['name' => $permission['name']],
                $permission,
            );
        }

        $roles = [
            'admin' => [
                'label' => 'Admin',
                'description' => 'Full system access',
                'permissions' => Permission::query()->pluck('name')->all(),
            ],
            'partner' => [
                'label' => 'Partner',
                'description' => 'Owner / partner access',
                'permissions' => ['dashboard.view'],
            ],
            'supervisor' => [
                'label' => 'Supervisor',
                'description' => 'Team supervision',
                'permissions' => ['dashboard.view'],
            ],
            'staff' => [
                'label' => 'Staff',
                'description' => 'Day-to-day operators',
                'permissions' => ['dashboard.view'],
            ],
            'accountant' => [
                'label' => 'Accountant',
                'description' => 'Finance operations',
                'permissions' => ['dashboard.view'],
            ],
        ];

        foreach ($roles as $name => $config) {
            $role = Role::query()->updateOrCreate(
                ['name' => $name],
                [
                    'label' => $config['label'],
                    'description' => $config['description'],
                ],
            );

            $permissionIds = Permission::query()
                ->whereIn('name', $config['permissions'])
                ->pluck('id');

            $role->permissions()->sync($permissionIds);
        }
    }
}
