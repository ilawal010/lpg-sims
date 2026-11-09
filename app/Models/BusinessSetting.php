<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessSetting extends Model
{
    protected $fillable = [
        'business_name',
        'trading_name',
        'address',
        'phone',
        'email',
        'logo_path',
        'receipt_heading',
        'receipt_footer',
        'receipt_prefix',
        'lpg_unit',
        'low_stock_threshold_kg',
        'currency_code',
        'currency_symbol',
        'costing_method',
        'timezone',
        'date_format',
        'updated_by',
    ];

    protected $casts = [
        'low_stock_threshold_kg' => 'decimal:3',
    ];

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Retrieve the business configuration.
     *
     * The application will maintain one settings record.
     */
    public static function current(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'business_name' => 'Intisar Oil & Gas',
                'receipt_prefix' => 'INT',
                'lpg_unit' => 'kg',
                'low_stock_threshold_kg' => 100,
                'currency_code' => 'NGN',
                'currency_symbol' => '₦',
                'costing_method' => 'weighted_average',
                'timezone' => 'Africa/Lagos',
                'date_format' => 'd/m/Y',
            ]
        );
    }
}
