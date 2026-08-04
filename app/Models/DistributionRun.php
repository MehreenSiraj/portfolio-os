<?php

namespace App\Models;

use App\Enums\DistributionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DistributionRun extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'period_month',
        'status',
        'holdback_bps',
        'total_revenue_paisa',
        'total_direct_expense_paisa',
        'total_shared_expense_paisa',
        'total_net_profit_paisa',
        'total_holdback_paisa',
        'total_credited_paisa',
        'ownership_snapshot',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
        'voided_by',
        'voided_at',
        'void_reason',
    ];

    protected function casts(): array
    {
        return [
            'period_month' => 'date',
            'status' => DistributionStatus::class,
            'holdback_bps' => 'integer',
            'total_revenue_paisa' => 'integer',
            'total_direct_expense_paisa' => 'integer',
            'total_shared_expense_paisa' => 'integer',
            'total_net_profit_paisa' => 'integer',
            'total_holdback_paisa' => 'integer',
            'total_credited_paisa' => 'integer',
            'ownership_snapshot' => 'array',
            'approved_at' => 'datetime',
            'voided_at' => 'datetime',
        ];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(DistributionLine::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isEditable(): bool
    {
        return $this->status === DistributionStatus::Draft;
    }

    public function isLocked(): bool
    {
        return $this->status?->isLocked() ?? false;
    }
}
