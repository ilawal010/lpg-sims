<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\LpgStorageUnit;
use App\Services\LpgInventoryCostingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LpgStorageUnitController extends Controller
{
    /**
     * Display storage units belonging to the
     * Manager's assigned active branches.
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

        $branches = $manager->branches()
            ->where('branches.status', 'active')
            ->orderBy('branches.branch_name')
            ->get();

        $branchIds = $branches
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'storage_type' => ['nullable', 'in:tank,cylinder'],
            'status' => [
                'nullable',
                'in:active,inactive,maintenance,decommissioned',
            ],
        ]);

        if (!empty($filters['branch_id'])) {
            abort_unless(
                in_array(
                    (int) $filters['branch_id'],
                    $branchIds,
                    true
                ),
                403
            );

            $branchIds = [(int) $filters['branch_id']];
        }

        /*
         * Select operational storage information only.
         * No inventory costs or financial valuations.
         */
        $query = LpgStorageUnit::query()
            ->select([
                'id',
                'branch_id',
                'storage_code',
                'storage_name',
                'storage_type',
                'capacity_kg',
                'manufacturer',
                'serial_number',
                'installation_date',
                'status',
            ])
            ->whereIn('branch_id', $branchIds)
            ->with('branch:id,branch_name');

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('storage_code', 'like', "%{$search}%")
                    ->orWhere('storage_name', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['storage_type'])) {
            $query->where(
                'storage_type',
                $filters['storage_type']
            );
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        /*
         * Retrieve authorized storage units.
         */
        $storageUnits = $query
            ->orderBy('storage_name')
            ->get();

        /*
         * Calculate operational stock balances.
         *
         * The costing service also calculates financial
         * information internally, but we retain only
         * quantity_kg in the Manager's results.
         */
        $rows = $storageUnits->map(
            function ($unit) use ($costingService) {
                $valuation = $costingService->getValuation($unit->id);

                $quantity = (float) ($valuation['quantity_kg'] ?? 0);
                $capacity = (float) $unit->capacity_kg;

                return [
                    'storage_unit' => $unit,
                    'quantity_kg' => $quantity,
                    'capacity_kg' => $capacity,
                    'utilization_percent' => $capacity > 0
                        ? round(($quantity / $capacity) * 100, 2)
                        : 0,
                ];
            }
        );

        $summary = [
            'total_units' => $rows->count(),
            'active_units' => $rows->filter(
                fn ($row) => $row['storage_unit']->status === 'active'
            )->count(),
            'total_capacity_kg' => $rows->sum('capacity_kg'),
            'total_stock_kg' => $rows->sum('quantity_kg'),
        ];

        $summary['utilization_percent'] =
            $summary['total_capacity_kg'] > 0
                ? round(
                    ($summary['total_stock_kg']
                        / $summary['total_capacity_kg']) * 100,
                    2
                )
                : 0;

        return view('manager.inventory.storage-units', [
            'rows' => $rows,
            'summary' => $summary,
            'branches' => $branches,
            'filters' => $filters,
        ]);
    }
}
