<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessorySale extends Model
{
    protected $table = 'accessory_sales';

    public const STATUS_POSTED = 'posted';

    public const PAYMENT_CASH = 'cash';
    public const PAYMENT_BANK_TRANSFER = 'bank_transfer';
    public const PAYMENT_POS = 'pos';

    protected $fillable = [
        'sale_number',
        'transaction_token',
        'sale_date',
        'branch_id',
        'accessory_id',
        'attendant_id',
        'quantity',
        'unit_selling_price',
        'total_amount',
        'payment_method',
        'payment_reference',
        'unit_cost_price',
        'cost_of_goods_sold',
        'gross_profit',
        'stock_movement_id',
        'status',
        'posted_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'sale_date' => 'date',
            'branch_id' => 'integer',
            'accessory_id' => 'integer',
            'attendant_id' => 'integer',
            'quantity' => 'decimal:3',
            'unit_selling_price' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'unit_cost_price' => 'decimal:2',
            'cost_of_goods_sold' => 'decimal:2',
            'gross_profit' => 'decimal:2',
            'stock_movement_id' => 'integer',
            'posted_at' => 'datetime',
            'created_by' => 'integer',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function accessory(): BelongsTo
    {
        return $this->belongsTo(Accessory::class);
    }

    public function attendant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attendant_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPosted(): bool
    {
        return $this->status === self::STATUS_POSTED;
    }

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
}
