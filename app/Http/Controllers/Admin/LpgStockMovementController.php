<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\LpgStockMovement;
use App\Models\LpgStorageUnit;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LpgStockMovementController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(
            $request->user()
                && $request->user()->hasRole('owner')
                && $request->user()->hasPermission('inventory.view'),
            403
        );

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'lpg_storage_unit_id' => [
                'nullable',
                'integer',
                'exists:lpg_storage_units,id',
            ],
            'movement_type' => ['nullable', 'string', 'max:50'],
            'direction' => ['nullable', 'in:in,out'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $query = LpgStockMovement::query()
            ->with(['branch', 'storageUnit', 'createdBy']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('movement_reference', 'like', "%{$search}%")
                    ->orWhere('document_reference', 'like', "%{$search}%")
                    ->orWhere('transaction_group', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (!empty($filters['lpg_storage_unit_id'])) {
            $query->where(
                'lpg_storage_unit_id',
                $filters['lpg_storage_unit_id']
            );
        }

        if (!empty($filters['movement_type'])) {
            $query->where('movement_type', $filters['movement_type']);
        }

        if (!empty($filters['direction'])) {
            $query->where('direction', $filters['direction']);
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

        $summaryQuery = clone $query;

        $summary = [
            'movement_count' => (clone $summaryQuery)->count(),

            'total_in_kg' => (float) (clone $summaryQuery)
                ->where('direction', 'in')
                ->sum('quantity_kg'),

            'total_out_kg' => (float) (clone $summaryQuery)
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

        $branches = Branch::query()
            ->orderBy('branch_name')
            ->get();

        $storageUnits = LpgStorageUnit::query()
            ->orderBy('storage_name')
            ->get();

        $movementTypes = LpgStockMovement::query()
            ->select('movement_type')
            ->distinct()
            ->orderBy('movement_type')
            ->pluck('movement_type');

        return view('admin.lpg-stock.movements', compact(
            'movements',
            'summary',
            'branches',
            'storageUnits',
            'movementTypes',
            'filters'
        ));
    }


    /**
     * Display a single LPG stock movement.
     *
     * Read-only: no stock or financial records are modified.
     */
    public function show(
        \Illuminate\Http\Request $request,
        \App\Models\LpgStockMovement $movement
    ): \Illuminate\View\View {

        abort_unless(
            $request->user()
                && $request->user()->hasRole('owner')
                && $request->user()->hasPermission('inventory.view'),
            403
        );

        $movement->load([
            'branch',
            'storageUnit',
            'createdBy',
        ]);

        return view('admin.lpg-stock.movement-show', [
            'movement' => $movement,
        ]);
    }

}
