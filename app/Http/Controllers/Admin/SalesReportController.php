<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccessorySale;
use App\Models\Branch;
use App\Models\LpgSale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesReportController extends Controller
{
    /**
     * Authorize reporting access and resolve branch/date filters.
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
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:date_from',
            ],
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

        $branchName = $branchId !== null
            ? $branches->firstWhere('id', $branchId)?->branch_name
            : null;

        return [
            'filters' => $filters,
            'branchId' => $branchId,
            'allowedBranchIds' => $allowedBranchIds,
            'branches' => $branches,
            'branchName' => $branchName,
            'canViewCosts' => $canViewCosts,
        ];
    }

    /**
     * Construct authorized posted-sales queries.
     */
    private function salesQueries(array $context): array
    {
        $lpgQuery = LpgSale::query()
            ->where('status', LpgSale::STATUS_POSTED);

        $accessoryQuery = AccessorySale::query()
            ->where('status', AccessorySale::STATUS_POSTED);

        foreach ([$lpgQuery, $accessoryQuery] as $query) {
            if ($context['allowedBranchIds'] !== null) {
                $query->whereIn(
                    'branch_id',
                    $context['allowedBranchIds']
                );
            }

            if ($context['branchId'] !== null) {
                $query->where(
                    'branch_id',
                    $context['branchId']
                );
            }

            if (! empty($context['filters']['date_from'])) {
                $query->whereDate(
                    'sale_date',
                    '>=',
                    $context['filters']['date_from']
                );
            }

            if (! empty($context['filters']['date_to'])) {
                $query->whereDate(
                    'sale_date',
                    '<=',
                    $context['filters']['date_to']
                );
            }
        }

        return [$lpgQuery, $accessoryQuery];
    }

    /**
     * Summarize posted transactions.
     *
     * Confidential financial calculations are only queried
     * for the Owner.
     */
    private function categorySummary(
        Builder $query,
        string $quantityColumn,
        bool $canViewCosts
    ): array {
        $summaryQuery = (clone $query)
            ->selectRaw('COUNT(*) as transactions')
            ->selectRaw(
                "COALESCE(SUM({$quantityColumn}), 0) as quantity"
            )
            ->selectRaw(
                'COALESCE(SUM(total_amount), 0) as revenue'
            );

        if ($canViewCosts) {
            $summaryQuery
                ->selectRaw(
                    'COALESCE(SUM(cost_of_goods_sold), 0) as cogs'
                )
                ->selectRaw(
                    'COALESCE(SUM(gross_profit), 0) as gross_profit'
                );
        }

        $record = $summaryQuery->first();

        $summary = [
            'transactions' => (int) ($record->transactions ?? 0),
            'quantity' => (float) ($record->quantity ?? 0),
            'revenue' => (float) ($record->revenue ?? 0),
        ];

        if ($canViewCosts) {
            $summary['cogs'] = (float) ($record->cogs ?? 0);
            $summary['gross_profit'] =
                (float) ($record->gross_profit ?? 0);
        }

        return $summary;
    }

    /**
     * Build the common report summaries.
     */
    private function reportSummaries(
        Builder $lpgQuery,
        Builder $accessoryQuery,
        bool $canViewCosts
    ): array {
        $lpgSummary = $this->categorySummary(
            $lpgQuery,
            'quantity_kg',
            $canViewCosts
        );

        $accessorySummary = $this->categorySummary(
            $accessoryQuery,
            'quantity',
            $canViewCosts
        );

        $summary = [
            'transactions' =>
                $lpgSummary['transactions']
                + $accessorySummary['transactions'],

            'revenue' =>
                $lpgSummary['revenue']
                + $accessorySummary['revenue'],
        ];

        if ($canViewCosts) {
            $summary['cogs'] =
                $lpgSummary['cogs']
                + $accessorySummary['cogs'];

            $summary['gross_profit'] =
                $lpgSummary['gross_profit']
                + $accessorySummary['gross_profit'];

            $summary['gross_margin'] = $summary['revenue'] > 0
                ? (
                    $summary['gross_profit']
                    / $summary['revenue']
                ) * 100
                : 0;
        }

        return [
            $lpgSummary,
            $accessorySummary,
            $summary,
        ];
    }

    /**
     * Select transaction columns according to the user's role.
     */
    private function transactionColumns(
        bool $canViewCosts,
        bool $isLpg
    ): array {
        $columns = [
            'id',
            'sale_number',
            'sale_date',
            'branch_id',
            'attendant_id',
            'total_amount',
            $isLpg ? 'quantity_kg' : 'quantity',
        ];

        if (! $isLpg) {
            $columns[] = 'accessory_id';
        }

        if ($canViewCosts) {
            $columns[] = 'cost_of_goods_sold';
            $columns[] = 'gross_profit';
        }

        return $columns;
    }

    /**
     * Format a transaction for the on-screen report.
     */
    private function screenSale(
        $sale,
        bool $isLpg,
        bool $canViewCosts
    ): array {
        $item = [
            'type' => $isLpg ? 'LPG' : 'Accessory',
            'sale_number' => $sale->sale_number,
            'sale_date' => $sale->sale_date,
            'branch' => $sale->branch?->branch_name,
            'attendant' => $sale->attendant?->name,
            'quantity' => $isLpg
                ? $sale->quantity_kg
                : $sale->quantity,
            'unit' => $isLpg ? 'kg' : 'units',
            'revenue' => $sale->total_amount,
        ];

        if ($canViewCosts) {
            $item['cogs'] = $sale->cost_of_goods_sold;
            $item['gross_profit'] = $sale->gross_profit;
        }

        return $item;
    }

    /**
     * Display consolidated Sales Reports.
     */
    public function index(Request $request): View
    {
        $context = $this->reportContext($request);

        [$lpgQuery, $accessoryQuery] =
            $this->salesQueries($context);

        $canViewCosts = $context['canViewCosts'];

        [
            $lpgSummary,
            $accessorySummary,
            $summary,
        ] = $this->reportSummaries(
            $lpgQuery,
            $accessoryQuery,
            $canViewCosts
        );

        $lpgSales = (clone $lpgQuery)
            ->select(
                $this->transactionColumns($canViewCosts, true)
            )
            ->with(['branch', 'attendant'])
            ->orderByDesc('sale_date')
            ->orderByDesc('id')
            ->limit(25)
            ->get();

        $accessorySales = (clone $accessoryQuery)
            ->select(
                $this->transactionColumns($canViewCosts, false)
            )
            ->with(['branch', 'attendant', 'accessory'])
            ->orderByDesc('sale_date')
            ->orderByDesc('id')
            ->limit(25)
            ->get();

        $sales = collect()
            ->concat(
                $lpgSales->map(
                    fn ($sale) => $this->screenSale(
                        $sale,
                        true,
                        $canViewCosts
                    )
                )
            )
            ->concat(
                $accessorySales->map(
                    fn ($sale) => $this->screenSale(
                        $sale,
                        false,
                        $canViewCosts
                    )
                )
            )
            ->sortByDesc('sale_date')
            ->take(25)
            ->values();

        return view('admin.reports.sales.index', [
            'filters' => $context['filters'],
            'branches' => $context['branches'],
            'lpgSummary' => $lpgSummary,
            'accessorySummary' => $accessorySummary,
            'summary' => $summary,
            'sales' => $sales,
            'canViewCosts' => $canViewCosts,
        ]);
    }

    /**
     * Export authorized posted sales to CSV.
     */
    public function exportCsv(Request $request)
    {
        $context = $this->reportContext($request);

        [$lpgQuery, $accessoryQuery] =
            $this->salesQueries($context);

        $canViewCosts = $context['canViewCosts'];

        $filename = 'intisar-sales-report-'
            . now()->format('Ymd-His')
            . '.csv';

        return response()->streamDownload(
            function () use (
                $lpgQuery,
                $accessoryQuery,
                $canViewCosts
            ) {
                $handle = fopen('php://output', 'w');

                fwrite($handle, "\xEF\xBB\xBF");

                $headings = [
                    'Date',
                    'Item Name',
                    'Category',
                    'Branch',
                    'Sales Agent',
                    'Quantity',
                    'Unit',
                    'Revenue (NGN)',
                ];

                if ($canViewCosts) {
                    $headings[] = 'COGS (NGN)';
                    $headings[] = 'Gross Profit (NGN)';
                }

                $headings[] = 'Payment Method';
                $headings[] = 'Payment Reference';

                fputcsv($handle, $headings);

                $safeText = static function ($value): string {
                    $text = (string) ($value ?? '');

                    if (preg_match('/^[\s]*[=+\-@]/u', $text)) {
                        return "'" . $text;
                    }

                    return $text;
                };

                $lpgColumns = [
                    'id',
                    'sale_date',
                    'branch_id',
                    'attendant_id',
                    'quantity_kg',
                    'total_amount',
                    'payment_method',
                    'payment_reference',
                ];

                if ($canViewCosts) {
                    $lpgColumns[] = 'cost_of_goods_sold';
                    $lpgColumns[] = 'gross_profit';
                }

                foreach (
                    (clone $lpgQuery)
                        ->select($lpgColumns)
                        ->with(['branch', 'attendant'])
                        ->orderBy('id')
                        ->cursor() as $sale
                ) {
                    $row = [
                        $sale->sale_date?->format('Y-m-d'),
                        'LPG',
                        'LPG',
                        $safeText($sale->branch?->branch_name),
                        $safeText($sale->attendant?->name),
                        $sale->quantity_kg,
                        'kg',
                        $sale->total_amount,
                    ];

                    if ($canViewCosts) {
                        $row[] = $sale->cost_of_goods_sold;
                        $row[] = $sale->gross_profit;
                    }

                    $row[] = $safeText($sale->payment_method);
                    $row[] = $safeText($sale->payment_reference);

                    fputcsv($handle, $row);
                }

                $accessoryColumns = [
                    'id',
                    'sale_date',
                    'branch_id',
                    'attendant_id',
                    'accessory_id',
                    'quantity',
                    'total_amount',
                ];

                if ($canViewCosts) {
                    $accessoryColumns[] = 'cost_of_goods_sold';
                    $accessoryColumns[] = 'gross_profit';
                }

                foreach (
                    (clone $accessoryQuery)
                        ->select($accessoryColumns)
                        ->with([
                            'branch',
                            'attendant',
                            'accessory',
                        ])
                        ->orderBy('id')
                        ->cursor() as $sale
                ) {
                    $row = [
                        $sale->sale_date?->format('Y-m-d'),
                        $safeText(
                            $sale->accessory?->name
                                ?? 'Unknown Accessory'
                        ),
                        'Accessory',
                        $safeText($sale->branch?->branch_name),
                        $safeText($sale->attendant?->name),
                        $sale->quantity,
                        'units',
                        $sale->total_amount,
                    ];

                    if ($canViewCosts) {
                        $row[] = $sale->cost_of_goods_sold;
                        $row[] = $sale->gross_profit;
                    }

                    $row[] = '';
                    $row[] = '';

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
     * Format a transaction for the PDF report.
     */
    private function pdfSale(
        $sale,
        bool $isLpg,
        bool $canViewCosts
    ): array {
        $item = [
            'date' => $sale->sale_date?->format('Y-m-d'),
            'item_name' => $isLpg
                ? 'LPG'
                : ($sale->accessory?->name ?? 'Unknown Accessory'),
            'category' => $isLpg ? 'LPG' : 'Accessory',
            'branch' => $sale->branch?->branch_name,
            'attendant' => $sale->attendant?->name,
            'quantity' => (
                $isLpg ? $sale->quantity_kg : $sale->quantity
            ) . ($isLpg ? ' kg' : ' units'),
            'revenue' => $sale->total_amount,
        ];

        if ($canViewCosts) {
            $item['cogs'] = $sale->cost_of_goods_sold;
            $item['gross_profit'] = $sale->gross_profit;
        }

        return $item;
    }

    /**
     * Export authorized posted sales to PDF.
     */
    public function exportPdf(Request $request)
    {
        $context = $this->reportContext($request);

        [$lpgQuery, $accessoryQuery] =
            $this->salesQueries($context);

        $canViewCosts = $context['canViewCosts'];

        [
            $lpgSummary,
            $accessorySummary,
            $summary,
        ] = $this->reportSummaries(
            $lpgQuery,
            $accessoryQuery,
            $canViewCosts
        );

        $lpgSales = (clone $lpgQuery)
            ->select(
                $this->transactionColumns($canViewCosts, true)
            )
            ->with(['branch', 'attendant'])
            ->orderBy('sale_date')
            ->orderBy('id')
            ->get();

        $accessorySales = (clone $accessoryQuery)
            ->select(
                $this->transactionColumns($canViewCosts, false)
            )
            ->with(['branch', 'attendant', 'accessory'])
            ->orderBy('sale_date')
            ->orderBy('id')
            ->get();

        $sales = collect()
            ->concat(
                $lpgSales->map(
                    fn ($sale) => $this->pdfSale(
                        $sale,
                        true,
                        $canViewCosts
                    )
                )
            )
            ->concat(
                $accessorySales->map(
                    fn ($sale) => $this->pdfSale(
                        $sale,
                        false,
                        $canViewCosts
                    )
                )
            )
            ->sortBy('date')
            ->values();

        $pdf = Pdf::loadView(
            'admin.reports.sales.pdf',
            [
                'filters' => $context['filters'],
                'branchName' => $context['branchName'],
                'lpgSummary' => $lpgSummary,
                'accessorySummary' => $accessorySummary,
                'summary' => $summary,
                'sales' => $sales,
                'canViewCosts' => $canViewCosts,
            ]
        )->setPaper('a4', 'landscape');

        return $pdf->download(
            'intisar-sales-report-'
            . now()->format('Ymd-His')
            . '.pdf'
        );
    }
}
