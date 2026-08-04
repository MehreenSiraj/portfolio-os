<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'expense_category_id',
        'amount_paisa',
        'currency',
        'description',
        'expense_date',
        'source_type',
        'source_id',
        'created_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount_paisa' => 'integer',
            'expense_date' => 'date',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function amountFormatted(): string
    {
        return number_format($this->amount_paisa / 100, 2).' PKR';
    }
}
