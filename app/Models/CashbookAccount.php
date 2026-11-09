<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashbookAccount extends Model
{
    protected $fillable = [
        'branch_id',
        'opening_date',
        'opening_balance',
        'bank_opening_balance',
        'created_by',
    ];

    protected $casts = [
        'opening_date' => 'date',
        'opening_balance' => 'decimal:2',
        'bank_opening_balance' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function hasStartedOn(string $date): bool
    {
        return $this->opening_date !== null
            && $this->opening_date->toDateString() <= $date;
    }

    public function hasVerifiedOpeningBalances(): bool
    {
        return $this->opening_date !== null
            && $this->opening_balance !== null
            && $this->bank_opening_balance !== null;
    }
}
