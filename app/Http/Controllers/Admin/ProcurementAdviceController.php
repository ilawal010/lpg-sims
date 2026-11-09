<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Services\ProcurementAdviceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProcurementAdviceController extends Controller
{
    /**
     * Display the Procurement Advice report.
     */
    public function index(
        Request $request,
        ProcurementAdviceService $adviceService
    ): View {
        $report = $this->prepareReport($request, $adviceService);

        return view(
            'admin.reports.procurement.index',
            $report
        );
    }

    /**
     * Export procurement advice as CSV.
     */
    public function exportCsv(
        Request $request,
        ProcurementAdviceService $adviceService
    ): StreamedResponse {
        $report = $this->prepareReport($request, $adviceService);

        $filename = 'procurement-advice-'
            . now()->format('Ymd-His')
            . '.csv';

        return response()->streamDownload(
            function () use ($report) {
                $handle = fopen('php://output', 'w');

                // UTF-8 BOM for Excel compatibility.
                fwrite($handle, "\xEF\xBB\xBF");

                fputcsv($handle, [
                    'Branch',
                    'Item Type',
                    'Item Code',
                    'Item Name',
                    'Available Stock',
                    'Minimum Stock',
                    'Target Stock',
                    'Quantity to Procure',
                    'Unit',
                    'Priority',
                ]);

                foreach ($report['advice'] as $item) {
                    fputcsv($handle, [
                        $item['branch_name'],
                        strtoupper($item['item_type']),
                        $item['item_code'],
                        $item['item_name'],
                        $item['available_stock'],
                        $item['minimum_stock'],
                        $item['target_stock'],
                        $item['quantity_to_procure'],
                        $item['unit'],
                        $item['priority'],
                    ]);
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
     * Export procurement advice as PDF.
     */
    public function exportPdf(
        Request $request,
        ProcurementAdviceService $adviceService
    ) {
        $report = $this->prepareReport($request, $adviceService);

        $pdf = Pdf::loadView(
            'admin.reports.procurement.pdf',
            $report
        )->setPaper('a4', 'landscape');

        return $pdf->download(
            'procurement-advice-'
            . now()->format('Ymd-His')
            . '.pdf'
        );
    }

    /**
     * Validate access, apply branch restrictions,
     * calculate advice and prepare report data.
     */
    private function prepareReport(
        Request $request,
        ProcurementAdviceService $adviceService
    ): array {
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
            'item_type' => [
                'nullable',
                'in:lpg,accessory',
            ],
            'priority' => [
                'nullable',
                'in:critical,reorder,routine,sufficient,not_configured',
            ],
            'procurement_only' => [
                'nullable',
                'boolean',
            ],
        ]);

        $branchId = $filters['branch_id'] ?? null;

        /*
         * Determine authorized branches.
         */
        $allowedBranchIds = null;

        if (!$user->hasRole('owner')) {
            $allowedBranchIds = $user->branches()
                ->pluck('branches.id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if ($branchId !== null) {
                abort_unless(
                    in_array(
                        (int) $branchId,
                        $allowedBranchIds,
                        true
                    ),
                    403
                );
            }
        }

        /*
         * Load only authorized branch options.
         */
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

        /*
         * Determine branches for calculations.
         */
        $reportBranchIds = $branchId !== null
            ? [(int) $branchId]
            : $branches->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

        /*
         * Generate advice using the existing service.
         */
        $allAdvice = $adviceService->generate(
            $reportBranchIds
        );

        /*
         * Summary uses all authorized items before
         * display filters are applied.
         */
        $summary = [
            'total_items' => $allAdvice->count(),

            'items_to_procure' => $allAdvice
                ->filter(
                    fn ($item) =>
                        ($item['quantity_to_procure'] ?? 0) > 0
                )
                ->count(),

            'critical_items' => $allAdvice
                ->where('status', 'critical')
                ->count(),

            'high_priority_items' => $allAdvice
                ->where('status', 'reorder')
                ->count(),

            'configuration_required' => $allAdvice
                ->where('status', 'not_configured')
                ->count(),
        ];

        /*
         * Apply item type filter.
         */
        $advice = $allAdvice;

        if (!empty($filters['item_type'])) {
            $advice = $advice->where(
                'item_type',
                $filters['item_type']
            );
        }

        /*
         * Apply priority filter.
         */
        if (!empty($filters['priority'])) {
            $advice = $advice->where(
                'status',
                $filters['priority']
            );
        }

        /*
         * Show procurement requirements only.
         */
        if (
            (string) ($filters['procurement_only'] ?? '0')
            === '1'
        ) {
            $advice = $advice->filter(
                fn ($item) =>
                    ($item['quantity_to_procure'] ?? 0) > 0
            );
        }

        return [
            'branches' => $branches,
            'filters' => $filters,
            'advice' => $advice->values(),
            'summary' => $summary,
            'generatedAt' => now(),
        ];
    }
}
