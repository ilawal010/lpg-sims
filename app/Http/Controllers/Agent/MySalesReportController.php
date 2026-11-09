<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\AccessorySale;
use App\Models\LpgSale;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;

class MySalesReportController extends Controller
{
    /**
     * Display the authenticated Sales Agent's sales report.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless(
            $user
                && $user->hasRole('attendant')
                && $user->hasPermission('sales.create'),
            403
        );

        $validated = $request->validate([
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $dateFrom = $validated['date_from']
            ?? now()->startOfMonth()->toDateString();

        $dateTo = $validated['date_to']
            ?? now()->toDateString();

        if (Carbon::parse($dateFrom)->gt(Carbon::parse($dateTo))) {
            return back()->withErrors([
                'date_to' => 'The end date must be on or after the start date.',
            ])->withInput();
        }

        $lpgSales = LpgSale::query()
            ->select([
                'id',
                'branch_id',
                'attendant_id',
                'sale_date',
                'total_amount',
                'status',
            ])
            ->with('branch')
            ->where('attendant_id', $user->id)
            ->where('status', LpgSale::STATUS_POSTED)
            ->whereDate('sale_date', '>=', $dateFrom)
            ->whereDate('sale_date', '<=', $dateTo)
            ->orderBy('sale_date')
            ->orderBy('id')
            ->get();

        $accessorySales = AccessorySale::query()
            ->select([
                'id',
                'branch_id',
                'accessory_id',
                'attendant_id',
                'sale_date',
                'total_amount',
                'status',
            ])
            ->with(['branch', 'accessory'])
            ->where('attendant_id', $user->id)
            ->where('status', AccessorySale::STATUS_POSTED)
            ->whereDate('sale_date', '>=', $dateFrom)
            ->whereDate('sale_date', '<=', $dateTo)
            ->orderBy('sale_date')
            ->orderBy('id')
            ->get();

        $lpgRevenue = (float) $lpgSales->sum('total_amount');
        $accessoryRevenue = (float) $accessorySales->sum('total_amount');

        return view('agent.reports.sales.index', [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'lpgSales' => $lpgSales,
            'accessorySales' => $accessorySales,
            'lpgRevenue' => $lpgRevenue,
            'accessoryRevenue' => $accessoryRevenue,
            'totalRevenue' => $lpgRevenue + $accessoryRevenue,
            'totalTransactions' => $lpgSales->count() + $accessorySales->count(),
        ]);
    }


    /**
     * Generate a PDF report containing only the
     * authenticated Sales Agent's posted sales.
     */
    public function exportPdf(Request $request)
    {
        $agent = $request->user();

        abort_unless(
            $agent
                && $agent->hasRole('attendant')
                && $agent->hasPermission('sales.create'),
            403
        );

        $validated = $request->validate([
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $dateFrom = $validated['date_from']
            ?? now()->startOfMonth()->toDateString();

        $dateTo = $validated['date_to']
            ?? now()->toDateString();

        if (Carbon::parse($dateFrom)->gt(Carbon::parse($dateTo))) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'date_to' => 'The end date must be on or after the start date.',
            ]);
        }

        $lpgSales = LpgSale::query()
            ->select([
                'id',
                'branch_id',
                'attendant_id',
                'sale_date',
                'total_amount',
                'status',
            ])
            ->with('branch')
            ->where('attendant_id', $agent->id)
            ->where('status', LpgSale::STATUS_POSTED)
            ->whereDate('sale_date', '>=', $dateFrom)
            ->whereDate('sale_date', '<=', $dateTo)
            ->orderBy('sale_date')
            ->orderBy('id')
            ->get();

        $accessorySales = AccessorySale::query()
            ->select([
                'id',
                'branch_id',
                'accessory_id',
                'attendant_id',
                'sale_date',
                'total_amount',
                'status',
            ])
            ->with(['branch', 'accessory'])
            ->where('attendant_id', $agent->id)
            ->where('status', AccessorySale::STATUS_POSTED)
            ->whereDate('sale_date', '>=', $dateFrom)
            ->whereDate('sale_date', '<=', $dateTo)
            ->orderBy('sale_date')
            ->orderBy('id')
            ->get();

        $lpgRevenue = (float) $lpgSales->sum('total_amount');
        $accessoryRevenue = (float) $accessorySales->sum('total_amount');

        $pdf = Pdf::loadView('agent.reports.sales.pdf', [
            'agent' => $agent,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'lpgSales' => $lpgSales,
            'accessorySales' => $accessorySales,
            'lpgRevenue' => $lpgRevenue,
            'accessoryRevenue' => $accessoryRevenue,
            'totalRevenue' => $lpgRevenue + $accessoryRevenue,
            'totalTransactions' => $lpgSales->count() + $accessorySales->count(),
        ])->setPaper('a4', 'portrait');

        $filename = 'my-sales-report-'
            . $dateFrom . '-to-' . $dateTo . '.pdf';

        return $pdf->stream($filename);
    }

}
