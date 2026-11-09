<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LpgPrice extends Model
{
    protected $fillable = [
        'selling_price_per_kg',
        'updated_by',
        'effective_at',
    ];

    protected $casts = [
        'selling_price_per_kg' => 'decimal:2',
        'effective_at' => 'datetime',
    ];

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(LpgPriceHistory::class);
    }

    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }
}
