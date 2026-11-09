<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\CashbookAccount;
use App\Models\CashbookTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CashbookController extends Controller
{
    private function context(Request $request): array
    {
        $user = $request->user();
        abort_unless($user && ($user->hasRole('owner') || $user->hasRole('manager')), 403);
        abort_unless($user->hasPermission('cashbook.view'), 403);
        $isOwner = $user->hasRole('owner');
        if ($isOwner) {
        $branches = Branch::query()
                ->where('status', 'active')
                ->orderBy('branch_name')
                ->get(['id', 'branch_name', 'branch_code']);
        } else {
            $branches = $user->branches()
                ->where('branches.status', 'active')
                ->orderBy('branches.branch_name')
                ->get([
                    'branches.id',
                    'branches.branch_name',
                    'branches.branch_code',
                ]);
        }
        $allowed = $branches->pluck('id')->map(fn ($id) => (int) $id)->all();
        $filters = $request->validate([
            'branch_id' => ['nullable', 'integer'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'type' => ['nullable', 'in:receipt,payment'],
            'status' => ['nullable', 'in:recorded,voided'],
            'method' => ['nullable', 'in:cash,bank_transfer,pos,other'],
        ]);
        $selectedBranch = isset($filters['branch_id']) ? (int) $filters['branch_id'] : null;
        abort_if($selectedBranch !== null && !in_array($selectedBranch, $allowed, true), 403, 'Branch not authorized or inactive.');
        if (!$isOwner) {
            abort_if(
                empty($allowed),
                403,
                'No active branch assignment.'
            );

            if ($selectedBranch === null && count($allowed) === 1) {
                $selectedBranch = $allowed[0];
            }

            if ($selectedBranch === null) {
                abort(422, 'Please select an assigned branch.');
            }
        }
        $ids = $selectedBranch !== null ? [$selectedBranch] : $allowed;
        if (!array_key_exists('status', $filters)) $filters['status'] = 'recorded';
        $filters['from'] = $filters['from'] ?? now()->startOfMonth()->toDateString();
        $filters['to'] = $filters['to'] ?? now()->toDateString();
        if ($filters['to'] < $filters['from']) {
            throw ValidationException::withMessages(['to' => 'End date must be on or after start date.']);
        }
        $accounts = CashbookAccount::query()->whereIn('branch_id', $ids)->get()->keyBy('branch_id');
        return compact('isOwner', 'branches', 'selectedBranch', 'ids', 'filters', 'accounts');
    }

    private function filteredQuery(array $ctx)
    {
        $f = $ctx['filters'];
        return CashbookTransaction::query()->with('branch:id,branch_name,branch_code')
            ->whereIn('branch_id', $ctx['ids'])
            ->whereBetween('transaction_date', [$f['from'], $f['to']])
            ->when(!empty($f['type']), fn ($q) => $q->where('transaction_type', $f['type']))
            ->when(!empty($f['status']), fn ($q) => $q->where('status', $f['status']))
            ->when(!empty($f['method']), fn ($q) => $q->where('payment_method', $f['method']));
    }

    // Convert decimal(19,2) to integer kobo without floating-point arithmetic.
    private function kobo(string $amount): int
    {
        $negative = str_starts_with($amount, '-');
        $parts = explode('.', ltrim($amount, '-'), 2);
        $whole = (int) $parts[0];
        $fraction = (int) str_pad(substr($parts[1] ?? '', 0, 2), 2, '0');
        $value = $whole * 100 + $fraction;
        return $negative ? -$value : $value;
    }

    private function money(int $kobo): string
    {
        return ($kobo < 0 ? '-' : '') . number_format(intdiv(abs($kobo), 100)) . '.' . str_pad((string) (abs($kobo) % 100), 2, '0', STR_PAD_LEFT);
    }

    private function channel(?string $method): ?string
    {
        if ($method === 'cash') return 'cash';
        if (in_array($method, ['bank_transfer', 'pos'], true)) return 'bank';
        return null; // 'other' cannot safely be assigned to cash or bank.
    }

    private function ledger(array $ctx): array
    {
        $from = $ctx['filters']['from'];
        $to = $ctx['filters']['to'];
        $summaries = [];
        $running = [];
        $warnings = [];

        foreach ($ctx['ids'] as $id) {
            $account = $ctx['accounts']->get($id);
            $ready = $account && $account->opening_date !== null
                && $account->opening_balance !== null
                && $account->bank_opening_balance !== null
                && $account->opening_date->toDateString() <= $from;
            $start = $account?->opening_date?->toDateString();
            $cash = $ready ? $this->kobo((string) $account->opening_balance) : null;
            $bank = $ready ? $this->kobo((string) $account->bank_opening_balance) : null;
            $unknown = false;
            if (!$ready) {
                $warnings[] = 'Branch #' . $id . ': verified opening balances are unavailable for the selected start date.';
            }

            // Include every recorded transaction from initialization to period end,
            // regardless of the display filters. Never count voided entries.
            $ledger = CashbookTransaction::query()->where('branch_id', $id)
                ->where('status', CashbookTransaction::STATUS_RECORDED)
                ->whereDate('transaction_date', '<=', $to)
                ->when($start !== null, fn ($q) => $q->whereDate('transaction_date', '>=', $start))
                ->orderBy('transaction_date')->orderBy('id')->get();
            $openCash = $cash;
            $openBank = $bank;
            $cashReceipts = $cashPayments = $bankReceipts = $bankPayments = 0;
            foreach ($ledger as $entry) {
                $date = $entry->transaction_date->toDateString();
                $channel = $this->channel($entry->payment_method);
                $amount = $this->kobo((string) $entry->amount);
                $signed = $entry->transaction_type === 'receipt' ? $amount : -$amount;
                if (!$channel) $unknown = true;
                if ($date < $from) {
                    if ($channel === 'cash' && $cash !== null) $cash += $signed;
                    if ($channel === 'bank' && $bank !== null) $bank += $signed;
                    continue;
                }
                if ($channel === 'cash') {
                    if ($cash !== null) $cash += $signed;
                    if ($entry->transaction_type === 'receipt') $cashReceipts += $amount;
                    else $cashPayments += $amount;
                } elseif ($channel === 'bank') {
                    if ($bank !== null) $bank += $signed;
                    if ($entry->transaction_type === 'receipt') $bankReceipts += $amount;
                    else $bankPayments += $amount;
                }
                $running[$entry->id] = [
                    'cash' => $cash !== null ? $this->money($cash) : null,
                    'bank' => $bank !== null ? $this->money($bank) : null,
                ];
            }
            // Recompute period opening independently of the period movement.
            $periodOpenCash = $cash !== null ? $cash - $cashReceipts + $cashPayments : null;
            $periodOpenBank = $bank !== null ? $bank - $bankReceipts + $bankPayments : null;
            if ($unknown) {
                $warnings[] = 'Branch #' . $id . ': transactions with payment method Other are unallocated; balances cannot be certified.';
            }
            $summaries[$id] = [
                'branch' => $ctx['branches']->firstWhere('id', $id)?->branch_name ?? ('Branch #' . $id),
                'ready' => $ready && !$unknown,
                'cash_open' => $ready && !$unknown ? $this->money($periodOpenCash) : null,
                'bank_open' => $ready && !$unknown ? $this->money($periodOpenBank) : null,
                'cash_receipts' => $this->money($cashReceipts),
                'cash_payments' => $this->money($cashPayments),
                'bank_receipts' => $this->money($bankReceipts),
                'bank_payments' => $this->money($bankPayments),
                'cash_close' => $ready && !$unknown ? $this->money($cash) : null,
                'bank_close' => $ready && !$unknown ? $this->money($bank) : null,
            ];
            if ($unknown || !$ready) {
                // Suppress running balances for the entire branch, not only summary.
                foreach ($ledger as $entry) unset($running[$entry->id]);
            }
        }
        return compact('summaries', 'running', 'warnings');
    }

    private function prepare(Request $request): array
    {
        $ctx = $this->context($request);
        $ledger = $this->ledger($ctx);
        $totals = (clone $this->filteredQuery($ctx))->where('status', 'recorded')
            ->selectRaw("COALESCE(SUM(CASE WHEN transaction_type = 'receipt' THEN amount ELSE 0 END),0) AS receipts")
            ->selectRaw("COALESCE(SUM(CASE WHEN transaction_type = 'payment' THEN amount ELSE 0 END),0) AS payments")
            ->first();
        return array_merge($ctx, $ledger, compact('totals'));
    }

    public function index(Request $request): View
    {
        $data = $this->context($request);
        $generated = $request->boolean('generate');
        $transactions = null;
        $totals = null;
        $summaries = $running = $warnings = [];
        if ($generated) {
            $data = $this->prepare($request);
            $transactions = $this->filteredQuery($data)->orderBy('transaction_date')->orderBy('id')
                ->paginate(25)->withQueryString();
            $totals = $data['totals'];
            $summaries = $data['summaries'];
            $running = $data['running'];
            $warnings = $data['warnings'];
        }
        return view('finance.cashbook.index', array_merge($data, compact(
            'generated', 'transactions', 'totals', 'summaries', 'running', 'warnings'
        )));
    }

    public function csv(Request $request): StreamedResponse
    {
        $data = $this->prepare($request);
        $rows = $this->filteredQuery($data)->orderBy('transaction_date')->orderBy('id');
        return response()->streamDownload(function () use ($data, $rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['LPG-SIMS CASHBOOK', $data['filters']['from'], $data['filters']['to']]);
            fputcsv($out, ['Filters', 'Branch: ' . ($data['selectedBranch'] ?? 'All authorized'), 'Type: ' . ($data['filters']['type'] ?? 'All'), 'Status: ' . ($data['filters']['status'] ?? 'All'), 'Method: ' . ($data['filters']['method'] ?? 'All')]);
            foreach ($data['warnings'] as $warning) fputcsv($out, ['WARNING', $warning]);
            fputcsv($out, []);
            fputcsv($out, ['Branch', 'Cash Opening (NGN)', 'Cash Receipts (NGN)', 'Cash Payments (NGN)', 'Cash Closing (NGN)', 'Bank Opening (NGN)', 'Bank Receipts (NGN)', 'Bank Payments (NGN)', 'Bank Closing (NGN)']);
            foreach ($data['summaries'] as $s) fputcsv($out, [$s['branch'], $s['cash_open'] ?? 'UNVERIFIED', $s['cash_receipts'], $s['cash_payments'], $s['cash_close'] ?? 'UNVERIFIED', $s['bank_open'] ?? 'UNVERIFIED', $s['bank_receipts'], $s['bank_payments'], $s['bank_close'] ?? 'UNVERIFIED']);
            fputcsv($out, []);
            fputcsv($out, ['Date', 'Reference', 'Branch', 'Type', 'Category', 'Description', 'Method', 'Status', 'Receipt (NGN)', 'Payment (NGN)', 'Running Cash (NGN)', 'Running Bank (NGN)']);
            foreach ($rows->cursor() as $t) {
                $active = $t->status === 'recorded';
                $receipt = $active && $t->transaction_type === 'receipt' ? (string) $t->amount : '0.00';
                $payment = $active && $t->transaction_type === 'payment' ? (string) $t->amount : '0.00';
                $r = $data['running'][$t->id] ?? [];
                fputcsv($out, [$t->transaction_date?->format('Y-m-d'), $t->transaction_number, $t->branch?->branch_name, $t->transaction_type, $t->category, $t->description, $t->payment_method, $t->status, $receipt, $payment, $r['cash'] ?? '—', $r['bank'] ?? '—']);
            }
            fputcsv($out, []);
            fputcsv($out, ['Filtered receipts (NGN)', (string) $data['totals']->receipts, 'Filtered payments (NGN)', (string) $data['totals']->payments]);
            fclose($out);
        }, 'cashbook-' . now()->format('Ymd-His') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function pdf(Request $request)
    {
        $data = $this->prepare($request);
        $data['transactions'] = $this->filteredQuery($data)->orderBy('transaction_date')->orderBy('id')->get();
        return Pdf::loadView('finance.cashbook.pdf', $data)
            ->setPaper('a4', 'landscape')
            ->download('cashbook-' . now()->format('Ymd-His') . '.pdf');
    }
}
