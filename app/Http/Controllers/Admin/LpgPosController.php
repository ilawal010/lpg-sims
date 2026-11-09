<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accessory;
use App\Models\LpgPrice;
use App\Models\LpgSale;
use App\Models\LpgStorageUnit;
use App\Services\AccessorySaleService;
use App\Services\LpgSaleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LpgPosController extends Controller
{
    /**
     * Display the unified LPG and Accessories POS.
     */
    public function index(Request $request): View
    {
        abort_unless(
            $request->user()?->hasPermission('sales.create'),
            403
        );

        $branch = $this->assignedBranch($request);

        $storageUnits = LpgStorageUnit::query()
            ->where('branch_id', $branch->id)
            ->where('status', 'active')
            ->orderBy('storage_name')
            ->get();

        $accessories = Accessory::query()
            ->where('branch_id', $branch->id)
            ->where('status', 'active')
            ->where('quantity', '>', 0)
            ->orderBy('name')
            ->get();

        $currentPrice = LpgPrice::query()
            ->whereKey(1)
            ->first();

        /*
         * Preserve compatibility with the existing
         * POS interface and draft listing.
         */
        $branches = collect([$branch]);

        $recentDrafts = LpgSale::query()
            ->where('branch_id', $branch->id)
            ->where('status', LpgSale::STATUS_DRAFT)
            ->when(
                $request->user()->hasRole('attendant'),
                fn ($query) => $query->where(
                    'attendant_id',
                    $request->user()->id
                )
            )
            ->with(['branch', 'storageUnit', 'attendant'])
            ->latest('id')
            ->limit(10)
            ->get();

        return view('admin.lpg-pos.index', [
            'branch' => $branch,
            'branches' => $branches,
            'storageUnits' => $storageUnits,
            'accessories' => $accessories,
            'currentPrice' => $currentPrice,
            'recentDrafts' => $recentDrafts,
            'transactionToken' => (string) Str::uuid(),
            'paymentMethods' => LpgSale::paymentMethods(),
        ]);
    }

    /**
     * Process an LPG or Accessories sale.
     */
    public function store(
        Request $request,
        LpgSaleService $lpgSaleService,
        AccessorySaleService $accessorySaleService
    ): RedirectResponse {
        abort_unless(
            $request->user()?->hasPermission('sales.create'),
            403
        );

        /*
         * Validate common POS transaction and
         * payment information before either service
         * is called.
         */
        $validated = $request->validate([
            'sale_type' => [
                'nullable',
                'in:lpg,accessory',
            ],
            'transaction_token' => [
                'required',
                'uuid',
            ],
            'payment_method' => [
                'required',
                'string',
                'in:cash,bank_transfer,pos',
            ],
            'payment_reference' => [
                'nullable',
                'string',
                'max:100',   
            ],
            'payment_confirmed' => [
                'required',
                'accepted',
            ],
        ], [
            'payment_method.required' =>
                'Please select a payment method.',

            'payment_method.in' =>
                'Select Cash, Bank Transfer or POS Card.',

            'payment_confirmed.required' =>
                'You must confirm successful payment before processing the sale.',

            'payment_confirmed.accepted' =>
                'You must confirm successful payment before processing the sale.',
        ]);

        $saleType = $validated['sale_type'] ?? 'lpg';
        $transactionToken = $validated['transaction_token'];
        $paymentMethod = $validated['payment_method'];

        // A cash sale does not require an external transaction reference.
        // Ignore any stale reference retained when switching payment methods.
        $paymentReference = trim((string) ($validated['payment_reference'] ?? ''));
        $paymentReference = $paymentReference !== '' ? $paymentReference : null;

        if ($paymentMethod === LpgSale::PAYMENT_CASH) {
            $paymentReference = null;
        } elseif (
            in_array($paymentMethod, [
                LpgSale::PAYMENT_BANK_TRANSFER,
                LpgSale::PAYMENT_POS,
            ], true) && $paymentReference === null
        ) {
            throw ValidationException::withMessages([
                'payment_reference' =>
                    'Enter the confirmed bank transfer or POS transaction reference.',
            ]);
        }

        /*
         * Accessories sale.
         */
        if ($saleType === 'accessory') {
            $data = $request->validate([
                'accessory_id' => [
                    'required',
                    'integer',
                    'exists:accessories,id',
                ],
                'quantity' => [
                    'required',
                    'numeric',
                    'gt:0',
                    'decimal:0,3',
                ],
            ]);

            $sale = $accessorySaleService->processSale(
                (int) $data['accessory_id'],
                (string) $data['quantity'],
                (int) $request->user()->id,
                $transactionToken,
                $paymentMethod,
                $paymentReference
            );

            return redirect()
                ->route('admin.accessory-sales.receipt', $sale->id)
                ->with(
                    'success',
                    'Accessories sale processed successfully.'
                );
        }

        /*
         * LPG sale.
         */
        $data = $request->validate([
            'lpg_storage_unit_id' => [
                'required',
                'integer',
                'exists:lpg_storage_units,id',
            ],
            'quantity_kg' => [
                'required',
                'numeric',
                'gt:0',
                'decimal:0,3',
            ],
        ]);

        $branch = $this->assignedBranch($request);

        /*
         * Verify that the selected LPG storage unit
         * belongs to the authenticated user's
         * active assigned branch.
         *
         * Never trust the submitted storage-unit ID
         * merely because the POS form displayed it.
         */
        $authorizedStorageUnit = LpgStorageUnit::query()
            ->whereKey($data['lpg_storage_unit_id'])
            ->where('branch_id', $branch->id)
            ->where('status', 'active')
            ->first();

        if (!$authorizedStorageUnit) {
            throw ValidationException::withMessages([
                'lpg_storage_unit_id' =>
                    'The selected LPG storage unit is not active or does not belong to your assigned branch.',
            ]);
        }

        /*
         * Include agent-confirmed payment information
         * in the existing LPG sale-processing workflow.
         */
        $saleData = [
            'sale_date' => now()->toDateString(),
            'branch_id' => $branch->id,
            'lpg_storage_unit_id' =>
                $data['lpg_storage_unit_id'],
            'attendant_id' => $request->user()->id,
            'quantity_kg' => $data['quantity_kg'],
            'payment_method' => $paymentMethod,
            'payment_reference' => $paymentReference,
        ];

        $sale = $lpgSaleService->processSale(
            $saleData,
            (int) $request->user()->id,
            $transactionToken
        );

        return redirect()
            ->route('admin.lpg-sales.receipt', $sale->id)
            ->with(
                'success',
                'LPG sale processed successfully.'
            );
    }

    /**
     * Retain posting support for previously
     * created LPG drafts.
     */
    public function post(
        Request $request,
        LpgSale $sale,
        LpgSaleService $saleService
    ): RedirectResponse {
        abort_unless(
            $request->user()?->hasPermission('sales.create'),
            403
        );

        $branch = $this->assignedBranch($request);

        // The sale must belong to the user's authorized branch.
        abort_unless(
            (int) $sale->branch_id === (int) $branch->id,
            403
        );

        // Sales Agents may post only their own LPG drafts.
        // Preserve existing Owner access.
        if ($request->user()->hasRole('attendant')) {
            abort_unless(
                (int) $sale->attendant_id ===
                    (int) $request->user()->id,
                403
            );
        }

        if ($sale->status !== LpgSale::STATUS_DRAFT) {
            return redirect()
                ->route('admin.lpg-pos.index')
                ->withErrors([
                    'sale' =>
                        'Only draft LPG sales can be posted.',
                ]);
        }

        $postedSale = $saleService->postSale(
            $sale->id,
            $request->user()->id
        );

        return redirect()
            ->route('admin.lpg-sales.receipt', $postedSale->id)
            ->with(
                'success',
                'LPG sale posted successfully.'
            );
    }

    /**
     * Resolve the user's single active assigned branch.
     */
    private function assignedBranch(Request $request)
    {
        $branches = $request->user()
            ->branches()
            ->where('branches.status', 'active')
            ->get();

        if ($branches->count() !== 1) {
            throw ValidationException::withMessages([
                'branch' =>
                    'Your account must have exactly one active assigned branch before processing sales.',
            ]);
        }

        return $branches->first();
    }
}
