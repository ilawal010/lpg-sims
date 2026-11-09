<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LpgSale extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_POSTED = 'posted';
    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_CASH = 'cash';
    public const PAYMENT_BANK_TRANSFER = 'bank_transfer';
    public const PAYMENT_POS = 'pos';

    protected $table = 'lpg_sales';

    protected $fillable = [
        'sale_number',
        'transaction_token',
        'sale_date',
        'branch_id',
        'lpg_storage_unit_id',
        'attendant_id',
        'quantity_kg',
        'selling_price_per_kg',
        'total_amount',
        'cost_price_per_kg',
        'cost_of_goods_sold',
        'gross_profit',
        'payment_method',
        'payment_reference',
        'status',
        'stock_movement_id',
        'posted_at',
        'created_by',
        'updated_by',
        'posted_by',
        'notes',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'quantity_kg' => 'decimal:3',
        'selling_price_per_kg' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'cost_price_per_kg' => 'decimal:4',
        'cost_of_goods_sold' => 'decimal:4',
        'gross_profit' => 'decimal:4',
        'posted_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Payment Methods
    |--------------------------------------------------------------------------
    */

    public static function paymentMethods(): array
    {
        return [
            self::PAYMENT_CASH => 'Cash',
            self::PAYMENT_BANK_TRANSFER => 'Bank Transfer',
            self::PAYMENT_POS => 'POS Card',
        ];
    }

    public function paymentMethodLabel(): string
    {
        return self::paymentMethods()[$this->payment_method]
            ?? 'Not Recorded';
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function storageUnit(): BelongsTo
    {
        return $this->belongsTo(
            LpgStorageUnit::class,
            'lpg_storage_unit_id'
        );
    }

    public function attendant(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'attendant_id'
        );
    }

    public function stockMovement(): BelongsTo
    {
        return $this->belongsTo(
            LpgStockMovement::class,
            'stock_movement_id'
        );
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'posted_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Status Helpers
    |--------------------------------------------------------------------------
    */

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPosted(): bool
    {
        return $this->status === self::STATUS_POSTED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function canBeEdited(): bool
    {
        return $this->isDraft();
    }

    public function canBePosted(): bool
    {
        return $this->isDraft()
            && $this->stock_movement_id === null;
    }

    public function canBeCancelled(): bool
    {
        return $this->isDraft();
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where(
            'status',
            self::STATUS_DRAFT
        );
    }

    public function scopePosted(Builder $query): Builder
    {
        return $query->where(
            'status',
            self::STATUS_POSTED
        );
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where(
            'status',
            self::STATUS_CANCELLED
        );
    }

    public function scopeForBranch(
        Builder $query,
        int $branchId
    ): Builder {
        return $query->where('branch_id', $branchId);
    }

    public function scopeForStorageUnit(
        Builder $query,
        int $storageUnitId
    ): Builder {
        return $query->where(
            'lpg_storage_unit_id',
            $storageUnitId
        );
    }

    public function scopeForAttendant(
        Builder $query,
        int $attendantId
    ): Builder {
        return $query->where(
            'attendant_id',
            $attendantId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Search Scope
    |--------------------------------------------------------------------------
    */

    public function scopeSearch(
        Builder $query,
        ?string $search
    ): Builder {
        if (blank($search)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search) {
            $q->where(
                'sale_number',
                'like',
                '%' . $search . '%'
            )->orWhere(
                'payment_reference',
                'like',
                '%' . $search . '%'
            );
        });
    }
}
