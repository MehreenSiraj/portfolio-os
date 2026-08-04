<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Support\AppSettings;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'org_name' => ['value' => 'PinSA Portfolio'],
            'base_currency' => ['value' => 'PKR'],
            'display_timezone' => ['value' => 'Asia/Karachi'],
            'two_factor_required' => ['value' => false],
            'late_arrival_hour' => ['value' => 10],
            'fx_defaults' => [
                'value' => [
                    'USD_to_PKR' => null,
                    'note' => 'Set rates when recording multi-currency amounts (Milestone 5).',
                ],
            ],
            'credential_expiry_thresholds' => [
                'value' => [30, 14, 7],
            ],
            'credential_expiry_notify_emails' => [
                'value' => [],
            ],
        ];

        foreach ($defaults as $key => $payload) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $payload['value']],
            );
        }

        AppSettings::flush();
    }
}
