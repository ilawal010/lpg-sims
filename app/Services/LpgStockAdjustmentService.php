<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\LpgStockAdjustment;
use App\Models\LpgStorageUnit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LpgStockAdjustmentService
{
    /**
     * Create an LPG stock adjustment draft.
     *
     * Draft creation does not affect inventory.
     */
    public function createDraft(
        array $data,
        int $userId
    ): LpgStockAdjustment {

        return DB::transaction(function () use ($data, $userId) {

            $validator = validator($data, [
                'adjustment_date' => [
                    'required',
                    'date_format:Y-m-d',
                ],
                'branch_id' => [
                    'required',
                    'integer',
                    'exists:branches,id',
                ],
                'lpg_storage_unit_id' => [
                    'required',
                    'integer',
                    'exists:lpg_storage_units,id',
                ],
                'adjustment_type' => [
                    'required',
                    'in:adjustment_in,adjustment_out',
                ],
                'quantity_kg' => [
                    'required',
                    'numeric',
                    'gt:0',
                    'decimal:0,3',
                ],
                'reason' => [
                    'required',
                    'string',
                    'max:500',
                ],
                'notes' => [
                    'nullable',
                    'string',
                ],
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $validated = $validator->validated();

            /*
             * Validate the responsible user.
             */
            $user = User::findOrFail($userId);

            if (!$user->hasPermission('inventory.adjust')) {
                throw ValidationException::withMessages([
                    'permission' =>
                        'You are not authorized to create stock adjustments.',
                ]);
            }

            /*
             * Validate the selected branch.
             */
            $branch = Branch::findOrFail(
                $validated['branch_id']
            );

            if ($branch->status !== 'active') {
                throw ValidationException::withMessages([
                    'branch_id' =>
                        'The selected branch is not active.',
                ]);
            }

            /*
             * Validate the storage unit and branch.
             */
            $storageUnit = LpgStorageUnit::findOrFail(
                $validated['lpg_storage_unit_id']
            );

            if ($storageUnit->status !== 'active') {
                throw ValidationException::withMessages([
                    'lpg_storage_unit_id' =>
                        'The selected storage unit is not active.',
                ]);
            }

            if (
                (int) $storageUnit->branch_id
                !== (int) $branch->id
            ) {
                throw ValidationException::withMessages([
                    'lpg_storage_unit_id' =>
                        'The selected storage unit does not belong to the branch.',
                ]);
            }

            /*
             * Normalize the adjustment quantity.
             */
            $quantity = bcadd(
                (string) $validated['quantity_kg'],
                '0',
                3
            );

            if (bccomp($quantity, '0', 3) <= 0) {
                throw ValidationException::withMessages([
                    'quantity_kg' =>
                        'Adjustment quantity must be greater than zero.',
                ]);
            }

            /*
             * Generate a unique adjustment reference.
             */
            $reference = 'LPG-ADJ-'
                . now()->format('YmdHis')
                . '-'
                . strtoupper(Str::random(8));

            /*
             * Create the draft without stock posting.
             */
            $adjustment = LpgStockAdjustment::create([
                'adjustment_reference' => $reference,
                'adjustment_date' =>
                    $validated['adjustment_date'],
                'branch_id' => $branch->id,
                'lpg_storage_unit_id' => $storageUnit->id,
                'adjustment_type' =>
                    $validated['adjustment_type'],
                'quantity_kg' => $quantity,
                'reason' => trim($validated['reason']),
                'notes' => $validated['notes'] ?? null,

                'status' => 'draft',
                'created_by' => $user->id,

                'submitted_by' => null,
                'submitted_at' => null,
                'approved_by' => null,
                'approved_at' => null,
                'rejected_by' => null,
                'rejected_at' => null,
                'rejection_reason' => null,

                'stock_movement_id' => null,
                'posted_at' => null,
            ]);

            /*
             * Record draft creation in the audit log.
             */
            AuditLogger::log(
                action: 'created',
                module: 'lpg_stock_adjustments',
                description:
                    'Created LPG stock adjustment draft '
                    . $adjustment->adjustment_reference,
                record: $adjustment,
                oldValues: [],
                newValues: $adjustment->getAttributes()
            );

            return $adjustment->refresh();

        }, 3);
    }

    /**
     * Update an existing LPG stock adjustment draft.
     *
     * Only drafts may be edited.
     * No inventory movement occurs.
     */
    public function updateDraft(
        int $adjustmentId,
        array $data,
        int $userId
    ): LpgStockAdjustment {

        return DB::transaction(function () use (
            $adjustmentId,
            $data,
            $userId
        ) {

            /*
            * Verify the responsible user's permission.
            */
            $user = User::findOrFail($userId);

            if (!$user->hasPermission('inventory.adjust')) {
                throw ValidationException::withMessages([
                    'permission' =>
                        'You are not authorized to update stock adjustments.',
                ]);
            }

            /*
            * Lock the adjustment against concurrent changes.
            */
            $adjustment = LpgStockAdjustment::query()
                ->lockForUpdate()
                ->findOrFail($adjustmentId);

            if (!$adjustment->canBeEdited()) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Only draft stock adjustments can be edited.',
                ]);
            }

            /*
            * A draft must never have posting information.
            */
            if (
                $adjustment->stock_movement_id !== null ||
                $adjustment->posted_at !== null
            ) {
                throw ValidationException::withMessages([
                    'status' =>
                        'This adjustment already contains posting information.',
                ]);
            }

            $validator = validator($data, [
                'adjustment_date' => [
                    'required',
                    'date_format:Y-m-d',
                ],
                'branch_id' => [
                    'required',
                    'integer',
                    'exists:branches,id',
                ],
                'lpg_storage_unit_id' => [
                    'required',
                    'integer',
                    'exists:lpg_storage_units,id',
                ],
                'adjustment_type' => [
                    'required',
                    'in:adjustment_in,adjustment_out',
                ],
                'quantity_kg' => [
                    'required',
                    'numeric',
                    'gt:0',
                    'decimal:0,3',
                ],
                'reason' => [
                    'required',
                    'string',
                    'max:500',
                ],
                'notes' => [
                    'nullable',
                    'string',
                ],
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $validated = $validator->validated();

            /*
            * Validate the branch.
            */
            $branch = Branch::findOrFail(
                $validated['branch_id']
            );

            if ($branch->status !== 'active') {
                throw ValidationException::withMessages([
                    'branch_id' =>
                        'The selected branch is not active.',
                ]);
            }

            /*
            * Validate the storage unit.
            */
            $storageUnit = LpgStorageUnit::findOrFail(
                $validated['lpg_storage_unit_id']
            );

            if ($storageUnit->status !== 'active') {
                throw ValidationException::withMessages([
                    'lpg_storage_unit_id' =>
                        'The selected storage unit is not active.',
                ]);
            }

            if (
                (int) $storageUnit->branch_id
                !== (int) $branch->id
            ) {
                throw ValidationException::withMessages([
                    'lpg_storage_unit_id' =>
                        'The selected storage unit does not belong to the branch.',
                ]);
            }

            /*
            * Normalize quantity.
            */
            $quantity = bcadd(
                (string) $validated['quantity_kg'],
                '0',
                3
            );

            if (bccomp($quantity, '0', 3) <= 0) {
                throw ValidationException::withMessages([
                    'quantity_kg' =>
                        'Adjustment quantity must be greater than zero.',
                ]);
            }

            /*
            * Preserve the original values for auditing.
            */
            $oldValues = $adjustment->getAttributes();

            /*
            * Update draft information only.
            */
            $adjustment->update([
                'adjustment_date' =>
                    $validated['adjustment_date'],

                'branch_id' => $branch->id,

                'lpg_storage_unit_id' =>
                    $storageUnit->id,

                'adjustment_type' =>
                    $validated['adjustment_type'],

                'quantity_kg' => $quantity,

                'reason' => trim($validated['reason']),

                'notes' => $validated['notes'] ?? null,
            ]);

            /*
            * Record the modification in the audit trail.
            */
            AuditLogger::log(
                action: 'updated',
                module: 'lpg_stock_adjustments',
                description:
                    'Updated LPG stock adjustment draft '
                    . $adjustment->adjustment_reference,
                record: $adjustment,
                oldValues: $oldValues,
                newValues: $adjustment->getAttributes()
            );

            return $adjustment->refresh();

        }, 3);
    }

}
