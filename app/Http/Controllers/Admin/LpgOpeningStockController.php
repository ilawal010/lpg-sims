<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\LpgStockMovement;
use App\Models\LpgStorageUnit;
use App\Services\LpgOpeningStockService;
use App\Services\LpgStockBalanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LpgOpeningStockController extends Controller
{
    /**
     * Display storage units and their opening stock status.
     */
    public function index(Request $request): View
    {
        $this->authorizeManagement($request);

        $query = LpgStorageUnit::query()
            ->with('branch')
            ->withCount('stockMovements');

        if ($request->filled('branch_id')) {
            $query->where(
                'branch_id',
                $request->input('branch_id')
            );
        }

        if (in_array(
            $request->input('storage_type'),
            ['tank', 'cylinder'],
            true
        )) {
            $query->where(
                'storage_type',
                $request->input('storage_type')
            );
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where(
                    'storage_code',
                    'like',
                    "%{$search}%"
                )->orWhere(
                    'storage_name',
                    'like',
                    "%{$search}%"
                );
            });
        }

        $storageUnits = $query
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $branches = Branch::query()
            ->orderBy('branch_name')
            ->get();

        $balanceService = app(
            LpgStockBalanceService::class
        );

        $balances = [];

        foreach ($storageUnits as $unit) {
            $balances[$unit->id] =
                $balanceService->getBalance($unit->id);
        }

        return view(
            'admin.opening-stock.index',
            compact(
                'storageUnits',
                'branches',
                'balances'
            )
        );
    }

    /**
     * Display the opening stock posting form.
     */
    public function create(
        Request $request,
        LpgStorageUnit $storageUnit
    ): View {
        $this->authorizeManagement($request);

        $storageUnit->load('branch');

        if (
            $storageUnit->status !== 'active' ||
            !$storageUnit->branch ||
            $storageUnit->branch->status !== 'active'
        ) {
            throw ValidationException::withMessages([
                'storage_unit' =>
                    'Opening stock requires an active storage unit in an active branch.',
            ]);
        }

        if (
            LpgStockMovement::query()
                ->forStorageUnit($storageUnit->id)
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'storage_unit' =>
                    'This storage unit already has stock movements. Opening stock is no longer permitted.',
            ]);
        }

        return view(
            'admin.opening-stock.create',
            compact('storageUnit')
        );
    }

    /**
     * Validate and post opening stock.
     */
    public function store(
        Request $request,
        LpgStorageUnit $storageUnit,
        LpgOpeningStockService $openingStockService
    ): RedirectResponse {
        $this->authorizeManagement($request);

        $validated = $request->validate([
            'quantity_kg' => [
                'required',
                'numeric',
                'gt:0',
                'decimal:0,3',
                'max:999999999999.999',
            ],

            'unit_cost' => [
                'required',
                'numeric',
                'min:0',
                'decimal:0,4',
                'max:99999999999999.9999',
            ],

            'movement_date' => [
                'required',
                'date_format:Y-m-d',
                'before_or_equal:today',
            ],

            'document_reference' => [
                'nullable',
                'string',
                'max:100',
            ],

            'description' => [
                'nullable',
                'string',
                'max:500',
            ],
        ]);

        /*
         * Convert the date to the business day's
         * start for the opening stock entry.
         */
        $movementDate = Carbon::createFromFormat(
            '!Y-m-d',
            $validated['movement_date']
        )->format('Y-m-d H:i:s');

        $openingStockService->post(
            storageUnitId: $storageUnit->id,
            quantityKg: (string) $validated['quantity_kg'],
            unitCost: (string) $validated['unit_cost'],
            movementDate: $movementDate,
            userId: $request->user()->id,
            documentReference:
                $validated['document_reference'] ?? null,
            description:
                $validated['description'] ?? null
        );

        return redirect()
            ->route('admin.opening-stock.index')
            ->with(
                'success',
                'Opening LPG stock posted successfully.'
            );
    }

    /**
     * Restrict opening stock management to the Owner.
     */
    private function authorizeManagement(
        Request $request
    ): void {
        abort_unless(
            $request->user()?->hasRole('owner'),
            403,
            'Only the Owner can manage opening LPG stock.'
        );
    }
}
