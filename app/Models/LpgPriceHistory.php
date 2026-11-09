<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LpgPriceHistory extends Model
{
    protected $fillable = [
        'lpg_price_id',
        'previous_price_per_kg',
        'new_price_per_kg',
        'reason',
        'changed_by',
        'effective_at',
    ];

    protected $casts = [
        'previous_price_per_kg' => 'decimal:2',
        'new_price_per_kg' => 'decimal:2',
        'effective_at' => 'datetime',
    ];

    public function price(): BelongsTo
    {
        return $this->belongsTo(LpgPrice::class, 'lpg_price_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
