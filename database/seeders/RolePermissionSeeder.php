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
            // Milestone 2
            ['name' => 'projects.view', 'group' => 'projects', 'label' => 'View projects'],
            ['name' => 'projects.create', 'group' => 'projects', 'label' => 'Create projects'],
            ['name' => 'projects.update', 'group' => 'projects', 'label' => 'Update projects'],
            ['name' => 'projects.delete', 'group' => 'projects', 'label' => 'Delete projects'],
            ['name' => 'projects.manage_ownership', 'group' => 'projects', 'label' => 'Manage project ownership'],
            ['name' => 'credentials.view', 'group' => 'credentials', 'label' => 'View credentials'],
            ['name' => 'credentials.manage', 'group' => 'credentials', 'label' => 'Manage credentials'],
            ['name' => 'credentials.reveal', 'group' => 'credentials', 'label' => 'Reveal credential secrets'],
            // Milestone 3 — Work
            ['name' => 'tasks.view', 'group' => 'tasks', 'label' => 'View tasks'],
            ['name' => 'tasks.create', 'group' => 'tasks', 'label' => 'Create tasks'],
            ['name' => 'tasks.update', 'group' => 'tasks', 'label' => 'Update own tasks'],
            ['name' => 'tasks.assign', 'group' => 'tasks', 'label' => 'Assign tasks'],
            ['name' => 'tasks.approve', 'group' => 'tasks', 'label' => 'Approve tasks'],
            ['name' => 'tasks.submit', 'group' => 'tasks', 'label' => 'Submit tasks'],
            ['name' => 'articles.view', 'group' => 'articles', 'label' => 'View articles'],
            ['name' => 'articles.create', 'group' => 'articles', 'label' => 'Create articles'],
            ['name' => 'articles.update', 'group' => 'articles', 'label' => 'Update own articles'],
            ['name' => 'articles.approve', 'group' => 'articles', 'label' => 'Approve articles'],
            ['name' => 'links.view', 'group' => 'links', 'label' => 'View links'],
            ['name' => 'links.create', 'group' => 'links', 'label' => 'Create links'],
            ['name' => 'links.update', 'group' => 'links', 'label' => 'Update own links'],
            ['name' => 'links.approve', 'group' => 'links', 'label' => 'Approve links'],
            ['name' => 'task_templates.manage', 'group' => 'tasks', 'label' => 'Manage task templates'],
            // Milestone 4 — People
            ['name' => 'login_history.view', 'group' => 'people', 'label' => 'View login history'],
            ['name' => 'attendance.view', 'group' => 'people', 'label' => 'View attendance'],
            ['name' => 'attendance.manage', 'group' => 'people', 'label' => 'Mark leave / holiday'],
            ['name' => 'work_logs.view', 'group' => 'people', 'label' => 'View work logs'],
            ['name' => 'work_logs.manage', 'group' => 'people', 'label' => 'Write own work logs'],
            ['name' => 'scorecards.view', 'group' => 'people', 'label' => 'View performance scorecards'],
            ['name' => 'people.view_team', 'group' => 'people', 'label' => 'View team people data'],
        ];

        foreach ($permissions as $permission) {
            Permission::query()->updateOrCreate(
                ['name' => $permission['name']],
                $permission,
            );
        }

        $workView = ['tasks.view', 'articles.view', 'links.view'];
        $workStaff = [
            ...$workView,
            'tasks.update',
            'tasks.submit',
            'articles.create',
            'articles.update',
            'links.create',
            'links.update',
        ];
        $workSupervisor = [
            ...$workStaff,
            'tasks.create',
            'tasks.assign',
            'tasks.approve',
            'articles.approve',
            'links.approve',
            'credentials.view',
        ];

        $peopleSelf = [
            'login_history.view',
            'attendance.view',
            'work_logs.view',
            'work_logs.manage',
            'scorecards.view',
        ];
        $peopleTeam = [
            ...$peopleSelf,
            'people.view_team',
            'attendance.manage',
        ];

        $roles = [
            'admin' => [
                'label' => 'Admin',
                'description' => 'Full system access',
                'permissions' => Permission::query()->pluck('name')->all(),
            ],
            'partner' => [
                'label' => 'Partner',
                'description' => 'Owner / partner access',
                'permissions' => [
                    'dashboard.view',
                    'projects.view',
                    // Read-only work visibility on owned/accessible projects — no write
                    ...$workView,
                    ...$peopleSelf,
                ],
            ],
            'supervisor' => [
                'label' => 'Supervisor',
                'description' => 'Team supervision',
                'permissions' => [
                    'dashboard.view',
                    'projects.view',
                    'projects.update',
                    ...$workSupervisor,
                    ...$peopleTeam,
                ],
            ],
            'staff' => [
                'label' => 'Staff',
                'description' => 'Day-to-day operators',
                'permissions' => [
                    'dashboard.view',
                    'projects.view',
                    ...$workStaff,
                    ...$peopleSelf,
                ],
            ],
            'accountant' => [
                'label' => 'Accountant',
                'description' => 'Finance operations',
                'permissions' => [
                    'dashboard.view',
                    'projects.view',
                    // No task workflow per brief
                ],
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
