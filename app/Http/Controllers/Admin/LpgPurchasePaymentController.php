<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LpgInventoryPurchase;
use App\Services\LpgPurchasePaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LpgPurchasePaymentController extends Controller
{
    private function authorizeOwner(Request $request): void
    {
        abort_unless($request->user()?->hasRole('owner') && $request->user()?->hasPermission('expenses.create'), 403);
    }

    public function create(Request $request, LpgInventoryPurchase $purchase): View
    {
        $this->authorizeOwner($request);
        $purchase->load(['supplier', 'branch', 'receipt']);
        abort_unless($purchase->branch?->status === 'active', 403);
        $paid = (float) \App\Models\LpgPurchasePayment::where('lpg_inventory_purchase_id', $purchase->id)->sum('amount');
        return view('admin.finance.purchases.payment', [
            'purchase' => $purchase,
            'paid' => $paid,
            'outstanding' => max(0, round((float) $purchase->total_cost - $paid, 2)),
            'transactionToken' => (string) Str::uuid(),
        ]);
    }

    public function store(Request $request, LpgInventoryPurchase $purchase, LpgPurchasePaymentService $service): RedirectResponse
    {
        $this->authorizeOwner($request);
        $data = $request->validate([
            'transaction_token' => ['required', 'uuid'],
            'payment_date' => ['required', 'date_format:Y-m-d'],
            'amount' => ['required', 'regex:/^\d+(?:\.\d{1,2})?$/', 'numeric', 'gt:0'],
            'payment_method' => ['required', Rule::in(['cash', 'bank_transfer', 'pos', 'other'])],
            'payment_reference' => ['nullable', 'string', 'max:150'],
        ]);
        $result = $service->record((int) $purchase->id, $data, (int) $request->user()->id);
        return redirect()->route('admin.expenses.index')->with('success', $result['duplicate']
            ? 'This supplier payment was already recorded; no duplicate Cashbook entry was created.'
            : 'Supplier payment recorded successfully and posted to the Cashbook.');
    }
}
