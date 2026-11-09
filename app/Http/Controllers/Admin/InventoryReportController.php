<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accessory;
use App\Models\Branch;
use App\Models\LpgStorageUnit;
use App\Services\LpgStockBalanceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class InventoryReportController extends Controller
{
    /**
     * Authorize access and resolve the user's branches.
     */
    private function reportContext(Request $request): array
    {
        $user = $request->user();

        abort_unless(
            $user
                && $user->hasPermission('reports.view')
                && (
                    $user->hasRole('owner')
                    || $user->hasRole('manager')
                ),
            403
        );

        $filters = $request->validate([
            'branch_id' => [
                'nullable',
                'integer',
                'exists:branches,id',
            ],
        ]);

        $branchId = isset($filters['branch_id'])
            ? (int) $filters['branch_id']
            : null;

        $canViewCosts = $user->hasRole('owner');

        $allowedBranchIds = null;

        if (! $canViewCosts) {
            $allowedBranchIds = $user->branches()
                ->pluck('branches.id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if ($branchId !== null) {
                abort_unless(
                    in_array($branchId, $allowedBranchIds, true),
                    403
                );
            }
        }

        $branches = Branch::query()
            ->when(
                $allowedBranchIds !== null,
                fn ($query) => $query->whereIn(
                    'id',
                    $allowedBranchIds
                )
            )
            ->orderBy('branch_name')
            ->get();

        $branchLabel = 'All Authorized Branches';

        if ($branchId !== null) {
            $branchLabel = $branches
                ->firstWhere('id', $branchId)
                ?->branch_name ?? 'Selected Branch';
        }

        return [
            'filters' => $filters,
            'branchId' => $branchId,
            'allowedBranchIds' => $allowedBranchIds,
            'branches' => $branches,
            'branchLabel' => $branchLabel,
            'canViewCosts' => $canViewCosts,
        ];
    }

    /**
     * Prepare the same authorized inventory dataset for
     * the report page and PDF export.
     */
    private function reportData(
        Request $request,
        LpgStockBalanceService $stockBalanceService
    ): array {
        $context = $this->reportContext($request);

        $branchId = $context['branchId'];
        $allowedBranchIds = $context['allowedBranchIds'];
        $canViewCosts = $context['canViewCosts'];

        $storageUnits = LpgStorageUnit::query()
            ->with('branch')
            ->when(
                $allowedBranchIds !== null,
                fn ($query) => $query->whereIn(
                    'branch_id',
                    $allowedBranchIds
                )
            )
            ->when(
                $branchId !== null,
                fn ($query) => $query->where(
                    'branch_id',
                    $branchId
                )
            )
            ->orderBy('storage_name')
            ->get();

        $lpgStock = $storageUnits->map(
            function ($unit) use ($stockBalanceService) {
                $balance = $stockBalanceService
                    ->getBalance($unit->id);

                $capacity = (float) $unit->capacity_kg;

                return [
                    'storage_unit_id' => $unit->id,
                    'storage_code' => $unit->storage_code,
                    'storage_name' => $unit->storage_name,
                    'storage_type' => $unit->storage_type,
                    'branch' => $unit->branch?->branch_name,
                    'capacity_kg' => $unit->capacity_kg,
                    'balance_kg' => $balance,
                    'utilization' => $capacity > 0
                        ? round(
                            ((float) $balance / $capacity) * 100,
                            2
                        )
                        : 0,
                    'status' => $unit->status,
                ];
            }
        );

        $accessories = Accessory::query()
            ->with('branch')
            ->when(
                $allowedBranchIds !== null,
                fn ($query) => $query->whereIn(
                    'branch_id',
                    $allowedBranchIds
                )
            )
            ->when(
                $branchId !== null,
                fn ($query) => $query->where(
                    'branch_id',
                    $branchId
                )
            )
            ->orderBy('name')
            ->get();

        $accessoryStock = $accessories->map(
            function ($accessory) use ($canViewCosts) {
                $item = [
                    'accessory_number' =>
                        $accessory->accessory_number,
                    'name' => $accessory->name,
                    'category' => $accessory->category,
                    'branch' => $accessory->branch?->branch_name,
                    'unit' => $accessory->unit,
                    'quantity' => $accessory->quantity,
                    'reorder_level' => $accessory->reorder_level,
                    'status' => $accessory->status,
                    'low_stock' =>
                        (float) $accessory->quantity <=
                        (float) $accessory->reorder_level,
                ];

                if ($canViewCosts) {
                    $item['unit_buying_price'] =
                        $accessory->unit_buying_price;

                    $item['unit_selling_price'] =
                        $accessory->unit_selling_price;

                    $item['stock_value'] =
                        $accessory->stock_value;
                }

                return $item;
            }
        );

        $summary = [
            'storage_units' => $lpgStock->count(),

            'lpg_quantity_kg' => $lpgStock->sum(
                fn ($item) => (float) $item['balance_kg']
            ),

            'lpg_capacity_kg' => $lpgStock->sum(
                fn ($item) => (float) $item['capacity_kg']
            ),

            'accessory_items' => $accessoryStock->count(),

            'low_stock_items' => $accessoryStock
                ->filter(fn ($item) => $item['low_stock'])
                ->count(),
        ];

        if ($canViewCosts) {
            $summary['accessory_stock_value'] =
                $accessoryStock->sum(
                    fn ($item) => (float) $item['stock_value']
                );
        }

        return array_merge($context, [
            'lpgStock' => $lpgStock,
            'accessoryStock' => $accessoryStock,
            'summary' => $summary,
            'generatedAt' => now(),
        ]);
    }

    /**
     * Display the inventory report.
     */
    public function index(
        Request $request,
        LpgStockBalanceService $stockBalanceService
    ): View {
        $report = $this->reportData(
            $request,
            $stockBalanceService
        );

        return view('admin.reports.inventory.index', $report);
    }

    /**
     * Export authorized inventory data to CSV.
     */
    public function exportCsv(
        Request $request,
        LpgStockBalanceService $stockBalanceService
    ) {
        $report = $this->reportData(
            $request,
            $stockBalanceService
        );

        $lpgStock = $report['lpgStock'];
        $accessoryStock = $report['accessoryStock'];
        $canViewCosts = $report['canViewCosts'];
        $generatedAt = $report['generatedAt'];

        $filename = 'inventory-report-'
            . $generatedAt->format('Ymd-His')
            . '.csv';

        return response()->streamDownload(
            function () use (
                $lpgStock,
                $accessoryStock,
                $canViewCosts,
                $generatedAt
            ) {
                $handle = fopen('php://output', 'w');

                fwrite($handle, "\xEF\xBB\xBF");

                fputcsv($handle, [
                    'INVENTORY REPORT - CURRENT STOCK POSITION',
                ]);

                fputcsv($handle, [
                    'Generated At',
                    $generatedAt->format('Y-m-d H:i:s'),
                ]);

                fputcsv($handle, []);

                fputcsv($handle, ['LPG STOCK POSITION']);

                fputcsv($handle, [
                    'Storage Code',
                    'Storage Unit',
                    'Type',
                    'Branch',
                    'Capacity (kg)',
                    'Available (kg)',
                    'Utilization (%)',
                    'Status',
                ]);

                foreach ($lpgStock as $item) {
                    fputcsv($handle, [
                        $item['storage_code'],
                        $item['storage_name'],
                        $item['storage_type'],
                        $item['branch'],
                        $item['capacity_kg'],
                        $item['balance_kg'],
                        $item['utilization'],
                        $item['status'],
                    ]);
                }

                fputcsv($handle, []);
                fputcsv($handle, ['ACCESSORY INVENTORY']);

                $headings = [
                    'Accessory Number',
                    'Item Name',
                    'Category',
                    'Branch',
                    'Unit',
                    'Quantity',
                    'Reorder Level',
                ];

                if ($canViewCosts) {
                    $headings[] = 'Unit Buying Price (NGN)';
                    $headings[] = 'Stock Value (NGN)';
                }

                $headings[] = 'Status';

                fputcsv($handle, $headings);

                foreach ($accessoryStock as $item) {
                    $row = [
                        $item['accessory_number'],
                        $item['name'],
                        $item['category'],
                        $item['branch'],
                        $item['unit'],
                        $item['quantity'],
                        $item['reorder_level'],
                    ];

                    if ($canViewCosts) {
                        $row[] = $item['unit_buying_price'];
                        $row[] = $item['stock_value'];
                    }

                    $row[] = $item['status'];

                    fputcsv($handle, $row);
                }

                fclose($handle);
            },
            $filename,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]
        );
    }

    /**
     * Export authorized inventory data to PDF.
     */
    public function exportPdf(
        Request $request,
        LpgStockBalanceService $stockBalanceService
    ) {
        $report = $this->reportData(
            $request,
            $stockBalanceService
        );

        $pdf = Pdf::loadView(
            'admin.reports.inventory.pdf',
            $report
        )->setPaper('a4', 'landscape');

        return $pdf->download(
            'inventory-report-'
            . $report['generatedAt']->format('Ymd-His')
            . '.pdf'
        );
    }
}
