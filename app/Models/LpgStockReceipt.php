<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LpgStockReceipt extends Model
{
    protected $table = 'lpg_stock_receipts';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_POSTED = 'posted';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_POSTED,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'receipt_number',
        'receipt_date',
        'lpg_supplier_id',
        'branch_id',
        'lpg_storage_unit_id',
        'supplier_invoice_number',
        'delivery_note_number',
        'vehicle_registration_number',
        'driver_name',
        'quantity_kg',
        'unit_cost',
        'total_cost',
        'status',
        'posted_at',
        'stock_movement_id',
        'notes',
        'created_by',
        'updated_by',
        'posted_by',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'quantity_kg' => 'decimal:3',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:4',
        'posted_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(
            LpgSupplier::class,
            'lpg_supplier_id'
        );
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(
            Branch::class,
            'branch_id'
        );
    }

    public function storageUnit(): BelongsTo
    {
        return $this->belongsTo(
            LpgStorageUnit::class,
            'lpg_storage_unit_id'
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

    public function scopeForSupplier(
        Builder $query,
        int $supplierId
    ): Builder {
        return $query->where(
            'lpg_supplier_id',
            $supplierId
        );
    }

    public function scopeForBranch(
        Builder $query,
        int $branchId
    ): Builder {
        return $query->where(
            'branch_id',
            $branchId
        );
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

    public function scopeSearch(
        Builder $query,
        ?string $search
    ): Builder {
        if (blank($search)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search) {
            $q->where(
                'receipt_number',
                'like',
                '%' . $search . '%'
            )
            ->orWhere(
                'supplier_invoice_number',
                'like',
                '%' . $search . '%'
            )
            ->orWhere(
                'delivery_note_number',
                'like',
                '%' . $search . '%'
            )
            ->orWhereHas('supplier', function (Builder $supplierQuery) use ($search) {
                $supplierQuery->where(
                    'supplier_name',
                    'like',
                    '%' . $search . '%'
                );
            });
        });
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
}
