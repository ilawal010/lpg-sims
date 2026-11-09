<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccessorySale;
use App\Models\Branch;
use App\Models\LpgSale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinancialReportController extends Controller
{
    /**
     * Authorize access and resolve reporting filters.
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
            'date_from' => ['nullable', 'date'],
            'date_to' => [
                'nullable',
                'date',
                'after_or_equal:date_from',
            ],
            'branch_id' => [
                'nullable',
                'integer',
                'exists:branches,id',
            ],
        ]);

        $dateFrom = $filters['date_from']
            ?? now()->startOfMonth()->toDateString();

        $dateTo = $filters['date_to']
            ?? now()->toDateString();

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
            : 'All Authorized Branches';

        return [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'branchId' => $branchId,
            'branchName' => $branchName,
            'allowedBranchIds' => $allowedBranchIds,
            'branches' => $branches,
            'canViewCosts' => $canViewCosts,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'branch_id' => $branchId,
            ],
        ];
    }

    /**
     * Summarize posted sales for one category.
     *
     * Managers receive revenue and transaction counts only.
     * Confidential cost fields are not queried for Managers.
     */
    private function categorySummary(
        string $model,
        array $context
    ): array {
        $query = $model::query()
            ->where('status', 'posted')
            ->whereDate(
                'sale_date',
                '>=',
                $context['dateFrom']
            )
            ->whereDate(
                'sale_date',
                '<=',
                $context['dateTo']
            );

        if ($context['branchId'] !== null) {
            $query->where(
                'branch_id',
                $context['branchId']
            );
        }

        if ($context['allowedBranchIds'] !== null) {
            $query->whereIn(
                'branch_id',
                $context['allowedBranchIds']
            );
        }

        $query
            ->selectRaw('COUNT(*) as transactions')
            ->selectRaw(
                'COALESCE(SUM(total_amount), 0) as revenue'
            );

        if ($context['canViewCosts']) {
            $query->selectRaw(
                'COALESCE(SUM(cost_of_goods_sold), 0) as cogs'
            );
        }

        $row = $query->first();

        $summary = [
            'transactions' => (int) ($row->transactions ?? 0),
            'revenue' => (float) ($row->revenue ?? 0),
        ];

        if ($context['canViewCosts']) {
            $cogs = (float) ($row->cogs ?? 0);
            $grossProfit = $summary['revenue'] - $cogs;

            $summary['cogs'] = $cogs;
            $summary['gross_profit'] = $grossProfit;
            $summary['margin'] = $summary['revenue'] > 0
                ? ($grossProfit / $summary['revenue']) * 100
                : 0;
        }

        return $summary;
    }

    /**
     * Build the common report dataset.
     */
    private function reportData(array $context): array
    {
        $lpg = $this->categorySummary(
            LpgSale::class,
            $context
        );

        $accessories = $this->categorySummary(
            AccessorySale::class,
            $context
        );

        $summary = [
            'transactions' =>
                $lpg['transactions']
                + $accessories['transactions'],

            'revenue' =>
                $lpg['revenue']
                + $accessories['revenue'],
        ];

        if ($context['canViewCosts']) {
            $summary['cogs'] =
                $lpg['cogs'] + $accessories['cogs'];

            $summary['gross_profit'] =
                $summary['revenue'] - $summary['cogs'];

            $summary['gross_margin'] =
                $summary['revenue'] > 0
                    ? (
                        $summary['gross_profit']
                        / $summary['revenue']
                    ) * 100
                    : 0;
        }

        return [
            'lpg' => $lpg,
            'accessories' => $accessories,
            'summary' => $summary,
        ];
    }

    /**
     * Display the Financial Report.
     */
    public function index(Request $request): View
    {
        $context = $this->reportContext($request);
        $data = $this->reportData($context);

        return view('admin.reports.financial.index', [
            'branches' => $context['branches'],
            'filters' => $context['filters'],
            'lpg' => $data['lpg'],
            'accessories' => $data['accessories'],
            'summary' => $data['summary'],
            'canViewCosts' => $context['canViewCosts'],
        ]);
    }

    /**
     * Export the authorized Financial Report to CSV.
     */
    public function exportCsv(Request $request)
    {
        $context = $this->reportContext($request);
        $data = $this->reportData($context);

        $dateFrom = $context['dateFrom'];
        $dateTo = $context['dateTo'];
        $branchName = $context['branchName'];
        $canViewCosts = $context['canViewCosts'];

        $filename = 'financial-report-'
            . $dateFrom
            . '-to-'
            . $dateTo
            . '.csv';

        return response()->streamDownload(
            function () use (
                $dateFrom,
                $dateTo,
                $branchName,
                $canViewCosts,
                $data
            ) {
                $handle = fopen('php://output', 'w');

                fwrite($handle, "\xEF\xBB\xBF");

                $safeText = static function ($value): string {
                    $text = (string) ($value ?? '');

                    if (preg_match('/^[\s]*[=+\-@]/u', $text)) {
                        return "'" . $text;
                    }

                    return $text;
                };

                fputcsv($handle, ['INTISAR OIL & GAS']);

                fputcsv(
                    $handle,
                    ['FINANCIAL PERFORMANCE REPORT']
                );

                fputcsv($handle, ['From Date', $dateFrom]);
                fputcsv($handle, ['To Date', $dateTo]);

                fputcsv(
                    $handle,
                    ['Branch', $safeText($branchName)]
                );

                fputcsv($handle, []);

                $headings = [
                    'Category',
                    'Transactions',
                    'Revenue (NGN)',
                ];

                if ($canViewCosts) {
                    $headings[] = 'COGS (NGN)';
                    $headings[] = 'Gross Profit (NGN)';
                    $headings[] = 'Gross Margin (%)';
                }

                fputcsv($handle, $headings);

                foreach ([
                    'LPG Sales' => $data['lpg'],
                    'Accessory Sales' => $data['accessories'],
                    'TOTAL' => $data['summary'],
                ] as $category => $item) {
                    $row = [
                        $category,
                        $item['transactions'],
                        number_format(
                            $item['revenue'],
                            2,
                            '.',
                            ''
                        ),
                    ];

                    if ($canViewCosts) {
                        $margin = $item['margin']
                            ?? $item['gross_margin']
                            ?? 0;

                        $row[] = number_format(
                            $item['cogs'],
                            2,
                            '.',
                            ''
                        );

                        $row[] = number_format(
                            $item['gross_profit'],
                            2,
                            '.',
                            ''
                        );

                        $row[] = number_format(
                            $margin,
                            2,
                            '.',
                            ''
                        );
                    }

                    fputcsv($handle, $row);
                }

                fputcsv($handle, []);

                if ($canViewCosts) {
                    fputcsv($handle, [
                        'Note',
                        'Gross profit excludes operating expenses. '
                        . 'This report does not represent net profit '
                        . 'or cash balance.',
                    ]);
                } else {
                    fputcsv($handle, [
                        'Note',
                        'This report reflects posted sales revenue only. '
                        . 'Operating expenses, collections and cash '
                        . 'balances are not included.',
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
     * Export the authorized Financial Report to PDF.
     */
    public function exportPdf(Request $request)
    {
        $context = $this->reportContext($request);
        $data = $this->reportData($context);

        $pdf = Pdf::loadView(
            'admin.reports.financial.pdf',
            [
                'dateFrom' => $context['dateFrom'],
                'dateTo' => $context['dateTo'],
                'branchName' => $context['branchName'],
                'lpg' => $data['lpg'],
                'accessories' => $data['accessories'],
                'summary' => $data['summary'],
                'canViewCosts' => $context['canViewCosts'],
            ]
        )->setPaper('a4', 'landscape');

        return $pdf->download(
            'financial-report-'
            . $context['dateFrom']
            . '-to-'
            . $context['dateTo']
            . '.pdf'
        );
    }
}
