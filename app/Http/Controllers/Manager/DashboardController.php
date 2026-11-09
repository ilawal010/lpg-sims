<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display branch-restricted operational information.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless(
            $user && $user->hasRole('manager'),
            403,
            'Manager access only.'
        );

        $branches = $user->branches()
            ->where('branches.status', 'active')
            ->orderBy('branches.branch_name')
            ->get([
                'branches.id',
                'branches.branch_code',
                'branches.branch_name',
            ]);

        $branchIds = $branches->pluck('id')->all();

        $today = now()->toDateString();

        $monthStart = now()->startOfMonth()->toDateString();

        /*
         * Sales aggregates are intentionally limited to:
         * - assigned active branches;
         * - posted transactions;
         * - quantities, revenue and transaction counts.
         *
         * Cost and profitability columns are not selected.
         */

        $lpgToday = $this->lpgSummary(
            $branchIds,
            $today,
            $today
        );

        $accessoriesToday = $this->accessorySummary(
            $branchIds,
            $today,
            $today
        );

        $lpgMonth = $this->lpgSummary(
            $branchIds,
            $monthStart,
            $today
        );

        $accessoriesMonth = $this->accessorySummary(
            $branchIds,
            $monthStart,
            $today
        );

        $lpgAllTime = $this->lpgSummary($branchIds);

        $accessoriesAllTime = $this->accessorySummary($branchIds);

        return view('manager.dashboard', [
            'branches' => $branches,

            'today' => $today,

            'lpgToday' => $lpgToday,
            'accessoriesToday' => $accessoriesToday,

            'lpgMonth' => $lpgMonth,
            'accessoriesMonth' => $accessoriesMonth,

            'lpgAllTime' => $lpgAllTime,
            'accessoriesAllTime' => $accessoriesAllTime,
        ]);
    }

    /**
     * Summarize posted LPG sales within authorized branches.
     */
    private function lpgSummary(
        array $branchIds,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): object {
        $query = DB::table('lpg_sales')
            ->whereIn('branch_id', $branchIds)
            ->where('status', 'posted');

        if ($dateFrom !== null) {
            $query->whereDate('sale_date', '>=', $dateFrom);
        }

        if ($dateTo !== null) {
            $query->whereDate('sale_date', '<=', $dateTo);
        }

        return $query
            ->selectRaw('COUNT(*) AS transactions')
            ->selectRaw(
                'COALESCE(SUM(quantity_kg), 0) AS quantity'
            )
            ->selectRaw(
                'COALESCE(SUM(total_amount), 0) AS revenue'
            )
            ->first();
    }

    /**
     * Summarize posted accessory sales within authorized branches.
     */
    private function accessorySummary(
        array $branchIds,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): object {
        $query = DB::table('accessory_sales')
            ->whereIn('branch_id', $branchIds)
            ->where('status', 'posted');

        if ($dateFrom !== null) {
            $query->whereDate('sale_date', '>=', $dateFrom);
        }

        if ($dateTo !== null) {
            $query->whereDate('sale_date', '<=', $dateTo);
        }

        return $query
            ->selectRaw('COUNT(*) AS transactions')
            ->selectRaw(
                'COALESCE(SUM(quantity), 0) AS quantity'
            )
            ->selectRaw(
                'COALESCE(SUM(total_amount), 0) AS revenue'
            )
            ->first();
    }
}
