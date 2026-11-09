<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\AccessorySale;
use App\Models\LpgSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SalesHistoryController extends Controller
{
    /**
     * Display posted LPG and Accessories sales
     * for the Manager's assigned active branches.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless(
            $user
                && $user->hasRole('manager')
                && $user->hasPermission('sales.view'),
            403
        );

        $branches = $user->branches()
            ->where('branches.status', 'active')
            ->orderBy('branches.branch_name')
            ->get();

        $branchIds = $branches
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'sale_type' => ['nullable', 'in:lpg,accessory'],
            'branch_id' => [
                'nullable',
                'integer',
                'exists:branches,id',
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
         * Never allow a Manager to select
         * a branch outside their assignments.
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
         * Only operational fields are selected.
         *
         * Cost prices, COGS and gross profit
         * are deliberately excluded.
         */
        $lpgQuery = DB::table('lpg_sales as s')
            ->join(
                'branches as b',
                'b.id',
                '=',
                's.branch_id'
            )
            ->leftJoin(
                'users as u',
                'u.id',
                '=',
                's.attendant_id'
            )
            ->whereIn('s.branch_id', $branchIds)
            ->where('s.status', LpgSale::STATUS_POSTED)
            ->select([
                's.id',
                DB::raw("'lpg' as sale_type"),
                's.sale_number',
                's.sale_date',
                's.branch_id',
                'b.branch_name',
                'u.name as attendant_name',
                's.quantity_kg as quantity',
                's.selling_price_per_kg as unit_price',
                's.total_amount',
                's.posted_at',
            ]);

        $accessoryQuery = DB::table('accessory_sales as s')
            ->join(
                'branches as b',
                'b.id',
                '=',
                's.branch_id'
            )
            ->leftJoin(
                'users as u',
                'u.id',
                '=',
                's.attendant_id'
            )
            ->whereIn('s.branch_id', $branchIds)
            ->where('s.status', AccessorySale::STATUS_POSTED)
            ->select([
                's.id',
                DB::raw("'accessory' as sale_type"),
                's.sale_number',
                's.sale_date',
                's.branch_id',
                'b.branch_name',
                'u.name as attendant_name',
                's.quantity as quantity',
                's.unit_selling_price as unit_price',
                's.total_amount',
                's.posted_at',
            ]);

        /*
         * Apply the same filters to both
         * transaction sources.
         */
        foreach ([$lpgQuery, $accessoryQuery] as $query) {
            if (!empty($filters['search'])) {
                $query->where(
                    's.sale_number',
                    'like',
                    '%' . $filters['search'] . '%'
                );
            }

            if (!empty($filters['date_from'])) {
                $query->whereDate(
                    's.sale_date',
                    '>=',
                    $filters['date_from']
                );
            }

            if (!empty($filters['date_to'])) {
                $query->whereDate(
                    's.sale_date',
                    '<=',
                    $filters['date_to']
                );
            }
        }

        /*
         * Apply transaction-type selection.
         */
        $saleType = $filters['sale_type'] ?? null;

        if ($saleType === 'lpg') {
            $combinedQuery = $lpgQuery;
        } elseif ($saleType === 'accessory') {
            $combinedQuery = $accessoryQuery;
        } else {
            $combinedQuery = $lpgQuery->unionAll(
                $accessoryQuery
            );
        }

        /*
         * Use a subquery for consistent
         * combined sorting and pagination.
         */
        $salesSource = DB::query()
            ->fromSub($combinedQuery, 'sales_history');

        $summary = (clone $salesSource)
            ->selectRaw(
                'COUNT(*) as transactions, ' .
                'COALESCE(SUM(total_amount), 0) as revenue'
            )
            ->first();

        $lpgQuantity = (clone $salesSource)
            ->where('sale_type', 'lpg')
            ->sum('quantity');

        $accessoryQuantity = (clone $salesSource)
            ->where('sale_type', 'accessory')
            ->sum('quantity');

        $sales = $salesSource
            ->orderByDesc('sale_date')
            ->orderByDesc('posted_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('manager.sales.index', [
            'sales' => $sales,
            'branches' => $branches,
            'filters' => $filters,
            'totalTransactions' =>
                (int) ($summary->transactions ?? 0),
            'totalRevenue' =>
                (string) ($summary->revenue ?? '0'),
            'totalLpgQuantity' =>
                (string) $lpgQuantity,
            'totalAccessoryQuantity' =>
                (string) $accessoryQuantity,
        ]);
    }
}
