<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashbookTransaction extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Transaction Types
    |--------------------------------------------------------------------------
    */

    public const TYPE_RECEIPT = 'receipt';
    public const TYPE_PAYMENT = 'payment';
    public const SOURCE_LPG_PURCHASE_PAYMENT = 'lpg_purchase_payment';

    /*
    |--------------------------------------------------------------------------
    | Transaction Sources
    |--------------------------------------------------------------------------
    */

    public const SOURCE_LPG_SALE = 'lpg_sale';
    public const SOURCE_ACCESSORY_SALE = 'accessory_sale';
    public const SOURCE_EXPENSE = 'expense';

    /*
    |--------------------------------------------------------------------------
    | Payment Methods
    |--------------------------------------------------------------------------
    */

    public const PAYMENT_CASH = 'cash';
    public const PAYMENT_BANK_TRANSFER = 'bank_transfer';
    public const PAYMENT_POS = 'pos';
    public const PAYMENT_OTHER = 'other';

    /*
    |--------------------------------------------------------------------------
    | Transaction Statuses
    |--------------------------------------------------------------------------
    */

    public const STATUS_RECORDED = 'recorded';
    public const STATUS_VOIDED = 'voided';

    protected $fillable = [
        'branch_id',
        'transaction_date',
        'transaction_number',
        'transaction_type',
        'category',
        'description',
        'amount',
        'payment_method',
        'payment_reference',
        'source_type',
        'source_id',
        'is_manual',
        'status',
        'created_by',
        'voided_by',
        'voided_at',
        'void_reason',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'is_manual' => 'boolean',
        'voided_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeRecorded($query)
    {
        return $query->where('status', self::STATUS_RECORDED);
    }

    public function scopeVoided($query)
    {
        return $query->where('status', self::STATUS_VOIDED);
    }

    public function scopeReceipts($query)
    {
        return $query->where('transaction_type', self::TYPE_RECEIPT);
    }

    public function scopePayments($query)
    {
        return $query->where('transaction_type', self::TYPE_PAYMENT);
    }

    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeForBranches($query, array $branchIds)
    {
        return $query->whereIn('branch_id', $branchIds);
    }

    public function scopeBetweenDates($query, string $from, string $to)
    {
        return $query->whereDate('transaction_date', '>=', $from)
            ->whereDate('transaction_date', '<=', $to);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isReceipt(): bool
    {
        return $this->transaction_type === self::TYPE_RECEIPT;
    }

    public function isPayment(): bool
    {
        return $this->transaction_type === self::TYPE_PAYMENT;
    }

    public function isRecorded(): bool
    {
        return $this->status === self::STATUS_RECORDED;
    }

    public function isVoided(): bool
    {
        return $this->status === self::STATUS_VOIDED;
    }
}
