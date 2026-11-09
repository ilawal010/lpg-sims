<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Expense extends Model
{
    protected $fillable = [
        'expense_number',
        'branch_id',
        'expense_date',
        'category',
        'description',
        'amount',
        'payment_method',
        'payment_reference',
        'payee',
        'notes',
        'status',
        'created_by',
        'voided_by',
        'voided_at',
        'void_reason',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
        'voided_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function scopeRecorded(Builder $query): Builder
    {
        return $query->where('status', 'recorded');
    }

    public function scopeVoided(Builder $query): Builder
    {
        return $query->where('status', 'voided');
    }

    public function scopeForBranches(
        Builder $query,
        array $branchIds
    ): Builder {
        return $query->whereIn('branch_id', $branchIds);
    }

    public function isRecorded(): bool
    {
        return $this->status === 'recorded';
    }

    public function isVoided(): bool
    {
        return $this->status === 'voided';
    }
}