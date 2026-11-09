<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\LpgInventoryPurchase;
use App\Models\LpgStockMovement;
use App\Models\LpgStockReceipt;
use App\Models\LpgStorageUnit;
use App\Models\LpgSupplier;
use App\Services\LpgInventoryCostingService;
use App\Services\LpgStockReceiptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LpgStockController extends Controller
{
    private function authorizeOwner(Request $request): void
    {
        abort_unless(
            $request->user()?->hasRole('owner')
            && $request->user()?->hasPermission('inventory.view'),
            403
        );
    }

    private function receiptRules(): array
    {
        return [
            'receipt_date' => ['required', 'date_format:Y-m-d'],
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'lpg_supplier_id' => ['required', 'integer', 'exists:lpg_suppliers,id'],
            'lpg_storage_unit_id' => [
                'required', 'integer', 'exists:lpg_storage_units,id'
            ],
            'quantity_kg' => [
                'required', 'regex:/^\d+(?:\.\d{1,3})?$/'
            ],
            'unit_cost' => [
                'required', 'regex:/^\d+(?:\.\d{1,4})?$/'
            ],
            'supplier_invoice_number' => [
                'nullable', 'string', 'max:100'
            ],
            'delivery_note_number' => [
                'nullable', 'string', 'max:100'
            ],
            'vehicle_registration_number' => [
                'nullable', 'string', 'max:50'
            ],
            'driver_name' => [
                'nullable', 'string', 'max:150'
            ],
            'notes' => [
                'nullable', 'string', 'max:2000'
            ],
        ];
    }

    private function formData(): array
    {
        return [
            'branches' => Branch::query()
                ->where('status', 'active')
                ->orderBy('branch_name')
                ->get(),

            'suppliers' => LpgSupplier::query()
                ->where('status', 'active')
                ->orderBy('supplier_name')
                ->get(),

            'storageUnits' => LpgStorageUnit::query()
                ->where('status', 'active')
                ->orderBy('storage_name')
                ->get(),
        ];
    }

    public function create(Request $request): View
    {
        $this->authorizeOwner($request);

        return view('admin.lpg-stock.add', $this->formData());
    }

    public function store(
        Request $request,
        LpgStockReceiptService $service
    ): RedirectResponse {
        $this->authorizeOwner($request);

        $data = $request->validate($this->receiptRules());

        $receipt = $service->createDraft(
            $data,
            (int) $request->user()->id
        );

        return redirect()
            ->route('admin.lpg-stock.index')
            ->with(
                'success',
                'Draft receipt ' . $receipt->receipt_number
                . ' saved successfully. Review and post it '
                . 'to update stock and register the purchase.'
            );
    }

    public function edit(
        Request $request,
        LpgStockReceipt $receipt
    ): View {
        $this->authorizeOwner($request);

        abort_unless(
            $receipt->canBeEdited()
            && $receipt->stock_movement_id === null
            && $receipt->posted_at === null,
            403,
            'Only unposted draft receipts can be edited.'
        );

        return view(
            'admin.lpg-stock.edit',
            array_merge($this->formData(), [
                'receipt' => $receipt,
            ])
        );
    }

    public function update(
        Request $request,
        LpgStockReceipt $receipt,
        LpgStockReceiptService $service
    ): RedirectResponse {
        $this->authorizeOwner($request);

        $data = $request->validate($this->receiptRules());

        $updated = $service->updateDraft(
            (int) $receipt->id,
            $data,
            (int) $request->user()->id
        );

        return redirect()
            ->route('admin.lpg-stock.index')
            ->with(
                'success',
                'Draft receipt ' . $updated->receipt_number
                . ' updated successfully. No inventory or '
                . 'purchase posting has occurred.'
            );
    }

    public function cancel(
        Request $request,
        LpgStockReceipt $receipt,
        LpgStockReceiptService $service
    ): RedirectResponse {
        $this->authorizeOwner($request);

        $cancelled = $service->cancelDraft(
            (int) $receipt->id,
            (int) $request->user()->id
        );

        return redirect()
            ->route('admin.lpg-stock.index')
            ->with(
                'success',
                'Draft receipt ' . $cancelled->receipt_number
                . ' cancelled successfully. No stock or '
                . 'purchase balances were changed.'
            );
    }

    public function post(
        Request $request,
        LpgStockReceipt $receipt,
        LpgStockReceiptService $service
    ): RedirectResponse {
        $this->authorizeOwner($request);

        try {
            DB::transaction(function () use (
                $receipt,
                $request,
                $service
            ) {
                $posted = $service->postReceipt(
                    (int) $receipt->id,
                    (int) $request->user()->id
                );

                LpgInventoryPurchase::create([
                    'lpg_stock_receipt_id' => $posted->id,
                    'branch_id' => $posted->branch_id,
                    'lpg_supplier_id' => $posted->lpg_supplier_id,
                    'purchase_date' => $posted->receipt_date
                        ->format('Y-m-d'),
                    'quantity_kg' => $posted->quantity_kg,
                    'unit_cost' => $posted->unit_cost,
                    'total_cost' => $posted->total_cost,
                    'payment_status' => 'unpaid',
                    'created_by' => (int) $request->user()->id,
                ]);
            }, 3);

        } catch (ValidationException $exception) {
            return redirect()
                ->route('admin.lpg-stock.index')
                ->with('error',
                    'Stock posting was not completed. '
                    . implode(' ', collect(
                        $exception->errors()
                    )->flatten()->all())
                );
        }

        return redirect()
            ->route('admin.lpg-stock.index')
            ->with(
                'success',
                'LPG stock posted successfully and the '
                . 'inventory purchase registered. '
                . 'No Cashbook payment was recorded.'
            );
    }

    public function index(
        Request $request,
        LpgInventoryCostingService $costingService
    ): View {
        $this->authorizeOwner($request);

        $storageUnits = LpgStorageUnit::query()
            ->with('branch')
            ->orderBy('branch_id')
            ->orderBy('storage_name')
            ->get();

        $stockRows = $storageUnits->map(
            function ($unit) use ($costingService) {
                $valuation = $costingService
                    ->getValuation($unit->id);

                $quantity = (float) (
                    $valuation['quantity_kg'] ?? 0
                );

                $capacity = (float) $unit->capacity_kg;

                return [
                    'storage_unit' => $unit,
                    'quantity_kg' => $quantity,
                    'capacity_kg' => $capacity,
                    'inventory_value' => (float) (
                        $valuation['inventory_value'] ?? 0
                    ),
                    'weighted_average_cost' => (float) (
                        $valuation['weighted_average_cost'] ?? 0
                    ),
                    'movement_count' => (int) (
                        $valuation['movement_count'] ?? 0
                    ),
                    'utilization_percent' => $capacity > 0
                        ? round(
                            ($quantity / $capacity) * 100,
                            2
                        )
                        : 0,
                ];
            }
        );

        $summary = [
            'storage_units' => $stockRows->count(),
            'total_capacity_kg' => $stockRows
                ->sum('capacity_kg'),
            'total_stock_kg' => $stockRows
                ->sum('quantity_kg'),
            'total_inventory_value' => $stockRows
                ->sum('inventory_value'),
        ];

        $summary['utilization_percent'] =
            $summary['total_capacity_kg'] > 0
                ? round(
                    (
                        $summary['total_stock_kg']
                        / $summary['total_capacity_kg']
                    ) * 100,
                    2
                )
                : 0;

        $recentMovements = LpgStockMovement::query()
            ->with(['branch', 'storageUnit', 'createdBy'])
            ->latest('id')
            ->limit(20)
            ->get();

        $draftReceipts = LpgStockReceipt::query()
            ->with(['supplier', 'branch', 'storageUnit'])
            ->where('status', LpgStockReceipt::STATUS_DRAFT)
            ->latest('id')
            ->limit(30)
            ->get();

        $balancesByStorageUnit = $stockRows
            ->mapWithKeys(function ($row) {
                return [
                    (int) $row['storage_unit']->id => [
                        'quantity_kg' => $row['quantity_kg'],
                        'capacity_kg' => $row['capacity_kg'],
                        'available_kg' => max(
                            0,
                            $row['capacity_kg']
                            - $row['quantity_kg']
                        ),
                    ],
                ];
            });

        return view('admin.lpg-stock.index', compact(
            'stockRows',
            'summary',
            'recentMovements',
            'draftReceipts',
            'balancesByStorageUnit'
        ));
    }
}
