<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Accessory extends Model
{
    protected $table = 'accessories';

    protected $fillable = [
        'name',
        'category',
        'description',
        'branch_id',
        'unit',
        'quantity',
        'reorder_level',
        'unit_buying_price',
        'unit_selling_price',
        'status',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'reorder_level' => 'decimal:3',
        'unit_buying_price' => 'decimal:2',
        'unit_selling_price' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (Accessory $accessory) {
            $accessory->created_by = auth()->id();
            $accessory->updated_by = auth()->id();

           // Generate a temporary unique number within
            // the database column's 30-character limit.
            if (empty($accessory->accessory_number)) {
                $accessory->accessory_number =
                    'TMP-' . bin2hex(random_bytes(12));
            }
        });

        static::created(function (Accessory $accessory) {
            $number = 'ACC-' . str_pad(
                (string) $accessory->id,
                6,
                '0',
                STR_PAD_LEFT
            );

            DB::table('accessories')
                ->where('id', $accessory->id)
                ->update([
                    'accessory_number' => $number,
                ]);

            $accessory->accessory_number = $number;
        });

        static::updating(function (Accessory $accessory) {
            $accessory->updated_by = auth()->id();
        });
    }

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

    public function getStockValueAttribute(): float
    {
        return round(
            (float) $this->quantity *
            (float) $this->unit_buying_price,
            2
        );
    }

    public function getExpectedSalesValueAttribute(): float
    {
        return round(
            (float) $this->quantity *
            (float) $this->unit_selling_price,
            2
        );
    }

    public function getExpectedGrossProfitAttribute(): float
    {
        return round(
            $this->expected_sales_value - $this->stock_value,
            2
        );
    }
}
