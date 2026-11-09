<?php

namespace App\Services;

use App\Models\LpgStockMovement;
use App\Models\LpgStorageUnit;
use Illuminate\Support\Facades\DB;

class LpgStockBalanceService
{
    /**
     * Calculate the current stock balance of one storage unit.
     *
     * All quantities are measured in kilograms.
     */
    public function getBalance(int $storageUnitId): string
    {
        $balance = LpgStockMovement::query()
            ->forStorageUnit($storageUnitId)
            ->selectRaw("
                COALESCE(
                    SUM(
                        CASE
                            WHEN direction = 'in'
                                THEN quantity_kg
                            WHEN direction = 'out'
                                THEN -quantity_kg
                            ELSE 0
                        END
                    ),
                    0
                ) AS stock_balance
            ")
            ->value('stock_balance');

        return number_format((float) $balance, 3, '.', '');
    }

    /**
     * Calculate total stock received by a storage unit.
     */
    public function getTotalIn(int $storageUnitId): string
    {
        $total = LpgStockMovement::query()
            ->forStorageUnit($storageUnitId)
            ->incoming()
            ->sum('quantity_kg');

        return number_format((float) $total, 3, '.', '');
    }

    /**
     * Calculate total stock issued by a storage unit.
     */
    public function getTotalOut(int $storageUnitId): string
    {
        $total = LpgStockMovement::query()
            ->forStorageUnit($storageUnitId)
            ->outgoing()
            ->sum('quantity_kg');

        return number_format((float) $total, 3, '.', '');
    }

    /**
     * Calculate total LPG stock across a branch.
     */
    public function getBranchBalance(int $branchId): string
    {
        $balance = LpgStockMovement::query()
            ->forBranch($branchId)
            ->selectRaw("
                COALESCE(
                    SUM(
                        CASE
                            WHEN direction = 'in'
                                THEN quantity_kg
                            WHEN direction = 'out'
                                THEN -quantity_kg
                            ELSE 0
                        END
                    ),
                    0
                ) AS stock_balance
            ")
            ->value('stock_balance');

        return number_format((float) $balance, 3, '.', '');
    }

    /**
     * Calculate stock capacity utilization for one unit.
     */
    public function getCapacityUtilization(
        LpgStorageUnit $storageUnit
    ): float {
        $capacity = (float) $storageUnit->capacity_kg;

        if ($capacity <= 0) {
            return 0.0;
        }

        $balance = (float) $this->getBalance(
            $storageUnit->id
        );

        return round(($balance / $capacity) * 100, 2);
    }

    /**
     * Get a storage unit's stock summary.
     */
    public function getStorageUnitSummary(
        LpgStorageUnit $storageUnit
    ): array {
        return [
            'storage_unit_id' => $storageUnit->id,
            'storage_code' => $storageUnit->storage_code,
            'storage_name' => $storageUnit->storage_name,
            'branch_id' => $storageUnit->branch_id,
            'capacity_kg' => (string) $storageUnit->capacity_kg,
            'total_in_kg' => $this->getTotalIn(
                $storageUnit->id
            ),
            'total_out_kg' => $this->getTotalOut(
                $storageUnit->id
            ),
            'balance_kg' => $this->getBalance(
                $storageUnit->id
            ),
            'capacity_utilization_percent' =>
                $this->getCapacityUtilization($storageUnit),
        ];
    }
}
