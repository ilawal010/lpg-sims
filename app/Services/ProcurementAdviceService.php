<?php

namespace App\Services;

use App\Models\Accessory;
use App\Models\LpgStorageUnit;
use App\Models\ProcurementStockSetting;
use Illuminate\Support\Collection;

class ProcurementAdviceService
{
    public function __construct(
        protected LpgStockBalanceService $stockBalanceService
    ) {
    }

    /**
     * Generate procurement advice for authorized branches.
     *
     * Branch IDs must be authorized by the calling controller.
     */
    public function generate(array $branchIds): Collection
    {
        $branchIds = array_values(array_unique(
            array_map('intval', $branchIds)
        ));

        if (empty($branchIds)) {
            return collect();
        }

        $settings = ProcurementStockSetting::query()
            ->whereIn('branch_id', $branchIds)
            ->where('is_active', true)
            ->get()
            ->keyBy(fn ($setting) =>
                $setting->branch_id . ':'
                . $setting->item_type . ':'
                . $setting->item_id
            );

        $advice = collect();

        $storageUnits = LpgStorageUnit::query()
            ->with('branch')
            ->whereIn('branch_id', $branchIds)
            ->where('status', 'active')
            ->get();

        foreach ($storageUnits as $unit) {
            $key = $unit->branch_id . ':lpg:' . $unit->id;

            $setting = $settings->get($key);

            $available = (float) $this->stockBalanceService
                ->getBalance($unit->id);

            $capacity = (float) $unit->capacity_kg;

            $minimum = $setting && $setting->minimum_stock !== null
                ? (float) $setting->minimum_stock
                : null;

            $target = $setting
                ? (float) $setting->target_stock
                : null;

            $advice->push($this->buildAdvice(
                'lpg',
                $unit->id,
                $unit->storage_name,
                $unit->storage_code,
                $unit->branch_id,
                $unit->branch?->branch_name,
                'kg',
                $available,
                $minimum,
                $target,
                $capacity
            ));
        }

        $accessories = Accessory::query()
            ->with('branch')
            ->whereIn('branch_id', $branchIds)
            ->where('status', 'active')
            ->get();

        foreach ($accessories as $accessory) {
            $key = $accessory->branch_id
                . ':accessory:' . $accessory->id;

            $setting = $settings->get($key);

            $advice->push($this->buildAdvice(
                'accessory',
                $accessory->id,
                $accessory->name,
                $accessory->accessory_number,
                $accessory->branch_id,
                $accessory->branch?->branch_name,
                $accessory->unit,
                (float) $accessory->quantity,
                (float) $accessory->reorder_level,
                $setting
                    ? (float) $setting->target_stock
                    : null
            ));
        }

        return $advice->sortBy([
            ['priority_rank', 'asc'],
            ['branch_name', 'asc'],
            ['item_name', 'asc'],
        ])->values();
    }

    protected function buildAdvice(
        string $type,
        int $itemId,
        string $name,
        string $code,
        int $branchId,
        ?string $branchName,
        string $unit,
        float $available,
        ?float $minimum,
        ?float $target,
        ?float $capacity = null
    ): array {
        $status = 'not_configured';
        $priority = 'Configuration Required';
        $rank = 5;
        $quantityToProcure = null;

        $validThresholds = $target !== null
            && $target > 0
            && $minimum !== null
            && $minimum >= 0
            && $minimum <= $target
            && ($capacity === null || $target <= $capacity);

        if ($validThresholds) {
            $quantityToProcure = max(
                0,
                round($target - $available, 3)
            );

            if ($quantityToProcure <= 0) {
                $status = 'sufficient';
                $priority = 'No Procurement';
                $rank = 4;
            } elseif ($available <= 0) {
                $status = 'critical';
                $priority = 'Critical';
                $rank = 1;
            } elseif ($available <= $minimum) {
                $status = 'reorder';
                $priority = 'High';
                $rank = 2;
            } else {
                $status = 'routine';
                $priority = 'Routine';
                $rank = 3;
            }
        }

        return [
            'item_type' => $type,
            'item_id' => $itemId,
            'item_name' => $name,
            'item_code' => $code,
            'branch_id' => $branchId,
            'branch_name' => $branchName,
            'unit' => $unit,
            'available_stock' => round($available, 3),
            'minimum_stock' => $minimum,
            'target_stock' => $target,
            'capacity' => $capacity,
            'quantity_to_procure' => $quantityToProcure,
            'status' => $status,
            'priority' => $priority,
            'priority_rank' => $rank,
        ];
    }
}
