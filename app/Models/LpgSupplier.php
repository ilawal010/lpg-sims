<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LpgSupplier extends Model
{
    /**
     * Database table.
     */
    protected $table = 'lpg_suppliers';

    /**
     * Attributes permitted for mass assignment.
     */
    protected $fillable = [
        'supplier_code',
        'supplier_name',
        'contact_person',
        'phone',
        'alternate_phone',
        'email',
        'address',
        'city',
        'state',
        'registration_number',
        'tax_identification_number',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    /**
     * Attribute casting.
     */
    protected function casts(): array
    {
        return [
            'created_by' => 'integer',
            'updated_by' => 'integer',
        ];
    }

    /**
     * User who registered the supplier.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    /**
     * User who last updated the supplier.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }

    /**
     * Filter active suppliers.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Filter inactive suppliers.
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Filter suppliers by operational status.
     */
    public function scopeWithStatus(
        Builder $query,
        string $status
    ): Builder {
        return $query->where('status', $status);
    }

    /**
     * Search suppliers by name, code or contact details.
     */
    public function scopeSearch(
        Builder $query,
        string $search
    ): Builder {
        $search = trim($search);

        if ($search === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search) {
            $q->where(
                'supplier_name',
                'like',
                "%{$search}%"
            )
            ->orWhere(
                'supplier_code',
                'like',
                "%{$search}%"
            )
            ->orWhere(
                'contact_person',
                'like',
                "%{$search}%"
            )
            ->orWhere(
                'phone',
                'like',
                "%{$search}%"
            )
            ->orWhere(
                'email',
                'like',
                "%{$search}%"
            );
        });
    }

    /**
     * Determine whether the supplier is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Determine whether the supplier is inactive.
     */
    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    /**
     * Return a formatted supplier identification label.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->supplier_code
            . ' — '
            . $this->supplier_name;
    }
}
