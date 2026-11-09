<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\LpgStockMovement;
use App\Models\LpgStorageUnit;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LpgStockMovementController extends Controller
{
    /**
     * Display operational LPG stock movements
     * for the Manager's assigned active branches.
     */
    public function index(Request $request): View
    {
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
            'branch_id' => [
                'nullable',
                'integer',
                'exists:branches,id',
            ],
            'lpg_storage_unit_id' => [
                'nullable',
                'integer',
                'exists:lpg_storage_units,id',
            ],
            'movement_type' => [
                'nullable',
                'string',
                'max:50',
            ],
            'direction' => [
                'nullable',
                'in:in,out',
            ],
            'date_from' => [
                'nullable',
                'date_format:Y-m-d',
            ],
            'date_to' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:date_from',
            ],
        ]);

        /*
         * Reject unauthorized branch selections.
         */
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
         * Reject storage-unit filters outside
         * the authorized branch scope.
         */
        if (!empty($filters['lpg_storage_unit_id'])) {
            $authorizedStorageUnit = LpgStorageUnit::query()
                ->whereKey($filters['lpg_storage_unit_id'])
                ->whereIn('branch_id', $branchIds)
                ->exists();

            abort_unless($authorizedStorageUnit, 403);
        }

        /*
         * Select operational columns only.
         *
         * Unit cost, total cost, and inventory
         * valuation are deliberately excluded.
         */
        $query = LpgStockMovement::query()
            ->select([
                'id',
                'branch_id',
                'lpg_storage_unit_id',
                'movement_reference',
                'document_reference',
                'transaction_group',
                'movement_date',
                'movement_type',
                'direction',
                'quantity_kg',
                'created_by',
            ])
            ->whereIn('branch_id', $branchIds)
            ->with([
                'branch:id,branch_name',
                'storageUnit:id,storage_name',
                'createdBy:id,name',
            ]);

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where(
                    'movement_reference',
                    'like',
                    "%{$search}%"
                )->orWhere(
                    'document_reference',
                    'like',
                    "%{$search}%"
                )->orWhere(
                    'transaction_group',
                    'like',
                    "%{$search}%"
                );
            });
        }

        if (!empty($filters['lpg_storage_unit_id'])) {
            $query->where(
                'lpg_storage_unit_id',
                $filters['lpg_storage_unit_id']
            );
        }

        if (!empty($filters['movement_type'])) {
            $query->where(
                'movement_type',
                $filters['movement_type']
            );
        }

        if (!empty($filters['direction'])) {
            $query->where(
                'direction',
                $filters['direction']
            );
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate(
                'movement_date',
                '>=',
                $filters['date_from']
            );
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate(
                'movement_date',
                '<=',
                $filters['date_to']
            );
        }

        /*
         * Summary reflects authorized branches
         * and the currently applied filters.
         */
        $summary = [
            'movement_count' => (clone $query)->count(),

            'total_in_kg' => (float) (clone $query)
                ->where('direction', 'in')
                ->sum('quantity_kg'),

            'total_out_kg' => (float) (clone $query)
                ->where('direction', 'out')
                ->sum('quantity_kg'),
        ];

        $summary['net_movement_kg'] =
            $summary['total_in_kg'] - $summary['total_out_kg'];

        $movements = $query
            ->orderByDesc('movement_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        /*
         * Storage-unit options are restricted
         * to the same authorized branches.
         */
        $storageUnits = LpgStorageUnit::query()
            ->select([
                'id',
                'branch_id',
                'storage_name',
            ])
            ->whereIn('branch_id', $branchIds)
            ->orderBy('storage_name')
            ->get();

        /*
         * Only movement types present in
         * authorized branches are listed.
         */
        $movementTypes = LpgStockMovement::query()
            ->whereIn('branch_id', $branchIds)
            ->select('movement_type')
            ->distinct()
            ->orderBy('movement_type')
            ->pluck('movement_type');

        return view('manager.inventory.stock-movements', [
            'movements' => $movements,
            'summary' => $summary,
            'branches' => $branches,
            'storageUnits' => $storageUnits,
            'movementTypes' => $movementTypes,
            'filters' => $filters,
        ]);
    }
}
