<?php

namespace App\Services;

use App\Models\LpgStockMovement;
use Illuminate\Validation\ValidationException;

class LpgInventoryCostingService
{
    /**
     * Calculate inventory valuation for one storage unit.
     *
     * Movements are processed in posting order (ID).
     * All calculations use BCMath.
     *
     * This method is read-only.
     */
    public function getValuation(int $storageUnitId): array
    {
        $quantity = '0.000';
        $inventoryValue = '0.0000';

        $movements = LpgStockMovement::query()
            ->forStorageUnit($storageUnitId)
            ->orderBy('id')
            ->get();

        foreach ($movements as $movement) {

            $reference = (string) $movement->movement_reference;

            $movementQuantity = bcadd(
                (string) $movement->quantity_kg,
                '0',
                3
            );

            /*
             * Every stock movement must have a
             * strictly positive quantity.
             */
            if (bccomp($movementQuantity, '0', 3) <= 0) {
                throw ValidationException::withMessages([
                    'inventory' =>
                        'Invalid stock quantity on movement '
                        . $reference,
                ]);
            }

            /*
             * Validate movement type and direction.
             */
            if (!$movement->hasValidDirection()) {
                throw ValidationException::withMessages([
                    'inventory' =>
                        'Invalid movement direction on '
                        . $reference,
                ]);
            }

            /*
             * Incoming opening stock and supplier receipts.
             */
            if (in_array($movement->movement_type, [
                LpgStockMovement::TYPE_OPENING_STOCK,
                LpgStockMovement::TYPE_STOCK_RECEIPT,
            ], true)) {

                $unitCost = (string) $movement->unit_cost;

                if (bccomp($unitCost, '0', 4) < 0) {
                    throw ValidationException::withMessages([
                        'inventory' =>
                            'Negative unit cost on movement '
                            . $reference,
                    ]);
                }

                $movementValue = bcmul(
                    $movementQuantity,
                    $unitCost,
                    4
                );

                $quantity = bcadd(
                    $quantity,
                    $movementQuantity,
                    3
                );

                $inventoryValue = bcadd(
                    $inventoryValue,
                    $movementValue,
                    4
                );

                continue;
            }

            /*
             * Outgoing LPG sales.
             *
             * The movement's total_cost must contain
             * the inventory cost calculated and recorded
             * at the time the sale was posted.
             */
            if (
                $movement->movement_type
                === LpgStockMovement::TYPE_SALE
            ) {

                if (
                    bccomp(
                        $movementQuantity,
                        $quantity,
                        3
                    ) > 0
                ) {
                    throw ValidationException::withMessages([
                        'inventory' =>
                            'Sale exceeds available inventory on movement '
                            . $reference,
                    ]);
                }

                if ($movement->total_cost === null) {
                    throw ValidationException::withMessages([
                        'inventory' =>
                            'Missing cost of goods sold on movement '
                            . $reference,
                    ]);
                }

                $movementValue = bcadd(
                    (string) $movement->total_cost,
                    '0',
                    4
                );

                if (bccomp($movementValue, '0', 4) < 0) {
                    throw ValidationException::withMessages([
                        'inventory' =>
                            'Negative cost of goods sold on movement '
                            . $reference,
                    ]);
                }

                if (
                    bccomp(
                        $movementValue,
                        $inventoryValue,
                        4
                    ) > 0
                ) {
                    throw ValidationException::withMessages([
                        'inventory' =>
                            'Sale cost exceeds inventory value on movement '
                            . $reference,
                    ]);
                }

                $quantity = bcsub(
                    $quantity,
                    $movementQuantity,
                    3
                );

                $inventoryValue = bcsub(
                    $inventoryValue,
                    $movementValue,
                    4
                );

                /*
                 * Fully depleted inventory must have
                 * no remaining inventory value.
                 */
                if (bccomp($quantity, '0', 3) === 0) {
                    if (
                        bccomp(
                            $inventoryValue,
                            '0',
                            4
                        ) !== 0
                    ) {
                        throw ValidationException::withMessages([
                            'inventory' =>
                                'Fully depleted stock has a residual inventory value on movement '
                                . $reference,
                        ]);
                    }

                    $inventoryValue = '0.0000';
                }

                continue;
            }

            /*
            * Authorized stock adjustments.
            *
            * Incoming adjustments use the unit cost
            * recorded when the adjustment was posted.
            *
            * Outgoing adjustments use the inventory cost
            * recorded at posting, just like LPG sales.
            */
            if (in_array($movement->movement_type, [
                LpgStockMovement::TYPE_ADJUSTMENT_IN,
                LpgStockMovement::TYPE_ADJUSTMENT_OUT,
            ], true)) {

                if ($movement->unit_cost === null ||
                    $movement->total_cost === null) {
                    throw ValidationException::withMessages([
                        'inventory' =>
                            'Missing adjustment costing on movement '
                            . $reference,
                    ]);
                }

                $unitCost = bcadd(
                    (string) $movement->unit_cost,
                    '0',
                    4
                );

                $movementValue = bcadd(
                    (string) $movement->total_cost,
                    '0',
                    4
                );

                if (
                    bccomp($unitCost, '0', 4) < 0 ||
                    bccomp($movementValue, '0', 4) < 0
                ) {
                    throw ValidationException::withMessages([
                        'inventory' =>
                            'Negative adjustment cost on movement '
                            . $reference,
                    ]);
                }

                

                /*
                * Adjustment OUT cannot exceed
                * available stock or inventory value.
                */
                if (bccomp($movementQuantity, $quantity, 3) > 0) {
                    throw ValidationException::withMessages([
                        'inventory' =>
                            'Stock adjustment exceeds available inventory on movement '
                            . $reference,
                    ]);
                }

                if (bccomp($movementValue, $inventoryValue, 4) > 0) {
                    throw ValidationException::withMessages([
                        'inventory' =>
                            'Adjustment cost exceeds inventory value on movement '
                            . $reference,
                    ]);
                }

                $quantity = bcsub(
                    $quantity,
                    $movementQuantity,
                    3
                );

                $inventoryValue = bcsub(
                    $inventoryValue,
                    $movementValue,
                    4
                );

                /*
                * Fully depleted stock must not
                * retain any inventory value.
                */
                if (bccomp($quantity, '0', 3) === 0) {
                    if (bccomp($inventoryValue, '0', 4) !== 0) {
                        throw ValidationException::withMessages([
                            'inventory' =>
                                'Fully depleted stock has a residual inventory value on movement '
                                . $reference,
                        ]);
                    }

                    $inventoryValue = '0.0000';
                }

                continue;
            }

            /*
            * Transfers require dedicated valuation rules.
            */
            throw ValidationException::withMessages([
                'inventory' =>
                    'Inventory costing is not yet configured for movement type '
                    . $movement->movement_type
                    . ' ('
                    . $reference
                    . ').',
            ]);
        }

        /*
         * Calculate weighted-average cost with
         * higher precision and half-up rounding.
         */
        $weightedAverageCost = '0.0000';

        if (bccomp($quantity, '0', 3) > 0) {
            $unroundedAverage = bcdiv(
                $inventoryValue,
                $quantity,
                12
            );

            $weightedAverageCost = bcadd(
                $unroundedAverage,
                '0.00005',
                4
            );
        }

        return [
            'lpg_storage_unit_id' => $storageUnitId,
            'quantity_kg' => $quantity,
            'inventory_value' => $inventoryValue,
            'weighted_average_cost' =>
                $weightedAverageCost,
            'movement_count' => $movements->count(),
        ];
    }
}
