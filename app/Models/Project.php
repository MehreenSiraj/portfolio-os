<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'domain',
        'niche',
        'cms',
        'start_date',
        'acquisition_cost_paisa',
        'status',
        'notes',
        'monthly_link_target',
        'monthly_link_budget_paisa',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'acquisition_cost_paisa' => 'integer',
            'status' => ProjectStatus::class,
            'monthly_link_target' => 'integer',
            'monthly_link_budget_paisa' => 'integer',
        ];
    }

    public function owners(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_owners')
            ->withPivot('share_bps')
            ->withTimestamps();
    }

    public function teamMembers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user')
            ->withPivot('assignment_note')
            ->withTimestamps();
    }

    public function credentials(): HasMany
    {
        return $this->hasMany(Credential::class);
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function links(): HasMany
    {
        return $this->hasMany(Link::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Month's revenue in paisa. Stub until Money milestone.
     */
    public function monthRevenuePaisa(): int
    {
        return 0;
    }

    /**
     * Month's cost in paisa from auto-created work expenses (M3 hooks).
     * Full shared-cost P&L lands in M5.
     */
    public function monthCostPaisa(): int
    {
        if (! Schema::hasTable('expenses')) {
            return 0;
        }

        $start = now('UTC')->startOfMonth()->toDateString();
        $end = now('UTC')->endOfMonth()->toDateString();

        return (int) $this->expenses()
            ->whereBetween('expense_date', [$start, $end])
            ->sum('amount_paisa');
    }

    public function monthProfitPaisa(): int
    {
        return $this->monthRevenuePaisa() - $this->monthCostPaisa();
    }

    /**
     * Open (non-approved) tasks for this project.
     */
    public function openTasksCount(): int
    {
        if (! Schema::hasTable('tasks')) {
            return 0;
        }

        return (int) $this->tasks()
            ->open()
            ->where(function ($q) {
                $q->where('is_recurrence_source', false)
                    ->orWhereNull('is_recurrence_source');
            })
            ->count();
    }

    public function ownershipShareTotalBps(): int
    {
        return (int) $this->owners()->sum('project_owners.share_bps');
    }

    public function acquisitionCostFormatted(): string
    {
        return number_format($this->acquisition_cost_paisa / 100, 2).' PKR';
    }

    /**
     * @param  Builder<Project>  $query
     * @return Builder<Project>
     */
    public function scopeAccessibleBy(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        $projectIds = $user->accessibleProjectIds();

        return $query->whereIn($query->getModel()->getQualifiedKeyName(), $projectIds);
    }
}
