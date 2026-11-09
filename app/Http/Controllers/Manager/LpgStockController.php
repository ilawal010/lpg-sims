<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\LpgStockMovement;
use App\Models\LpgStorageUnit;
use App\Services\LpgInventoryCostingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LpgStockController extends Controller
{
    /**
     * Manager LPG Stock Dashboard.
     *
     * Read-only, branch-scoped operational inventory.
     * Financial costing information is not exposed.
     */
    public function index(
        Request $request,
        LpgInventoryCostingService $costingService
    ): View {
        $manager = $request->user();

        abort_unless(
            $manager
                && $manager->hasRole('manager')
                && $manager->hasPermission('inventory.view'),
            403
        );

        /*
         * Only active branches assigned to the Manager.
         */
        $branchIds = $manager->branches()
            ->where('branches.status', 'active')
            ->pluck('branches.id');

        /*
         * Storage units belonging to authorized branches.
         */
        $storageUnits = LpgStorageUnit::query()
            ->whereIn('branch_id', $branchIds)
            ->with('branch')
            ->orderBy('branch_id')
            ->orderBy('storage_name')
            ->get();

        /*
         * Reuse the established inventory calculation.
         *
         * Only operational quantities are retained
         * in the Manager's response data.
         */
        $stockRows = $storageUnits->map(
            function ($unit) use ($costingService) {

                $valuation = $costingService
                    ->getValuation($unit->id);

                $quantity = (float) (
                    $valuation['quantity_kg'] ?? 0
                );

                $capacity = (float) $unit->capacity_kg;

                return [
                    'storage_unit' => $unit,
                    'quantity_kg' => $quantity,
                    'capacity_kg' => $capacity,
                    'utilization_percent' => $capacity > 0
                        ? round(
                            ($quantity / $capacity) * 100,
                            2
                        )
                        : 0,
                ];
            }
        );

        /*
         * Operational inventory summary.
         * No cost or valuation fields.
         */
        $summary = [
            'storage_units' => $stockRows->count(),
            'total_capacity_kg' => $stockRows->sum('capacity_kg'),
            'total_stock_kg' => $stockRows->sum('quantity_kg'),
        ];

        $summary['utilization_percent'] =
            $summary['total_capacity_kg'] > 0
                ? round(
                    (
                        $summary['total_stock_kg']
                        / $summary['total_capacity_kg']
                    ) * 100,
                    2
                )
                : 0;

        /*
         * Recent movements:
         * Fetch only operational columns, excluding
         * financial cost information.
         */
        $recentMovements = LpgStockMovement::query()
            ->whereIn('branch_id', $branchIds)
            ->select([
                'id',
                'branch_id',
                'lpg_storage_unit_id',
                'movement_reference',
                'movement_date',
                'movement_type',
                'direction',
                'quantity_kg',
            ])
            ->with([
                'branch:id,branch_name',
                'storageUnit:id,storage_name',
            ])
            ->latest('id')
            ->limit(20)
            ->get();

        return view('manager.inventory.lpg-stock', [
            'stockRows' => $stockRows,
            'summary' => $summary,
            'recentMovements' => $recentMovements,
        ]);
    }
}
