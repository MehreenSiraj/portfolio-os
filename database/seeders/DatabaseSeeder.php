<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            SettingsSeeder::class,
            AdminUserSeeder::class,
            TaskTemplateSeeder::class,
            DemoPortfolioSeeder::class,
            DemoWorkSeeder::class,
        ]);
    }
}
