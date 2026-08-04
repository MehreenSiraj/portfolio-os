<?php

namespace App\Livewire;

use App\Enums\ProjectStatus;
use App\Models\Credential;
use App\Models\Project;
use App\Support\AppSettings;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();
        $canViewProjects = $user?->hasPermission('projects.view');

        $projectsQuery = $canViewProjects
            ? Project::query()->accessibleBy($user)
            : Project::query()->whereRaw('1 = 0');

        $totalProjects = (clone $projectsQuery)->count();
        $byStatus = (clone $projectsQuery)
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $thresholds = AppSettings::get('credential_expiry_thresholds', [30, 14, 7]);
        if (! is_array($thresholds) || $thresholds === []) {
            $thresholds = [30, 14, 7];
        }
        $thresholds = array_map('intval', $thresholds);

        $expiring = collect();
        if ($user?->hasPermission('credentials.view')) {
            $accessibleIds = $user->isAdmin()
                ? null
                : $user->accessibleProjectIds();

            $expiring = Credential::query()
                ->with('project')
                ->when($accessibleIds !== null, fn ($q) => $q->whereIn('project_id', $accessibleIds ?: [0]))
                ->expiringWithin($thresholds)
                ->orderBy('expires_on')
                ->limit(15)
                ->get();
        }

        $recentProjects = $canViewProjects
            ? (clone $projectsQuery)->orderByDesc('updated_at')->limit(5)->get()
            : collect();

        return view('livewire.dashboard', [
            'totalProjects' => $totalProjects,
            'byStatus' => $byStatus,
            'statusOptions' => ProjectStatus::options(),
            'expiring' => $expiring,
            'thresholds' => $thresholds,
            'recentProjects' => $recentProjects,
            'monthRevenuePlaceholder' => 0,
            'monthCostPlaceholder' => 0,
            'canViewProjects' => $canViewProjects,
            'canViewCredentials' => (bool) $user?->hasPermission('credentials.view'),
        ]);
    }
}
