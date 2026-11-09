<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\LpgStockReceipt;
use App\Models\LpgStorageUnit;
use App\Models\LpgSupplier;
use App\Services\LpgStockReceiptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LpgStockReceiptController extends Controller
{
    public function index(Request $request): View
    {
        $branchIds = $this->authorizedBranchIds($request, 'inventory.view');

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', Rule::in(LpgStockReceipt::STATUSES)],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ]);

        if (!empty($filters['branch_id'])) {
            abort_unless(in_array((int) $filters['branch_id'], $branchIds, true), 403);
            $branchIds = [(int) $filters['branch_id']];
        }

        $query = LpgStockReceipt::query()
            ->whereIn('branch_id', $branchIds)
            ->with([
                'supplier:id,supplier_name',
                'branch:id,branch_name',
                'storageUnit:id,storage_name',
                'createdBy:id,name',
                'postedBy:id,name',
            ]);

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['date_from'])) {
            $query->whereDate('receipt_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('receipt_date', '<=', $filters['date_to']);
        }

        $receipts = $query->orderByDesc('receipt_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $branches = $request->user()->branches()
            ->where('branches.status', 'active')
            ->whereIn('branches.id', $branchIds)
            ->orderBy('branches.branch_name')
            ->get(['branches.id', 'branches.branch_name']);

        return view('manager.inventory.receipts.index', compact('receipts', 'branches', 'filters'));
    }

    public function create(Request $request): View
    {
        $branchIds = $this->authorizedBranchIds($request, 'inventory.receive');

        return view('manager.inventory.receipts.form', [
            'receipt' => null,
            'branches' => $this->branches($request, $branchIds),
            'suppliers' => $this->suppliers(),
            'storageUnits' => $this->storageUnits($branchIds),
        ]);
    }

    public function store(Request $request, LpgStockReceiptService $service): RedirectResponse
    {
        $branchIds = $this->authorizedBranchIds($request, 'inventory.receive');
        $data = $this->validatedReceipt($request, $branchIds);

        $receipt = $service->createDraft($data, (int) $request->user()->id, $branchIds);

        return redirect()->route('manager.stock-receipts.index')
            ->with('success', "Draft receipt {$receipt->receipt_number} created successfully.");
    }

    public function edit(Request $request, LpgStockReceipt $receipt): View
    {
        $branchIds = $this->authorizedBranchIds($request, 'inventory.receive');
        $this->authorizeReceipt($receipt, $branchIds);
        abort_unless($receipt->canBeEdited(), 403);

        return view('manager.inventory.receipts.form', [
            'receipt' => $receipt,
            'branches' => $this->branches($request, $branchIds),
            'suppliers' => $this->suppliers(),
            'storageUnits' => $this->storageUnits($branchIds),
        ]);
    }

    public function update(
        Request $request,
        LpgStockReceipt $receipt,
        LpgStockReceiptService $service
    ): RedirectResponse {
        $branchIds = $this->authorizedBranchIds($request, 'inventory.receive');
        $this->authorizeReceipt($receipt, $branchIds);
        $data = $this->validatedReceipt($request, $branchIds);

        $service->updateDraft((int) $receipt->id, $data, (int) $request->user()->id, $branchIds);

        return redirect()->route('manager.stock-receipts.index')
            ->with('success', 'Draft receipt updated successfully.');
    }

    public function cancel(
        Request $request,
        LpgStockReceipt $receipt,
        LpgStockReceiptService $service
    ): RedirectResponse {
        $branchIds = $this->authorizedBranchIds($request, 'inventory.receive');
        $this->authorizeReceipt($receipt, $branchIds);

        $service->cancelDraft((int) $receipt->id, (int) $request->user()->id, $branchIds);

        return redirect()->route('manager.stock-receipts.index')
            ->with('success', 'Draft receipt cancelled successfully.');
    }

    public function post(
        Request $request,
        LpgStockReceipt $receipt,
        LpgStockReceiptService $service
    ): RedirectResponse {
        $branchIds = $this->authorizedBranchIds($request, 'inventory.receive');
        $this->authorizeReceipt($receipt, $branchIds);

        $service->postReceipt((int) $receipt->id, (int) $request->user()->id, $branchIds);

        return redirect()->route('manager.stock-receipts.index')
            ->with('success', 'LPG receipt posted and stock updated successfully.');
    }

    /** @return array<int> */
    private function authorizedBranchIds(Request $request, string $permission): array
    {
        $user = $request->user();
        abort_unless(
            $user && $user->hasRole('manager') && $user->hasPermission($permission),
            403
        );

        $ids = $user->branches()
            ->where('branches.status', 'active')
            ->pluck('branches.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        abort_if(empty($ids), 403, 'No active branch assignment.');

        return $ids;
    }

    /** @param array<int> $branchIds */
    private function authorizeReceipt(LpgStockReceipt $receipt, array $branchIds): void
    {
        abort_unless(in_array((int) $receipt->branch_id, $branchIds, true), 403);
    }

    /** @param array<int> $branchIds */
    private function validatedReceipt(Request $request, array $branchIds): array
    {
        return $request->validate([
            'receipt_date' => ['required', 'date_format:Y-m-d'],
            'lpg_supplier_id' => ['required', 'integer', Rule::exists('lpg_suppliers', 'id')->where('status', 'active')],
            'branch_id' => ['required', 'integer', Rule::in($branchIds)],
            'lpg_storage_unit_id' => [
                'required',
                'integer',
                Rule::exists('lpg_storage_units', 'id')
                    ->where('branch_id', $request->input('branch_id'))
                    ->where('status', 'active'),
            ],
            'quantity_kg' => ['required', 'regex:/^\d+(?:\.\d{1,3})?$/', 'numeric', 'gt:0'],
            'unit_cost' => ['required', 'regex:/^\d+(?:\.\d{1,4})?$/', 'numeric', 'gte:0'],
            'supplier_invoice_number' => ['nullable', 'string', 'max:150'],
            'delivery_note_number' => ['nullable', 'string', 'max:150'],
            'vehicle_registration_number' => ['nullable', 'string', 'max:100'],
            'driver_name' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    /** @param array<int> $branchIds */
    private function branches(Request $request, array $branchIds): Collection
    {
        return $request->user()->branches()
            ->where('branches.status', 'active')
            ->whereIn('branches.id', $branchIds)
            ->orderBy('branches.branch_name')
            ->get(['branches.id', 'branches.branch_name']);
    }

    private function suppliers(): Collection
    {
        return LpgSupplier::query()
            ->where('status', 'active')
            ->orderBy('supplier_name')
            ->get(['id', 'supplier_name', 'supplier_code']);
    }

    /** @param array<int> $branchIds */
    private function storageUnits(array $branchIds): Collection
    {
        return LpgStorageUnit::query()
            ->whereIn('branch_id', $branchIds)
            ->where('status', 'active')
            ->orderBy('storage_name')
            ->get(['id', 'branch_id', 'storage_name', 'storage_code', 'capacity_kg']);
    }
}
