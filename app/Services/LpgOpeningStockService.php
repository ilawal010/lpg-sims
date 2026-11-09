<?php

namespace App\Services;

use App\Models\LpgStockMovement;
use App\Models\LpgStorageUnit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LpgOpeningStockService
{
    /**
     * Post the initial LPG stock for a storage unit.
     *
     * Quantity: kilograms, 3 decimal places.
     * Unit cost: NGN per kilogram, 4 decimal places.
     */
    public function post(
        int $storageUnitId,
        string $quantityKg,
        string $unitCost,
        string $movementDate,
        int $userId,
        ?string $documentReference = null,
        ?string $description = null
    ): LpgStockMovement {
        $this->validateDecimal($quantityKg, 3, 'quantity_kg');
        $this->validateDecimal($unitCost, 4, 'unit_cost');

        if (bccomp($quantityKg, '0', 3) <= 0) {
            throw ValidationException::withMessages([
                'quantity_kg' => 'Opening stock quantity must be greater than zero.',
            ]);
        }

        if (bccomp($unitCost, '0', 4) < 0) {
            throw ValidationException::withMessages([
                'unit_cost' => 'Unit acquisition cost cannot be negative.',
            ]);
        }

        return DB::transaction(function () use (
            $storageUnitId,
            $quantityKg,
            $unitCost,
            $movementDate,
            $userId,
            $documentReference,
            $description
        ) {
            /*
             * Lock the storage unit to serialize stock postings
             * and prevent concurrent opening stock submissions.
             */
            $storageUnit = LpgStorageUnit::query()
                ->with('branch')
                ->lockForUpdate()
                ->findOrFail($storageUnitId);

            if ($storageUnit->status !== 'active') {
                throw ValidationException::withMessages([
                    'lpg_storage_unit_id' =>
                        'Opening stock can only be posted to an active storage unit.',
                ]);
            }

            if (!$storageUnit->branch ||
                $storageUnit->branch->status !== 'active') {
                throw ValidationException::withMessages([
                    'lpg_storage_unit_id' =>
                        'The storage unit must belong to an active branch.',
                ]);
            }

            /*
             * Opening stock is permitted only before any
             * inventory movements have been recorded.
             */
            $existingMovement = LpgStockMovement::query()
                ->where('lpg_storage_unit_id', $storageUnitId)
                ->exists();

            if ($existingMovement) {
                throw ValidationException::withMessages([
                    'lpg_storage_unit_id' =>
                        'Opening stock cannot be posted because this storage unit already has inventory transactions.',
                ]);
            }

            /*
             * Check rated storage capacity.
             */
            if (bccomp(
                $quantityKg,
                (string) $storageUnit->capacity_kg,
                3
            ) > 0) {
                throw ValidationException::withMessages([
                    'quantity_kg' =>
                        'Opening stock exceeds the rated storage capacity.',
                ]);
            }

            /*
             * Normalize decimal quantities and costs.
             */
            $quantity = bcadd($quantityKg, '0', 3);
            $cost = bcadd($unitCost, '0', 4);

            /*
             * Calculate total acquisition value.
             */
            $totalCost = bcmul($quantity, $cost, 4);

            /*
             * Generate a unique reference.
             */
            $reference = 'OS-' . now()->format('YmdHis')
                . '-' . strtoupper(Str::random(8));

            $movement = LpgStockMovement::create([
                'branch_id' => $storageUnit->branch_id,
                'lpg_storage_unit_id' => $storageUnit->id,
                'movement_reference' => $reference,
                'movement_type' =>
                    LpgStockMovement::TYPE_OPENING_STOCK,
                'direction' =>
                    LpgStockMovement::DIRECTION_IN,
                'quantity_kg' => $quantity,
                'unit_cost' => $cost,
                'total_cost' => $totalCost,
                'movement_date' => $movementDate,
                'document_reference' => $documentReference,
                'description' => $description
                    ?: 'Initial LPG opening stock',
                'transaction_group' => null,
                'created_by' => $userId,
            ]);

            /*
             * Record the posting in the audit trail.
             */
            AuditLogger::log(
                action: 'created',
                module: 'lpg_stock_movements',
                description:
                    'Posted opening LPG stock of '
                    . $quantity
                    . ' kg to '
                    . $storageUnit->storage_code,
                record: $movement,
                oldValues: [],
                newValues: $movement->getAttributes()
            );

            return $movement;
        }, 3);
    }

    /**
     * Validate an unsigned decimal without rounding input.
     */
    private function validateDecimal(
        string $value,
        int $scale,
        string $field
    ): void {
        $pattern = '/^\d+(?:\.\d{1,' . $scale . '})?$/';

        if (!preg_match($pattern, $value)) {
            throw ValidationException::withMessages([
                $field =>
                    'Enter a valid non-negative number with no more than '
                    . $scale
                    . ' decimal places.',
            ]);
        }
    }
}
