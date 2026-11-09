<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LpgPurchasePayment extends Model
{
    protected $table = 'lpg_purchase_payments';

    protected $fillable = [
        'lpg_inventory_purchase_id',
        'branch_id',
        'payment_date',
        'amount',
        'payment_method',
        'payment_reference',
        'notes',
        'transaction_token',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(
            LpgInventoryPurchase::class,
            'lpg_inventory_purchase_id'
        );
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
