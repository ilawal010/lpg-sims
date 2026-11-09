<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\CashbookTransaction;
use App\Models\LpgInventoryPurchase;
use App\Models\LpgPurchasePayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LpgPurchasePaymentService
{
    /** Must run as one atomic operation. Locks the purchase before computing its outstanding amount. */
    public function record(int $purchaseId, array $data, int $userId): array
    {
        return DB::transaction(function () use ($purchaseId, $data, $userId) {
            $purchase = LpgInventoryPurchase::query()->whereKey($purchaseId)->lockForUpdate()->firstOrFail();
            $branch = Branch::query()->whereKey($purchase->branch_id)->lockForUpdate()->firstOrFail();
            if ($branch->status !== 'active') {
                throw ValidationException::withMessages(['branch' => 'Payments cannot be posted to an inactive branch.']);
            }

            $token = $data['transaction_token'];
            $existing = LpgPurchasePayment::query()->where('transaction_token', $token)->first();
            if ($existing) {
                if ((int) $existing->lpg_inventory_purchase_id !== $purchaseId || (int) $existing->created_by !== $userId
                    || bccomp((string) $existing->amount, (string) $data['amount'], 2) !== 0
                    || $existing->payment_date->toDateString() !== $data['payment_date']
                    || $existing->payment_method !== $data['payment_method']
                    || $existing->payment_reference !== ($data['payment_reference'] ?? null)) {
                    throw ValidationException::withMessages(['payment' => 'This submission token belongs to another payment.']);
                }
                $this->assertCashbookMatch($existing, $purchase);
                return ['payment' => $existing, 'duplicate' => true];
            }

            $amount = (string) $data['amount'];
            if (bccomp($amount, '0', 2) <= 0) {
                throw ValidationException::withMessages(['amount' => 'Payment must be greater than zero.']);
            }
            $paid = (string) (LpgPurchasePayment::query()
                ->where('lpg_inventory_purchase_id', $purchaseId)->sum('amount'));
            $outstanding = bcsub((string) $purchase->total_cost, $paid, 2);
            if (bccomp($amount, $outstanding, 2) > 0) {
                throw ValidationException::withMessages(['amount' => 'Payment exceeds outstanding purchase balance of ₦' . number_format((float) $outstanding, 2) . '.']);
            }
            // Purchases may have fractional-kobo totals; never collect beyond the two-decimal payable balance.
            if (bccomp($outstanding, '0', 2) <= 0) {
                throw ValidationException::withMessages(['amount' => 'This purchase has no outstanding payable balance.']);
            }

            $payment = LpgPurchasePayment::query()->create([
                'lpg_inventory_purchase_id' => $purchaseId,
                'payment_date' => $data['payment_date'],
                'amount' => $amount,
                'payment_method' => $data['payment_method'],
                'payment_reference' => $data['payment_reference'] ?? null,
                'transaction_token' => $token,
                'created_by' => $userId,
            ]);

            CashbookTransaction::query()->create([
                'branch_id' => $purchase->branch_id,
                'transaction_date' => $data['payment_date'],
                'transaction_number' => 'CB-LPG-PAY-' . $payment->id,
                'transaction_type' => CashbookTransaction::TYPE_PAYMENT,
                'category' => 'LPG Inventory Purchases',
                'description' => 'Supplier payment for LPG purchase #' . $purchase->id,
                'amount' => $amount,
                'payment_method' => $data['payment_method'],
                'payment_reference' => $data['payment_reference'] ?? null,
                'source_type' => 'lpg_purchase_payment',
                'source_id' => $payment->id,
                'is_manual' => false,
                'status' => CashbookTransaction::STATUS_RECORDED,
                'created_by' => $userId,
            ]);

            AuditLog::create([
                'user_id' => $userId,
                'action' => 'create',
                'module' => 'lpg_purchase_payments',
                'description' => 'Recorded LPG supplier payment #' . $payment->id . ' for purchase #' . $purchase->id,
                'record_type' => LpgPurchasePayment::class,
                'record_id' => $payment->id,
                'new_values' => ['purchase_id' => $purchase->id, 'amount' => $amount, 'payment_method' => $payment->payment_method, 'payment_date' => $payment->payment_date->toDateString()],
            ]);

            $newOutstanding = bcsub($outstanding, $amount, 2);
            $purchase->payment_status = bccomp($newOutstanding, '0', 2) <= 0 ? 'paid' : 'partial';
            $purchase->save();

            return ['payment' => $payment, 'duplicate' => false];
        }, 3);
    }

    private function assertCashbookMatch(LpgPurchasePayment $payment, LpgInventoryPurchase $purchase): void
    {
        $entry = CashbookTransaction::query()->where('source_type', 'lpg_purchase_payment')
            ->where('source_id', $payment->id)->first();
        if (!$entry || (int) $entry->branch_id !== (int) $purchase->branch_id
            || $entry->transaction_type !== CashbookTransaction::TYPE_PAYMENT
            || $entry->status !== CashbookTransaction::STATUS_RECORDED
            || $entry->is_manual
            || bccomp((string) $entry->amount, (string) $payment->amount, 2) !== 0) {
            throw ValidationException::withMessages(['cashbook' => 'Existing supplier payment has an inconsistent Cashbook entry.']);
        }
    }
}
