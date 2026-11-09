<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\LpgSale;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LpgSaleController extends Controller
{
    /**
     * Display the LPG sales register.
     */
    public function index(Request $request): View
    {
        abort_unless(
            $request->user()
                && $request->user()->hasRole('owner')
                && $request->user()->hasPermission('sales.view'),
            403
        );

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => [
                'nullable',
                'in:draft,posted,cancelled',
            ],
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
         * Build a reusable filtered sales query.
         */
        $query = LpgSale::query();

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $search = '%' . $filters['search'] . '%';

                $q->where('sale_number', 'like', $search)
                    ->orWhere(
                        'payment_reference',
                        'like',
                        $search
                    );
            });
        }

        if (!empty($filters['status'])) {
            $query->where(
                'status',
                $filters['status']
            );
        }

        if (!empty($filters['branch_id'])) {
            $query->where(
                'branch_id',
                $filters['branch_id']
            );
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate(
                'sale_date',
                '>=',
                $filters['date_from']
            );
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate(
                'sale_date',
                '<=',
                $filters['date_to']
            );
        }

        /*
         * Summary figures use the same filters.
         *
         * Only posted sales contribute to
         * revenue, COGS and gross profit.
         */
        $totalSales = (clone $query)->count();

        $draftSales = (clone $query)
            ->where('status', LpgSale::STATUS_DRAFT)
            ->count();

        $postedSales = (clone $query)
            ->where('status', LpgSale::STATUS_POSTED)
            ->count();

        $cancelledSales = (clone $query)
            ->where('status', LpgSale::STATUS_CANCELLED)
            ->count();

        $postedQuery = (clone $query)
            ->where('status', LpgSale::STATUS_POSTED);

        $totalRevenue = (clone $postedQuery)
            ->sum('total_amount');

        $totalCogs = (clone $postedQuery)
            ->sum('cost_of_goods_sold');

        $totalGrossProfit = (clone $postedQuery)
            ->sum('gross_profit');

        /*
         * Paginated transaction register.
         */
        $sales = $query
            ->with([
                'branch',
                'storageUnit',
                'attendant',
            ])
            ->orderByDesc('sale_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        /*
         * Branch selection for filtering.
         */
        $branches = Branch::query()
            ->orderBy('branch_name')
            ->get();

        return view('admin.lpg-sales.index', [
            'sales' => $sales,
            'branches' => $branches,
            'totalSales' => $totalSales,
            'draftSales' => $draftSales,
            'postedSales' => $postedSales,
            'cancelledSales' => $cancelledSales,
            'totalRevenue' => $totalRevenue,
            'totalCogs' => $totalCogs,
            'totalGrossProfit' => $totalGrossProfit,
        ]);
    }


        
    /**
     * Display a thermal receipt for a posted LPG sale.
     *
     * Management users with sales.view may access receipts.
     * Sales Agents with sales.create may access only their
     * own sales from their assigned branches.
     */
    public function receipt(
        \Illuminate\Http\Request $request,
        \App\Models\LpgSale $sale
    ): \Illuminate\View\View {

        $user = $request->user();

        abort_unless($user, 403);

        $canViewSales = $user->hasPermission('sales.view');
        $canCreateSales = $user->hasPermission('sales.create');

        abort_unless(
            $canViewSales || $canCreateSales,
            403
        );

        abort_unless(
            $sale->status === \App\Models\LpgSale::STATUS_POSTED,
            404,
            'Receipts are available only for posted LPG sales.'
        );

     
        /*
        * Owner: unrestricted posted-sale receipt access.
        *
        * Manager: receipts from assigned active branches only.
        *
        * Sales Agent: own posted sales from assigned
        * active branches only.
        */
        if ($user->hasRole('manager')) {

            abort_unless(
                $canViewSales,
                403
            );

            abort_unless(
                $user->branches()
                    ->where('branches.id', $sale->branch_id)
                    ->where('branches.status', 'active')
                    ->exists(),
                403
            );

        } elseif ($user->hasRole('attendant')) {

            abort_unless(
                $canCreateSales,
                403
            );

            abort_unless(
                (int) $sale->attendant_id === (int) $user->id,
                403
            );

            abort_unless(
                $user->branches()
                    ->where('branches.id', $sale->branch_id)
                    ->where('branches.status', 'active')
                    ->exists(),
                403
            );

        } else {

            abort_unless(
                $user->hasRole('owner') && $canViewSales,
                403
            );
        }

        $sale->load([
            'branch',
            'storageUnit',
            'attendant',
        ]);

        return view('admin.lpg-sales.receipt', [
            'sale' => $sale,
        ]);
    }


}
