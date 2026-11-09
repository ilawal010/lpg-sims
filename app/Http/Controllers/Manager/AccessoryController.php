<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Accessory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccessoryController extends Controller
{
    /**
     * Display accessories belonging to the
     * Manager's assigned active branches.
     *
     * Read-only operational inventory.
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

        /*
         * Retrieve only active branches
         * assigned to this Manager.
         */
        $branches = $manager->branches()
            ->where('branches.status', 'active')
            ->orderBy('branches.branch_name')
            ->get();

        $branchIds = $branches
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        /*
         * Validate inventory filters.
         */
        $filters = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:150',
            ],
            'branch_id' => [
                'nullable',
                'integer',
                'exists:branches,id',
            ],
            'category' => [
                'nullable',
                'string',
                'max:100',
            ],
            'status' => [
                'nullable',
                'in:active,inactive',
            ],
            'stock_status' => [
                'nullable',
                'in:all,low,out,available',
            ],
        ]);

        /*
         * Reject unauthorized branch filters.
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

            $branchIds = [
                (int) $filters['branch_id'],
            ];
        }

        /*
         * Select operational columns only.
         *
         * Buying prices and profit margins
         * must never be included.
         */
        $query = Accessory::query()
            ->select([
                'id',
                'accessory_number',
                'name',
                'category',
                'branch_id',
                'unit',
                'quantity',
                'reorder_level',
                'unit_selling_price',
                'status',
            ])
            ->whereIn('branch_id', $branchIds)
            ->with('branch:id,branch_name');

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where(
                    'accessory_number',
                    'like',
                    "%{$search}%"
                )->orWhere(
                    'name',
                    'like',
                    "%{$search}%"
                );
            });
        }

        if (!empty($filters['category'])) {
            $query->where(
                'category',
                $filters['category']
            );
        }

        if (!empty($filters['status'])) {
            $query->where(
                'status',
                $filters['status']
            );
        }

        /*
         * Stock availability filters.
         */
        $stockStatus = $filters['stock_status'] ?? 'all';

        if ($stockStatus === 'out') {
            $query->where('quantity', '<=', 0);
        } elseif ($stockStatus === 'low') {
            $query->where('quantity', '>', 0)
                ->whereColumn(
                    'quantity',
                    '<=',
                    'reorder_level'
                );
        } elseif ($stockStatus === 'available') {
            $query->whereColumn(
                'quantity',
                '>',
                'reorder_level'
            );
        }

        /*
         * Summary based on authorized branches.
         * No financial valuations.
         */
        $summaryQuery = Accessory::query()
            ->whereIn('branch_id', $branchIds);

        $summary = [
            'total_items' => (clone $summaryQuery)->count(),

            'active_items' => (clone $summaryQuery)
                ->where('status', 'active')
                ->count(),

            'low_stock_items' => (clone $summaryQuery)
                ->where('quantity', '>', 0)
                ->whereColumn(
                    'quantity',
                    '<=',
                    'reorder_level'
                )
                ->count(),

            'out_of_stock_items' => (clone $summaryQuery)
                ->where('quantity', '<=', 0)
                ->count(),
        ];

        /*
         * Categories from authorized branches only.
         */
        $categories = Accessory::query()
            ->whereIn('branch_id', $branchIds)
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $accessories = $query
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('manager.inventory.accessories', [
            'accessories' => $accessories,
            'branches' => $branches,
            'categories' => $categories,
            'summary' => $summary,
            'filters' => $filters,
        ]);
    }
}
