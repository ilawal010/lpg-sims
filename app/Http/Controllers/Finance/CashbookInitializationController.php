<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\CashbookAccount;
use App\Models\CashbookTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CashbookInitializationController extends Controller
{
    private function authorizeOwner(Request $request): void
    {
        $user = $request->user();

        abort_unless(
            $user
            && $user->hasRole('owner')
            && $user->hasPermission('cashbook.view'),
            403
        );
    }

    public function create(Request $request): View
    {
        $this->authorizeOwner($request);

        $branches = Branch::query()
            ->where('status', 'active')
            ->orderBy('branch_name')
            ->get(['id', 'branch_name', 'branch_code']);

        $accounts = CashbookAccount::query()
            ->with('branch:id,branch_name,branch_code')
            ->get()
            ->keyBy('branch_id');

        return view('finance.cashbook.initialize', compact(
            'branches',
            'accounts'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeOwner($request);

        $validated = $request->validate([
            'branch_id' => [
                'required',
                'integer',
                'exists:branches,id',
            ],
            'opening_date' => [
                'required',
                'date_format:Y-m-d',
                'before_or_equal:today',
            ],
            'opening_balance' => [
                'required',
                'numeric',
                'min:0',
                'decimal:0,2',
            ],
            'bank_opening_balance' => [
                'required',
                'numeric',
                'min:0',
                'decimal:0,2',
            ],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $branch = Branch::query()
                ->whereKey($validated['branch_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($branch->status !== 'active') {
                throw ValidationException::withMessages([
                    'branch_id' => 'The selected branch is inactive.',
                ]);
            }

            if (CashbookAccount::query()
                ->where('branch_id', $branch->id)
                ->exists()) {
                throw ValidationException::withMessages([
                    'branch_id' => 'This branch cashbook has already been initialized.',
                ]);
            }

            $earliestTransaction = CashbookTransaction::query()
                ->where('branch_id', $branch->id)
                ->min('transaction_date');

            if (
                $earliestTransaction !== null
                && $validated['opening_date'] > substr(
                    (string) $earliestTransaction,
                    0,
                    10
                )
            ) {
                throw ValidationException::withMessages([
                    'opening_date' =>
                        'The opening date cannot be later than existing cashbook transactions. Reconcile the transaction history first.',
                ]);
            }

            CashbookAccount::query()->create([
                'branch_id' => $branch->id,
                'opening_date' => $validated['opening_date'],
                'opening_balance' => $validated['opening_balance'],
                'bank_opening_balance' => $validated['bank_opening_balance'],
                'created_by' => $request->user()->id,
            ]);
        }, 3);

        return redirect()
            ->route('admin.cashbook.initialize')
            ->with('success', 'Branch cashbook opening balances initialized successfully.');
    }
}
