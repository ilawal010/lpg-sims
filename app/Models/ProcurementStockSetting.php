<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcurementStockSetting extends Model
{
    public const TYPE_LPG = 'lpg';
    public const TYPE_ACCESSORY = 'accessory';

    protected $table = 'procurement_stock_settings';

    protected $fillable = [
        'branch_id',
        'item_type',
        'item_id',
        'minimum_stock',
        'target_stock',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'branch_id' => 'integer',
        'item_id' => 'integer',
        'minimum_stock' => 'decimal:3',
        'target_stock' => 'decimal:3',
        'is_active' => 'boolean',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForLpg($query)
    {
        return $query->where('item_type', self::TYPE_LPG);
    }

    public function scopeForAccessories($query)
    {
        return $query->where('item_type', self::TYPE_ACCESSORY);
    }
}
