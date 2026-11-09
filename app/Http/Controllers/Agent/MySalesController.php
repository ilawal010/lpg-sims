<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\AccessorySale;
use App\Models\LpgSale;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MySalesController extends Controller
{
    /**
     * Display posted sales belonging exclusively
     * to the authenticated Sales Agent.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless(
            $user && $user->hasRole('attendant'),
            403
        );

        abort_unless(
            $user->hasPermission('sales.create'),
            403
        );

        $lpgSales = LpgSale::query()
            ->select([
                'id',
                'branch_id',
                'attendant_id',
                'sale_date',
                'total_amount',
                'status',
                'created_at',
            ])
            ->with('branch')
            ->where('attendant_id', $user->id)
            ->where('status', LpgSale::STATUS_POSTED)
            ->latest('id')
            ->paginate(15, ['*'], 'lpg_page');

        $accessorySales = AccessorySale::query()
            ->select([
                'id',
                'branch_id',
                'accessory_id',
                'attendant_id',
                'sale_date',
                'total_amount',
                'status',
                'created_at',
            ])
            ->with(['branch', 'accessory'])
            ->where('attendant_id', $user->id)
            ->where('status', AccessorySale::STATUS_POSTED)
            ->latest('id')
            ->paginate(15, ['*'], 'accessory_page');

        return view('agent.sales.index', [
            'lpgSales' => $lpgSales,
            'accessorySales' => $accessorySales,
        ]);
    }
}
