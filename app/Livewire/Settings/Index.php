<?php

namespace App\Livewire\Settings;

use App\Support\AppSettings;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Settings')]
class Index extends Component
{
    public string $org_name = '';

    public string $base_currency = 'PKR';

    public string $display_timezone = 'Asia/Karachi';

    public bool $two_factor_required = false;

    public string $fx_usd_to_pkr = '';

    public string $fx_note = '';

    public function mount(): void
    {
        abort_unless(Auth::user()?->hasPermission('settings.view'), 403);

        $this->org_name = (string) AppSettings::get('org_name', 'PinSA Portfolio');
        $this->base_currency = (string) AppSettings::get('base_currency', 'PKR');
        $this->display_timezone = (string) AppSettings::get('display_timezone', 'Asia/Karachi');
        $this->two_factor_required = (bool) AppSettings::get('two_factor_required', false);

        $fx = AppSettings::get('fx_defaults', []);
        $this->fx_usd_to_pkr = isset($fx['USD_to_PKR']) && $fx['USD_to_PKR'] !== null
            ? (string) $fx['USD_to_PKR']
            : '';
        $this->fx_note = (string) ($fx['note'] ?? '');
    }

    public function save(): void
    {
        abort_unless(Auth::user()?->hasPermission('settings.update'), 403);

        $validated = $this->validate([
            'org_name' => ['required', 'string', 'max:255'],
            'base_currency' => ['required', 'string', 'size:3'],
            'display_timezone' => ['required', 'string', 'timezone'],
            'two_factor_required' => ['boolean'],
            'fx_usd_to_pkr' => ['nullable', 'string', 'max:32'],
            'fx_note' => ['nullable', 'string', 'max:500'],
        ]);

        AppSettings::set('org_name', $validated['org_name']);
        AppSettings::set('base_currency', strtoupper($validated['base_currency']));
        AppSettings::set('display_timezone', $validated['display_timezone']);
        AppSettings::set('two_factor_required', (bool) $validated['two_factor_required']);
        AppSettings::set('fx_defaults', [
            'USD_to_PKR' => $validated['fx_usd_to_pkr'] !== '' ? $validated['fx_usd_to_pkr'] : null,
            'note' => $validated['fx_note'] ?: 'Set rates when recording multi-currency amounts (Milestone 5).',
        ]);

        session()->flash('status', 'Settings saved.');
    }

    public function render()
    {
        return view('livewire.settings.index');
    }
}
