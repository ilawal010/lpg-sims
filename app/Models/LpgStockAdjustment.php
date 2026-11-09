<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LpgStockAdjustment extends Model
{
    protected $table = 'lpg_stock_adjustments';

    protected $fillable = [
        'adjustment_reference',
        'branch_id',
        'lpg_storage_unit_id',
        'adjustment_date',
        'adjustment_type',
        'quantity_kg',
        'reason',
        'notes',
        'status',
        'created_by',
        'submitted_by',
        'submitted_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'stock_movement_id',
        'posted_at',
    ];

    protected $casts = [
        'adjustment_date' => 'date',
        'quantity_kg' => 'decimal:3',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'posted_at' => 'datetime',
    ];

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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function stockMovement(): BelongsTo
    {
        return $this->belongsTo(
            LpgStockMovement::class,
            'stock_movement_id'
        );
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPendingApproval(): bool
    {
        return $this->status === 'pending_approval';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isPosted(): bool
    {
        return $this->stock_movement_id !== null
            && $this->posted_at !== null;
    }

    public function canBeEdited(): bool
    {
        return $this->isDraft();
    }

    public function canBeSubmitted(): bool
    {
        return $this->isDraft();
    }

    public function canBeApproved(): bool
    {
        return $this->isPendingApproval();
    }

    public function canBeRejected(): bool
    {
        return $this->isPendingApproval();
    }
}
