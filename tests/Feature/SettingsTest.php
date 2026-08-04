<?php

use App\Livewire\Settings\Index as SettingsIndex;
use App\Models\Setting;
use App\Models\User;
use App\Support\AppSettings;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingsSeeder;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(SettingsSeeder::class);
});

it('seeds foundation settings defaults', function () {
    expect(AppSettings::get('org_name'))->toBe('PinSA Portfolio')
        ->and(AppSettings::get('base_currency'))->toBe('PKR')
        ->and(AppSettings::get('display_timezone'))->toBe('Asia/Karachi')
        ->and(AppSettings::get('two_factor_required'))->toBeFalse()
        ->and(AppSettings::get('fx_defaults.note'))->not->toBeEmpty();
});

it('allows admins to update settings', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(SettingsIndex::class)
        ->set('org_name', 'Acme Sites')
        ->set('base_currency', 'PKR')
        ->set('display_timezone', 'Asia/Karachi')
        ->set('fx_usd_to_pkr', '278.50')
        ->call('save')
        ->assertHasNoErrors();

    AppSettings::flush();

    expect(AppSettings::get('org_name'))->toBe('Acme Sites')
        ->and(Setting::query()->where('key', 'org_name')->exists())->toBeTrue()
        ->and(AppSettings::get('fx_defaults.USD_to_PKR'))->toBe('278.50');
});

it('prevents users without settings.update from saving', function () {
    $viewerRole = \App\Models\Role::query()->create([
        'name' => 'settings_viewer',
        'label' => 'Settings Viewer',
    ]);
    $viewerRole->permissions()->attach(
        \App\Models\Permission::query()->where('name', 'settings.view')->value('id')
    );

    $viewer = User::factory()->create();
    $viewer->assignRole('settings_viewer');

    Livewire::actingAs($viewer)
        ->test(SettingsIndex::class)
        ->set('org_name', 'Hacked')
        ->call('save')
        ->assertForbidden();
});
