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
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'acquisition_cost_paisa' => 'integer',
            'status' => ProjectStatus::class,
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

    /**
     * Month's revenue in paisa. Stub until Money milestone.
     */
    public function monthRevenuePaisa(): int
    {
        return 0;
    }

    /**
     * Month's cost in paisa. Uses acquisition cost as placeholder until Money.
     * Portfolio list shows 0 for recurring month cost until M5.
     */
    public function monthCostPaisa(): int
    {
        return 0;
    }

    public function monthProfitPaisa(): int
    {
        return $this->monthRevenuePaisa() - $this->monthCostPaisa();
    }

    /**
     * Open tasks count. Stub until Work milestone.
     */
    public function openTasksCount(): int
    {
        if (Schema::hasTable('tasks')) {
            // Reserved for M3
            return 0;
        }

        return 0;
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
