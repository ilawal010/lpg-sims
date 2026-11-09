<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Expense;
use App\Services\ExpenseCashbookService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $branchIds = $this->authorizedBranchIds($request, 'expenses.view');

        $filters = $request->validate([
            'branch_id' => ['nullable', 'integer', Rule::in($branchIds)],
            'category' => ['nullable', 'string', 'max:100'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'status' => ['nullable', Rule::in(['recorded', 'voided'])],
        ]);

        $query = Expense::query()
            ->forBranches($branchIds)
            ->with([
                'branch:id,branch_name',
                'createdBy:id,name',
                'voidedBy:id,name',
            ]);

        foreach (['branch_id', 'category', 'status'] as $field) {
            if (!empty($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('expense_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('expense_date', '<=', $filters['date_to']);
        }

        $expenses = $query
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $lpgPurchases = \App\Models\LpgInventoryPurchase::with(['receipt','supplier','branch'])
            ->withSum('payments as total_paid', 'amount')
            ->orderByDesc('purchase_date')->orderByDesc('id')->limit(50)->get();

        return view('admin.finance.expenses.index', [
            'expenses' => $expenses,
            'lpgPurchases' => $lpgPurchases,
            'branches' => $this->branches($request),
            'filters' => $filters,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorizedBranchIds($request, 'expenses.create');

        return view('admin.finance.expenses.form', [
            'branches' => $this->branches($request),
            'transactionToken' => (string) Str::uuid(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $branchIds = $this->authorizedBranchIds($request, 'expenses.create');

        $data = $request->validate([
            'transaction_token' => ['required', 'uuid'],
            'branch_id' => ['required', 'integer', Rule::in($branchIds)],
            'expense_date' => ['required', 'date_format:Y-m-d'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:500'],
            'amount' => [
                'required',
                'regex:/^\d+(?:\.\d{1,2})?$/',
                'numeric',
                'gt:0',
                'lte:9999999999999999.99',
            ],
            'payment_method' => [
                'required',
                Rule::in(['cash', 'bank_transfer', 'pos', 'other']),
            ],
            'payment_reference' => ['nullable', 'string', 'max:150'],
            'payee' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $userId = (int) $request->user()->id;
        $token = $data['transaction_token'];

        unset($data['transaction_token']);

        try {
            $result = Cache::lock('expense-submit:' . $token, 60)
                ->block(15, function () use (
                    $data,
                    $request,
                    $userId,
                    $token,
                    $branchIds
                ) {
                    return DB::transaction(function () use (
                        $data,
                        $request,
                        $userId,
                        $token,
                        $branchIds
                    ) {
                        $existingToken = DB::table('expense_transaction_tokens')
                            ->where('transaction_token', $token)
                            ->lockForUpdate()
                            ->first();

                        if ($existingToken) {
                            abort_unless(
                                (int) $existingToken->user_id === $userId,
                                403
                            );

                            $existingExpense = Expense::query()
                                ->whereKey($existingToken->expense_id)
                                ->whereIn('branch_id', $branchIds)
                                ->first();

                            abort_unless(
                                $existingExpense,
                                409,
                                'Expense transaction is incomplete or unavailable.'
                            );

                            return [
                                'expense' => $existingExpense,
                                'duplicate' => true,
                            ];
                        }

                        DB::table('expense_transaction_tokens')->insert([
                            'transaction_token' => $token,
                            'user_id' => $userId,
                            'expense_id' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        $expense = Expense::create([
                            ...$data,
                            'expense_number' => 'PENDING-' . bin2hex(random_bytes(12)),
                            'status' => 'recorded',
                            'created_by' => $userId,
                        ]);

                        $expense->expense_number = sprintf(
                            'EXP-%s-%08d',
                            now()->format('Y'),
                            $expense->id
                        );

                        $expense->save();

                        app(ExpenseCashbookService::class)->record($expense);

                        DB::table('expense_transaction_tokens')
                            ->where('transaction_token', $token)
                            ->where('user_id', $userId)
                            ->update([
                                'expense_id' => $expense->id,
                                'updated_at' => now(),
                            ]);

                        AuditLog::create([
                            'user_id' => $userId,
                            'action' => 'create',
                            'module' => 'expenses',
                            'description' => 'Recorded expense ' . $expense->expense_number,
                            'record_type' => Expense::class,
                            'record_id' => $expense->id,
                            'new_values' => [
                                'expense_number' => $expense->expense_number,
                                'branch_id' => $expense->branch_id,
                                'expense_date' => $expense->expense_date?->format('Y-m-d'),
                                'category' => $expense->category,
                                'amount' => $expense->amount,
                                'payment_method' => $expense->payment_method,
                                'status' => $expense->status,
                            ],
                            'ip_address' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                        ]);

                        return [
                            'expense' => $expense,
                            'duplicate' => false,
                        ];
                    }, 3);
                });
        } catch (QueryException $exception) {
            // MySQL duplicate-key error: another request may have
            // committed this transaction token concurrently.
            $mysqlErrorCode = (int) ($exception->errorInfo[1] ?? 0);

            if ($mysqlErrorCode !== 1062) {
                throw $exception;
            }

            $existingToken = DB::table('expense_transaction_tokens')
                ->where('transaction_token', $token)
                ->where('user_id', $userId)
                ->first();

            // Do not treat an unrelated unique-key failure
            // as a successful duplicate expense submission.
            if (!$existingToken || !$existingToken->expense_id) {
                throw $exception;
            }

            $existingExpense = Expense::query()
                ->whereKey($existingToken->expense_id)
                ->whereIn('branch_id', $branchIds)
                ->first();

            if (!$existingExpense) {
                throw $exception;
            }

            $result = [
                'expense' => $existingExpense,
                'duplicate' => true,
            ];
        }

        $expense = $result['expense'];

        return redirect()
            ->route('admin.expenses.index')
            ->with(
                'success',
                $result['duplicate']
                    ? "Expense {$expense->expense_number} was already recorded."
                    : "Expense {$expense->expense_number} recorded successfully."
            );
    }

    public function void(Request $request, Expense $expense): RedirectResponse
    {
        $branchIds = $this->authorizedBranchIds($request, 'expenses.void');

        abort_unless(in_array((int) $expense->branch_id, $branchIds, true), 403);

        $validated = $request->validate([
            'void_reason' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $voided = DB::transaction(function () use ($request, $expense, $branchIds, $validated) {
            $lockedExpense = Expense::query()
                ->whereKey($expense->id)
                ->whereIn('branch_id', $branchIds)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$lockedExpense->isRecorded()) {
                return false;
            }

            $oldValues = [
                'status' => $lockedExpense->status,
                'voided_by' => $lockedExpense->voided_by,
                'voided_at' => $lockedExpense->voided_at,
                'void_reason' => $lockedExpense->void_reason,
            ];

            $lockedExpense->status = 'voided';
            $lockedExpense->voided_by = (int) $request->user()->id;
            $lockedExpense->voided_at = now();
            $lockedExpense->void_reason = trim($validated['void_reason']);
            $lockedExpense->save();

            app(ExpenseCashbookService::class)->void($lockedExpense, (int) $request->user()->id, trim($validated['void_reason']));

            AuditLog::create([
                'user_id' => (int) $request->user()->id,
                'action' => 'void',
                'module' => 'expenses',
                'description' => 'Voided expense ' . $lockedExpense->expense_number,
                'record_type' => Expense::class,
                'record_id' => $lockedExpense->id,
                'old_values' => $oldValues,
                'new_values' => [
                    'status' => $lockedExpense->status,
                    'voided_by' => $lockedExpense->voided_by,
                    'voided_at' => $lockedExpense->voided_at?->toDateTimeString(),
                    'void_reason' => $lockedExpense->void_reason,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return true;
        }, 3);

        if (!$voided) {
            return redirect()->route('admin.expenses.index')
                ->withErrors(['void_reason' => 'This expense has already been voided.']);
        }

        return redirect()->route('admin.expenses.index')
            ->with('success', "Expense {$expense->expense_number} voided successfully.");
    }

    /** @return array<int> */
    private function authorizedBranchIds(
        Request $request,
        string $permission
    ): array {
        $user = $request->user();

        abort_unless(
            $user
                && $user->hasRole('owner')
                && $user->hasPermission($permission),
            403
        );

        $ids = \App\Models\Branch::query()
            ->where('status', 'active')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        abort_if(empty($ids), 403, 'No active branches available.');

        return $ids;
    }

    private function branches(Request $request)
    {
        return \App\Models\Branch::query()
            ->where('status', 'active')
            ->orderBy('branch_name')
            ->get(['id', 'branch_name']);
    }
}
