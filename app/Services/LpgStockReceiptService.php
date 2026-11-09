<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\LpgStockReceipt;
use App\Models\LpgStorageUnit;
use App\Models\LpgSupplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LpgStockReceiptService
{
    /**
     * Create a draft supplier LPG receipt.
     *
     * A draft does not affect the LPG stock ledger.
     *
     * Expected input:
     * receipt_date, lpg_supplier_id, branch_id,
     * lpg_storage_unit_id, quantity_kg, unit_cost,
     * and optional delivery documentation.
     */
    public function createDraft(
        array $data,
        int $userId,
        ?array $authorizedBranchIds = null
    ): LpgStockReceipt {
        $quantity = $this->normalizeDecimal(
            (string) ($data['quantity_kg'] ?? ''),
            3,
            'quantity_kg'
        );

        $unitCost = $this->normalizeDecimal(
            (string) ($data['unit_cost'] ?? ''),
            4,
            'unit_cost'
        );

        if (bccomp($quantity, '0', 3) <= 0) {
            throw ValidationException::withMessages([
                'quantity_kg' =>
                    'Received LPG quantity must be greater than zero.',
            ]);
        }

        if (bccomp($unitCost, '0', 4) < 0) {
            throw ValidationException::withMessages([
                'unit_cost' =>
                    'LPG purchase unit cost cannot be negative.',
            ]);
        }

        if (empty($data['receipt_date'])) {
            throw ValidationException::withMessages([
                'receipt_date' => 'Receipt date is required.',
            ]);
        }

        $totalCost = bcmul(
            $quantity,
            $unitCost,
            4
        );

        return DB::transaction(function () use (
            $data,
            $userId,
            $quantity,
            $unitCost,
            $totalCost,
            $authorizedBranchIds
        ) {
            // Enforce the caller's permitted receiving branches.
            $this->assertAuthorizedBranch(
                (int) $data['branch_id'],
                $authorizedBranchIds
            );

            /*
             * Validate registered supplier.
             */
            $supplier = LpgSupplier::query()
                ->findOrFail($data['lpg_supplier_id']);

            if ($supplier->status !== 'active') {
                throw ValidationException::withMessages([
                    'lpg_supplier_id' =>
                        'Only active suppliers can be selected.',
                ]);
            }

            /*
             * Validate branch.
             */
            $branch = Branch::query()
                ->findOrFail($data['branch_id']);

            if ($branch->status !== 'active') {
                throw ValidationException::withMessages([
                    'branch_id' =>
                        'Only active branches can receive LPG.',
                ]);
            }

            /*
             * Validate storage unit and branch ownership.
             */
            $storageUnit = LpgStorageUnit::query()
                ->findOrFail($data['lpg_storage_unit_id']);

            if ($storageUnit->status !== 'active') {
                throw ValidationException::withMessages([
                    'lpg_storage_unit_id' =>
                        'Only active storage units can receive LPG.',
                ]);
            }

            if ((int) $storageUnit->branch_id !== (int) $branch->id) {
                throw ValidationException::withMessages([
                    'lpg_storage_unit_id' =>
                        'The selected storage unit does not belong to the selected branch.',
                ]);
            }

            /*
             * Generate a unique receipt reference.
             */
            $receiptNumber = 'LPG-RC-'
                . now()->format('YmdHis')
                . '-'
                . strtoupper(Str::random(8));

            $receipt = LpgStockReceipt::create([
                'receipt_number' => $receiptNumber,
                'receipt_date' => $data['receipt_date'],

                'lpg_supplier_id' => $supplier->id,
                'branch_id' => $branch->id,
                'lpg_storage_unit_id' => $storageUnit->id,

                'supplier_invoice_number' =>
                    $data['supplier_invoice_number'] ?? null,

                'delivery_note_number' =>
                    $data['delivery_note_number'] ?? null,

                'vehicle_registration_number' =>
                    $data['vehicle_registration_number'] ?? null,

                'driver_name' =>
                    $data['driver_name'] ?? null,

                'quantity_kg' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,

                'status' => LpgStockReceipt::STATUS_DRAFT,
                'posted_at' => null,
                'stock_movement_id' => null,

                'notes' => $data['notes'] ?? null,

                'created_by' => $userId,
                'updated_by' => $userId,
                'posted_by' => null,
            ]);

            /*
             * Audit draft receipt creation.
             */
            AuditLogger::log(
                action: 'created',
                module: 'lpg_stock_receipts',
                description:
                    'Created draft LPG stock receipt '
                    . $receipt->receipt_number
                    . ' for supplier '
                    . $supplier->supplier_name,
                record: $receipt,
                oldValues: [],
                newValues: $receipt->getAttributes()
            );

            return $receipt;
        }, 3);
    }

    /**
     * Update an existing draft LPG stock receipt.
     *
     * Posted and cancelled receipts cannot be edited.
     * Updating a draft does not affect LPG stock balances.
     */
    public function updateDraft(
        int $receiptId,
        array $data,
        int $userId,
        ?array $authorizedBranchIds = null
    ): LpgStockReceipt {
        $quantity = $this->normalizeDecimal(
            (string) ($data['quantity_kg'] ?? ''),
            3,
            'quantity_kg'
        );

        $unitCost = $this->normalizeDecimal(
            (string) ($data['unit_cost'] ?? ''),
            4,
            'unit_cost'
        );

        if (bccomp($quantity, '0', 3) <= 0) {
            throw ValidationException::withMessages([
                'quantity_kg' =>
                    'Received LPG quantity must be greater than zero.',
            ]);
        }

        if (bccomp($unitCost, '0', 4) < 0) {
            throw ValidationException::withMessages([
                'unit_cost' =>
                    'LPG purchase unit cost cannot be negative.',
            ]);
        }

        if (empty($data['receipt_date'])) {
            throw ValidationException::withMessages([
                'receipt_date' => 'Receipt date is required.',
            ]);
        }

        $totalCost = bcmul($quantity, $unitCost, 4);

        return DB::transaction(function () use (
            $receiptId,
            $data,
            $userId,
            $quantity,
            $unitCost,
            $totalCost,
            $authorizedBranchIds
        ) {
            /*
            * Lock the receipt to prevent simultaneous edits
            * or conflicts with future stock posting.
            */
            $receipt = LpgStockReceipt::query()
                ->lockForUpdate()
                ->findOrFail($receiptId);

            // Authorize both the original and requested branches.
            $this->assertAuthorizedBranch(
                (int) $receipt->branch_id,
                $authorizedBranchIds
            );
            $this->assertAuthorizedBranch(
                (int) $data['branch_id'],
                $authorizedBranchIds
            );

            if (!$receipt->canBeEdited() ||
                $receipt->stock_movement_id !== null) {
                throw ValidationException::withMessages([
                    'receipt' =>
                        'Only draft receipts that have not been posted can be edited.',
                ]);
            }

            /*
            * Validate the supplier.
            */
            $supplier = LpgSupplier::query()
                ->findOrFail($data['lpg_supplier_id']);

            if ($supplier->status !== 'active') {
                throw ValidationException::withMessages([
                    'lpg_supplier_id' =>
                        'Only active suppliers can be selected.',
                ]);
            }

            /*
            * Validate the receiving branch.
            */
            $branch = Branch::query()
                ->findOrFail($data['branch_id']);

            if ($branch->status !== 'active') {
                throw ValidationException::withMessages([
                    'branch_id' =>
                        'Only active branches can receive LPG.',
                ]);
            }

            /*
            * Validate the storage unit.
            */
            $storageUnit = LpgStorageUnit::query()
                ->findOrFail($data['lpg_storage_unit_id']);

            if ($storageUnit->status !== 'active') {
                throw ValidationException::withMessages([
                    'lpg_storage_unit_id' =>
                        'Only active storage units can receive LPG.',
                ]);
            }

            if ((int) $storageUnit->branch_id !== (int) $branch->id) {
                throw ValidationException::withMessages([
                    'lpg_storage_unit_id' =>
                        'The selected storage unit does not belong to the selected branch.',
                ]);
            }

            /*
            * Preserve original values for audit logging.
            */
            $oldValues = $receipt->getAttributes();

            /*
            * Update only editable receipt fields.
            *
            * Receipt number, posting status, posting user,
            * and stock movement reference remain unchanged.
            */
            $receipt->fill([
                'receipt_date' => $data['receipt_date'],

                'lpg_supplier_id' => $supplier->id,
                'branch_id' => $branch->id,
                'lpg_storage_unit_id' => $storageUnit->id,

                'supplier_invoice_number' =>
                    $data['supplier_invoice_number'] ?? null,

                'delivery_note_number' =>
                    $data['delivery_note_number'] ?? null,

                'vehicle_registration_number' =>
                    $data['vehicle_registration_number'] ?? null,

                'driver_name' =>
                    $data['driver_name'] ?? null,

                'quantity_kg' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,

                'notes' => $data['notes'] ?? null,
                'updated_by' => $userId,
            ]);

            /*
            * Save only if there are actual changes.
            */
            if ($receipt->isDirty()) {
                $changes = $receipt->getDirty();

                $receipt->save();

                AuditLogger::log(
                    action: 'updated',
                    module: 'lpg_stock_receipts',
                    description:
                        'Updated draft LPG stock receipt '
                        . $receipt->receipt_number,
                    record: $receipt,
                    oldValues: array_intersect_key(
                        $oldValues,
                        $changes
                    ),
                    newValues: $changes
                );
            }

            return $receipt;
        }, 3);
    }

    /**
     * Cancel a draft LPG stock receipt.
     *
     * Only unposted draft receipts may be cancelled.
     * Cancellation does not create or modify stock movements.
     */
    public function cancelDraft(
        int $receiptId,
        int $userId,
        ?array $authorizedBranchIds = null
    ): LpgStockReceipt {
        return DB::transaction(function () use (
            $receiptId,
            $userId,
            $authorizedBranchIds
        ) {
            /*
            * Lock the receipt to prevent simultaneous
            * cancellation, editing, or posting.
            */
            $receipt = LpgStockReceipt::query()
                ->lockForUpdate()
                ->findOrFail($receiptId);

            $this->assertAuthorizedBranch(
                (int) $receipt->branch_id,
                $authorizedBranchIds
            );

            /*
            * Protect posted and previously cancelled receipts.
             */
            if (
                !$receipt->canBeCancelled()
                || $receipt->stock_movement_id !== null
                || $receipt->posted_at !== null
            ) {
                throw ValidationException::withMessages([
                    'receipt' =>
                        'Only draft receipts that have not been posted can be cancelled.',
                ]);
            }

            /*
            * Preserve original values for auditing.
            */
            $oldValues = [
                'status' => $receipt->status,
                'updated_by' => $receipt->updated_by,
            ];

            /*
            * Mark as cancelled.
            * Do not delete the receipt or its history.
            */
            $receipt->status = LpgStockReceipt::STATUS_CANCELLED;
            $receipt->updated_by = $userId;

            $receipt->save();

            /*
            * Record the cancellation in the audit trail.
            */
            AuditLogger::log(
                action: 'cancelled',
                module: 'lpg_stock_receipts',
                description:
                    'Cancelled draft LPG stock receipt '
                    . $receipt->receipt_number,
                record: $receipt,
                oldValues: $oldValues,
                newValues: [
                    'status' => $receipt->status,
                    'updated_by' => $receipt->updated_by,
                ]
            );

            return $receipt;
        }, 3);
    }

    /**
     * Post a draft supplier receipt into the LPG stock ledger.
     *
     * Creates exactly one incoming stock movement.
     * Receipt and movement are saved atomically.
     */
    public function postReceipt(
        int $receiptId,
        int $userId,
        ?array $authorizedBranchIds = null
    ): LpgStockReceipt {
        return DB::transaction(function () use (
            $receiptId,
            $userId,
            $authorizedBranchIds
        ) {
            /*
            * Lock the receipt against concurrent posting,
            * editing or cancellation.
            */
            $receipt = LpgStockReceipt::query()
                ->lockForUpdate()
                ->findOrFail($receiptId);

            $this->assertAuthorizedBranch(
                (int) $receipt->branch_id,
                $authorizedBranchIds
            );

            if (
                !$receipt->canBePosted()
                || $receipt->posted_at !== null
                || $receipt->posted_by !== null
            ) {
                throw ValidationException::withMessages([
                    'receipt' =>
                        'This receipt cannot be posted because it is not an unposted draft.',
                ]);
            }

            /*
            * Lock the receiving tank.
            *
            * All inventory-writing services must lock
            * this same storage-unit record.
            */
            $storageUnit = LpgStorageUnit::query()
                ->with('branch')
                ->lockForUpdate()
                ->findOrFail($receipt->lpg_storage_unit_id);

            if ($storageUnit->status !== 'active') {
                throw ValidationException::withMessages([
                    'lpg_storage_unit_id' =>
                        'The receiving storage unit must be active.',
                ]);
            }

            if (
                !$storageUnit->branch
                || $storageUnit->branch->status !== 'active'
            ) {
                throw ValidationException::withMessages([
                    'branch_id' =>
                        'The receiving branch must be active.',
                ]);
            }

            if (
                (int) $receipt->branch_id
                !== (int) $storageUnit->branch_id
            ) {
                throw ValidationException::withMessages([
                    'lpg_storage_unit_id' =>
                        'The receiving tank does not belong to the receipt branch.',
                ]);
            }

            /*
            * Supplier must still be active at posting.
            */
            $supplier = LpgSupplier::query()
                ->findOrFail($receipt->lpg_supplier_id);

            if ($supplier->status !== 'active') {
                throw ValidationException::withMessages([
                    'lpg_supplier_id' =>
                        'The supplier must be active before posting.',
                ]);
            }

            /*
            * Read current stock directly from the ledger
            * while the receiving storage unit is locked.
            *
            * Keep decimal arithmetic exact.
            */
            $currentBalance = DB::table('lpg_stock_movements')
                ->where(
                    'lpg_storage_unit_id',
                    $storageUnit->id
                )
                ->selectRaw("
                    COALESCE(
                        SUM(
                            CASE
                                WHEN direction = 'in'
                                    THEN quantity_kg
                                WHEN direction = 'out'
                                    THEN -quantity_kg
                                ELSE 0
                            END
                        ),
                        0
                    ) AS balance_kg
                ")
                ->value('balance_kg');

            $currentBalance = bcadd(
                (string) $currentBalance,
                '0',
                3
            );

            $quantity = bcadd(
                (string) $receipt->quantity_kg,
                '0',
                3
            );

            $unitCost = bcadd(
                (string) $receipt->unit_cost,
                '0',
                4
            );

            if (bccomp($quantity, '0', 3) <= 0) {
                throw ValidationException::withMessages([
                    'quantity_kg' =>
                        'Receipt quantity must be greater than zero.',
                ]);
            }

            if (bccomp($unitCost, '0', 4) < 0) {
                throw ValidationException::withMessages([
                    'unit_cost' =>
                        'Receipt unit cost cannot be negative.',
                ]);
            }

            if (bccomp($currentBalance, '0', 3) < 0) {
                throw ValidationException::withMessages([
                    'lpg_storage_unit_id' =>
                        'The storage unit has a negative stock balance. Reconcile inventory before posting.',
                ]);
            }

            $newBalance = bcadd(
                $currentBalance,
                $quantity,
                3
            );

            if (
                bccomp(
                    $newBalance,
                    (string) $storageUnit->capacity_kg,
                    3
                ) > 0
            ) {
                throw ValidationException::withMessages([
                    'quantity_kg' =>
                        'Posting this receipt would exceed the rated storage capacity.',
                ]);
            }

            /*
            * Recalculate the receipt purchase value.
            */
            $totalCost = bcmul(
                $quantity,
                $unitCost,
                4
            );

            /*
            * Create the incoming inventory movement.
            *
            * The receipt number provides a stable,
            * traceable transaction reference.
            */
            $movement = \App\Models\LpgStockMovement::create([
                'branch_id' => $receipt->branch_id,
                'lpg_storage_unit_id' =>
                    $receipt->lpg_storage_unit_id,

                'movement_reference' =>
                    'RC-' . $receipt->id,

                'movement_type' =>
                    \App\Models\LpgStockMovement::TYPE_STOCK_RECEIPT,

                'direction' =>
                    \App\Models\LpgStockMovement::DIRECTION_IN,

                'quantity_kg' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,

                'movement_date' =>
                    $receipt->receipt_date->format('Y-m-d'),

                'document_reference' =>
                    $receipt->receipt_number,

                'description' =>
                    'LPG supplier receipt '
                    . $receipt->receipt_number
                    . ' from '
                    . $supplier->supplier_name,

                'transaction_group' =>
                    $receipt->receipt_number,

                'created_by' => $userId,
            ]);

            /*
            * Update receipt posting information.
            */
            $oldValues = $receipt->getAttributes();

            $receipt->status =
                LpgStockReceipt::STATUS_POSTED;

            $receipt->stock_movement_id =
                $movement->id;

            $receipt->total_cost = $totalCost;
            $receipt->posted_at = now();
            $receipt->posted_by = $userId;
            $receipt->updated_by = $userId;

            $receipt->save();

            /*
            * Audit the stock movement.
            */
            AuditLogger::log(
                action: 'created',
                module: 'lpg_stock_movements',
                description:
                    'Posted supplier LPG receipt '
                    . $receipt->receipt_number
                    . ' into storage unit '
                    . $storageUnit->storage_code
                    . ' (' . $quantity . ' kg)',
                record: $movement,
                oldValues: [],
                newValues: $movement->getAttributes()
            );

            /*
            * Audit the receipt posting.
            */
            AuditLogger::log(
                action: 'posted',
                module: 'lpg_stock_receipts',
                description:
                    'Posted LPG stock receipt '
                    . $receipt->receipt_number,
                record: $receipt,
                oldValues: $oldValues,
                newValues: $receipt->getAttributes()
            );

            return $receipt->load('stockMovement');
        }, 3);
    }

    /**
     * Enforce an optional branch scope supplied by a trusted caller.
     * Null preserves the existing unrestricted Owner/service behavior.
     * An empty array denies access to every branch.
     */
    private function assertAuthorizedBranch(
        int $branchId,
        ?array $authorizedBranchIds
    ): void {
        if ($authorizedBranchIds === null) {
            return;
        }

        $allowedIds = array_map('intval', $authorizedBranchIds);

        abort_unless(in_array($branchId, $allowedIds, true), 403);
    }

    /**
     * Validate and normalize an unsigned decimal.
     *
     * Quantity: maximum 3 decimal places.
     * Unit cost: maximum 4 decimal places.
     */
    private function normalizeDecimal(
        string $value,
        int $scale,
        string $field
    ): string {
        $value = trim($value);

        $pattern = '/^\d+(?:\.\d{1,'
            . $scale
            . '})?$/';

        if (!preg_match($pattern, $value)) {
            throw ValidationException::withMessages([
                $field =>
                    'Enter a valid non-negative number with no more than '
                    . $scale
                    . ' decimal places.',
            ]);
        }

        return bcadd($value, '0', $scale);
    }
}
