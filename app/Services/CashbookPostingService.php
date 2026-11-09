<?php

namespace App\Services;

use App\Models\AccessorySale;
use App\Models\Branch;
use App\Models\CashbookTransaction;
use App\Models\LpgSale;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CashbookPostingService
{
    public function recordLpgSale(LpgSale $sale): CashbookTransaction
    {
        if (!$sale->isPosted()) {
            throw ValidationException::withMessages([
                'sale' => 'Only posted LPG sales can be entered in the cashbook.',
            ]);
        }

        return $this->recordSale(
            CashbookTransaction::SOURCE_LPG_SALE,
            (int) $sale->id,
            (int) $sale->branch_id,
            $sale->sale_date->toDateString(),
            (string) $sale->sale_number,
            (string) $sale->total_amount,
            $sale->payment_method,
            $sale->payment_reference,
            (int) ($sale->posted_by ?: $sale->created_by)
        );
    }

    public function recordAccessorySale(AccessorySale $sale): CashbookTransaction
    {
        if (!$sale->isPosted()) {
            throw ValidationException::withMessages([
                'sale' => 'Only posted accessory sales can be entered in the cashbook.',
            ]);
        }

        return $this->recordSale(
            CashbookTransaction::SOURCE_ACCESSORY_SALE,
            (int) $sale->id,
            (int) $sale->branch_id,
            $sale->sale_date->toDateString(),
            (string) $sale->sale_number,
            (string) $sale->total_amount,
            $sale->payment_method,
            $sale->payment_reference,
            (int) $sale->created_by
        );
    }

    private function recordSale(
        string $sourceType,
        int $sourceId,
        int $branchId,
        string $date,
        string $saleNumber,
        string $amount,
        ?string $method,
        ?string $reference,
        int $userId
    ): CashbookTransaction {
        return DB::transaction(function () use (
            $sourceType,
            $sourceId,
            $branchId,
            $date,
            $saleNumber,
            $amount,
            $method,
            $reference,
            $userId
        ) {
            /*
             * Lock the existing branch row.
             *
             * This serializes cashbook posting for the branch
             * without requiring an opening balance.
             */
            $branch = Branch::query()
                ->whereKey($branchId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($branch->status !== 'active') {
                throw ValidationException::withMessages([
                    'cashbook' => 'Cannot post to an inactive branch.',
                ]);
            }

            if (bccomp($amount, '0', 2) <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'Cashbook receipt amount must be positive.',
                ]);
            }

            if (!in_array($method, [
                CashbookTransaction::PAYMENT_CASH,
                CashbookTransaction::PAYMENT_BANK_TRANSFER,
                CashbookTransaction::PAYMENT_POS,
            ], true)) {
                throw ValidationException::withMessages([
                    'payment_method' => 'A valid payment method is required for cashbook posting.',
                ]);
            }

            /*
             * Idempotency:
             * An originating sale may have only one cashbook entry.
             */
            $existing = CashbookTransaction::query()
                ->where('source_type', $sourceType)
                ->where('source_id', $sourceId)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                if (
                    (int) $existing->branch_id !== $branchId ||
                    $existing->transaction_date->toDateString() !== $date ||
                    $existing->transaction_type !== CashbookTransaction::TYPE_RECEIPT ||
                    $existing->status !== CashbookTransaction::STATUS_RECORDED ||
                    $existing->is_manual ||
                    bccomp((string) $existing->amount, $amount, 2) !== 0 ||
                    $existing->payment_method !== $method ||
                    $existing->payment_reference !== $reference
                ) {
                    throw ValidationException::withMessages([
                        'cashbook' => 'An inconsistent cashbook entry already exists for this sale.',
                    ]);
                }

                return $existing;
            }

            $prefix = $sourceType === CashbookTransaction::SOURCE_LPG_SALE
                ? 'CB-LPG-'
                : 'CB-AS-';

            return CashbookTransaction::query()->create([
                'branch_id' => $branchId,
                'transaction_date' => $date,
                'transaction_number' => $prefix . $sourceId,
                'transaction_type' => CashbookTransaction::TYPE_RECEIPT,
                'category' => $sourceType === CashbookTransaction::SOURCE_LPG_SALE
                    ? 'LPG Sales'
                    : 'Accessory Sales',
                'description' => 'Payment received for sale ' . $saleNumber,
                'amount' => $amount,
                'payment_method' => $method,
                'payment_reference' => $reference,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'is_manual' => false,
                'status' => CashbookTransaction::STATUS_RECORDED,
                'created_by' => $userId,
            ]);
        }, 3);
    }
}
