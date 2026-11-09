<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LpgStockMovement extends Model
{
    protected $table = 'lpg_stock_movements';

    /**
     * Stock movement types.
     */
    public const TYPE_OPENING_STOCK = 'opening_stock';
    public const TYPE_STOCK_RECEIPT = 'stock_receipt';
    public const TYPE_SALE = 'sale';
    public const TYPE_TRANSFER_IN = 'transfer_in';
    public const TYPE_TRANSFER_OUT = 'transfer_out';
    public const TYPE_ADJUSTMENT_IN = 'adjustment_in';
    public const TYPE_ADJUSTMENT_OUT = 'adjustment_out';

    /**
     * Movement directions.
     */
    public const DIRECTION_IN = 'in';
    public const DIRECTION_OUT = 'out';

    /**
     * Permitted movement types and their required directions.
     */
    public const TYPE_DIRECTIONS = [
        self::TYPE_OPENING_STOCK => self::DIRECTION_IN,
        self::TYPE_STOCK_RECEIPT => self::DIRECTION_IN,
        self::TYPE_SALE => self::DIRECTION_OUT,
        self::TYPE_TRANSFER_IN => self::DIRECTION_IN,
        self::TYPE_TRANSFER_OUT => self::DIRECTION_OUT,
        self::TYPE_ADJUSTMENT_IN => self::DIRECTION_IN,
        self::TYPE_ADJUSTMENT_OUT => self::DIRECTION_OUT,
    ];

    /**
     * Attributes allowed for mass assignment.
     *
     * Inventory postings must be performed through the
     * inventory service, not directly from user input.
     */
    protected $fillable = [
        'branch_id',
        'lpg_storage_unit_id',
        'movement_reference',
        'movement_type',
        'direction',
        'quantity_kg',
        'unit_cost',
        'total_cost',
        'movement_date',
        'document_reference',
        'description',
        'transaction_group',
        'created_by',
    ];

    /**
     * Attribute casting.
     */
    protected function casts(): array
    {
        return [
            'branch_id' => 'integer',
            'lpg_storage_unit_id' => 'integer',
            'quantity_kg' => 'decimal:3',
            'unit_cost' => 'decimal:4',
            'total_cost' => 'decimal:4',
            'movement_date' => 'datetime',
            'created_by' => 'integer',
        ];
    }

    /**
     * Branch where the movement occurred.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Storage tank or cylinder affected.
     */
    public function storageUnit(): BelongsTo
    {
        return $this->belongsTo(
            LpgStorageUnit::class,
            'lpg_storage_unit_id'
        );
    }

    /**
     * Staff member who posted the movement.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Filter by branch.
     */
    public function scopeForBranch(
        Builder $query,
        int $branchId
    ): Builder {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Filter by storage unit.
     */
    public function scopeForStorageUnit(
        Builder $query,
        int $storageUnitId
    ): Builder {
        return $query->where(
            'lpg_storage_unit_id',
            $storageUnitId
        );
    }

    /**
     * Incoming stock movements.
     */
    public function scopeIncoming(Builder $query): Builder
    {
        return $query->where(
            'direction',
            self::DIRECTION_IN
        );
    }

    /**
     * Outgoing stock movements.
     */
    public function scopeOutgoing(Builder $query): Builder
    {
        return $query->where(
            'direction',
            self::DIRECTION_OUT
        );
    }

    /**
     * Filter by movement type.
     */
    public function scopeOfType(
        Builder $query,
        string $type
    ): Builder {
        return $query->where('movement_type', $type);
    }

    /**
     * Check whether this movement increases stock.
     */
    public function isIncoming(): bool
    {
        return $this->direction === self::DIRECTION_IN;
    }

    /**
     * Check whether this movement decreases stock.
     */
    public function isOutgoing(): bool
    {
        return $this->direction === self::DIRECTION_OUT;
    }

    /**
     * Signed movement quantity.
     *
     * Positive = stock increase.
     * Negative = stock decrease.
     */
    public function signedQuantity(): string
    {
        $quantity = (string) $this->quantity_kg;

        return $this->isOutgoing()
            ? '-' . $quantity
            : $quantity;
    }

    /**
     * Expected direction for a movement type.
     */
    public static function expectedDirection(
        string $movementType
    ): ?string {
        return self::TYPE_DIRECTIONS[$movementType] ?? null;
    }

    /**
     * Check that the movement type and direction agree.
     */
    public function hasValidDirection(): bool
    {
        $expected = self::expectedDirection(
            $this->movement_type
        );

        return $expected !== null
            && $expected === $this->direction;
    }
}
