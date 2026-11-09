<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LpgStorageUnit extends Model
{
    protected $table = 'lpg_storage_units';

    protected $fillable = [
        'branch_id',
        'storage_code',
        'storage_type',
        'storage_name',
        'capacity_kg',
        'manufacturer',
        'serial_number',
        'installation_date',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'capacity_kg' => 'decimal:3',
            'installation_date' => 'date',
        ];
    }

    /**
     * Branch where the storage unit is located.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * User who registered the storage unit.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    /**
     * Inventory ledger entries belonging to this storage unit.
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(
            LpgStockMovement::class,
            'lpg_storage_unit_id'
        );
    }

    /**
     * User who last updated the storage unit.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }

    /**
     * Filter storage units belonging to a branch.
     */
    public function scopeForBranch(
        Builder $query,
        int $branchId
    ): Builder {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Filter operationally active storage units.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Filter bulk LPG storage tanks.
     */
    public function scopeTanks(Builder $query): Builder
    {
        return $query->where('storage_type', 'tank');
    }

    /**
     * Filter LPG storage cylinders.
     */
    public function scopeCylinders(Builder $query): Builder
    {
        return $query->where('storage_type', 'cylinder');
    }

    /**
     * Determine whether the storage unit is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Determine whether the unit is a storage tank.
     */
    public function isTank(): bool
    {
        return $this->storage_type === 'tank';
    }

    /**
     * Determine whether the unit is a storage cylinder.
     */
    public function isCylinder(): bool
    {
        return $this->storage_type === 'cylinder';
    }
}
