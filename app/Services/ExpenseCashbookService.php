<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\CashbookTransaction;
use App\Models\Expense;
use Illuminate\Validation\ValidationException;

class ExpenseCashbookService
{
    /** Call inside the expense creation database transaction. */
    public function record(Expense $expense): CashbookTransaction
    {
        if (!$expense->isRecorded()) {
            throw ValidationException::withMessages(['expense' => 'Only recorded expenses may be posted.']);
        }

        // Serialize postings for this branch without requiring an opening balance.
        $branch = Branch::query()->whereKey($expense->branch_id)->lockForUpdate()->firstOrFail();
        if ($branch->status !== 'active') {
            throw ValidationException::withMessages(['branch_id' => 'Cannot post to an inactive branch.']);
        }

        $amount = (string) $expense->amount;
        if (bccomp($amount, '0', 2) <= 0) {
            throw ValidationException::withMessages(['amount' => 'Expense amount must be positive.']);
        }
        if (!in_array($expense->payment_method, [
            CashbookTransaction::PAYMENT_CASH,
            CashbookTransaction::PAYMENT_BANK_TRANSFER,
            CashbookTransaction::PAYMENT_POS,
            CashbookTransaction::PAYMENT_OTHER,
        ], true)) {
            throw ValidationException::withMessages(['payment_method' => 'Invalid payment method.']);
        }

        $existing = CashbookTransaction::query()
            ->where('source_type', CashbookTransaction::SOURCE_EXPENSE)
            ->where('source_id', $expense->id)
            ->lockForUpdate()->first();

        if ($existing) {
            if ((int) $existing->branch_id !== (int) $expense->branch_id
                || $existing->transaction_date->toDateString() !== $expense->expense_date->toDateString()
                || $existing->transaction_type !== CashbookTransaction::TYPE_PAYMENT
                || $existing->status !== CashbookTransaction::STATUS_RECORDED
                || $existing->is_manual
                || bccomp((string) $existing->amount, $amount, 2) !== 0
                || $existing->payment_method !== $expense->payment_method
                || $existing->payment_reference !== $expense->payment_reference
            ) {
                throw ValidationException::withMessages(['cashbook' => 'The linked cashbook payment does not match this expense.']);
            }
            return $existing;
        }

        return CashbookTransaction::query()->create([
            'branch_id' => $expense->branch_id,
            'transaction_date' => $expense->expense_date->toDateString(),
            'transaction_number' => 'CB-EXP-' . $expense->id,
            'transaction_type' => CashbookTransaction::TYPE_PAYMENT,
            'category' => $expense->category,
            'description' => 'Expense ' . $expense->expense_number . ': ' . $expense->description,
            'amount' => $amount,
            'payment_method' => $expense->payment_method,
            'payment_reference' => $expense->payment_reference,
            'source_type' => CashbookTransaction::SOURCE_EXPENSE,
            'source_id' => $expense->id,
            'is_manual' => false,
            'status' => CashbookTransaction::STATUS_RECORDED,
            'created_by' => $expense->created_by,
        ]);
    }

    /** Call inside the expense void database transaction, after locking the expense. */
    public function void(Expense $expense, int $userId, string $reason): void
    {
        $entry = CashbookTransaction::query()
            ->where('source_type', CashbookTransaction::SOURCE_EXPENSE)
            ->where('source_id', $expense->id)
            ->lockForUpdate()->first();

        // Historical expenses may predate cashbook integration. Do not silently
        // void an expense without a linked payment; reconcile it first.
        if (!$entry) {
            throw ValidationException::withMessages([
                'cashbook' => 'This expense has no linked cashbook payment. Reconcile its historical posting before voiding.',
            ]);
        }
        if ((int) $entry->branch_id !== (int) $expense->branch_id
            || $entry->transaction_type !== CashbookTransaction::TYPE_PAYMENT
            || $entry->is_manual
        ) {
            throw ValidationException::withMessages(['cashbook' => 'Linked cashbook payment is inconsistent.']);
        }
        if ($entry->status !== CashbookTransaction::STATUS_RECORDED) {
            throw ValidationException::withMessages(['cashbook' => 'Linked cashbook payment is not recorded.']);
        }

        $entry->status = CashbookTransaction::STATUS_VOIDED;
        $entry->voided_by = $userId;
        $entry->voided_at = $expense->voided_at ?? now();
        $entry->void_reason = $reason;
        $entry->save();
    }
}
