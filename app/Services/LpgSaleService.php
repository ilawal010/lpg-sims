<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\LpgPrice;
use App\Models\LpgSale;
use App\Models\LpgStorageUnit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\LpgStockMovement;
use Illuminate\Database\UniqueConstraintViolationException;

class LpgSaleService
{
    
    /**
     * Validate and normalize payment information.
     *
     * Historical drafts may have no payment method.
     * New POS sales must supply an approved method.
     */
    private function validatePaymentDetails(
        array $data,
        bool $required = false
    ): array {
        $method = $data['payment_method'] ?? null;
        $reference = $data['payment_reference'] ?? null;

        $method = is_string($method)
            ? trim($method)
            : $method;

        $reference = is_string($reference)
            ? trim($reference)
            : $reference;

        if ($method === '') {
            $method = null;
        }

        if ($reference === '') {
            $reference = null;
        }

        if ($required && $method === null) {
            throw ValidationException::withMessages([
                'payment_method' =>
                    'Please select a payment method.',
            ]);
        }

        if (
            $method !== null &&
            !array_key_exists($method, LpgSale::paymentMethods())
        ) {
            throw ValidationException::withMessages([
                'payment_method' =>
                    'Select Cash, Bank Transfer or POS Card.',
            ]);
        }

        if (
            $reference !== null &&
            (
                !is_string($reference) ||
                mb_strlen($reference) > 100
            )
        ) {
            throw ValidationException::withMessages([
                'payment_reference' =>
                    'Payment reference must not exceed 100 characters.',
            ]);
        }

        
        return [
            'payment_method' => $method,
            'payment_reference' => $reference,
        ];
    }


    /**
     * Create an LPG sale draft.
     *
     * No stock movement or inventory deduction occurs.
     */
    public function createDraft(array $data, int $userId): LpgSale
    {
        return DB::transaction(function () use ($data, $userId) {

            /*
             * Validate mandatory fields.
             */
            $validator = validator($data, [
                'sale_date' => ['required', 'date'],
                'branch_id' => ['required', 'integer', 'exists:branches,id'],
                'lpg_storage_unit_id' => [
                    'required',
                    'integer',
                    'exists:lpg_storage_units,id',
                ],
                'attendant_id' => [
                    'required',
                    'integer',
                    'exists:users,id',
                ],
                'quantity_kg' => [
                    'required',
                    'numeric',
                    'gt:0',
                    'decimal:0,3',
                ],
                'payment_method' => [
                    'nullable',
                    'string',
                    'max:30',
                ],
                'payment_reference' => [
                    'nullable',
                    'string',
                    'max:100',
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
             * Validate and normalize payment details.
             *
             * Payment method remains optional for draft
             * creation to preserve the existing workflow.
             */
            $paymentDetails = $this->validatePaymentDetails(
                $validated,
                false
            );

            $validated = array_merge(
                $validated,
                $paymentDetails
            );

            /*
             * Confirm branch is active.
             */
            $branch = Branch::findOrFail(
                $validated['branch_id']
            );

            if ($branch->status !== 'active') {
                throw ValidationException::withMessages([
                    'branch_id' => 'The selected branch is not active.',
                ]);
            }

            /*
             * Confirm storage unit is active and
             * belongs to the selected branch.
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

            if ((int) $storageUnit->branch_id !== (int) $branch->id) {
                throw ValidationException::withMessages([
                    'lpg_storage_unit_id' =>
                        'The selected storage unit does not belong to the branch.',
                ]);
            }

            /*
             * Confirm attendant exists.
             *
             * Role-based eligibility will be enforced
             * when sales permissions are implemented.
             */
            $attendant = User::findOrFail(
                $validated['attendant_id']
            );

            /*
             * Retrieve current selling price.
             */
            $price = LpgPrice::query()
                ->whereKey(1)
                ->first();

            if (
                !$price ||
                $price->selling_price_per_kg === null ||
                bccomp(
                    (string) $price->selling_price_per_kg,
                    '0',
                    2
                ) <= 0
            ) {
                throw ValidationException::withMessages([
                    'selling_price_per_kg' =>
                        'A valid LPG selling price has not been configured.',
                ]);
            }

            /*
             * Calculate quantity and total sales amount
             * using decimal arithmetic.
             */
            $quantity = bcadd(
                (string) $validated['quantity_kg'],
                '0',
                3
            );

            $sellingPrice = bcadd(
                (string) $price->selling_price_per_kg,
                '0',
                2
            );

            $totalAmount = bcmul(
                $quantity,
                $sellingPrice,
                2
            );

            /*
             * Create the draft sale.
             */
            $sale = LpgSale::create([
                'sale_number' =>
                    'LPG-SL-' .
                    now()->format('YmdHis') .
                    '-' .
                    strtoupper(Str::random(8)),

                'sale_date' => $validated['sale_date'],
                'branch_id' => $branch->id,
                'lpg_storage_unit_id' => $storageUnit->id,
                'attendant_id' => $attendant->id,

                'quantity_kg' => $quantity,
                'selling_price_per_kg' => $sellingPrice,
                'total_amount' => $totalAmount,

                'cost_price_per_kg' => null,
                'cost_of_goods_sold' => null,
                'gross_profit' => null,

                'payment_method' =>
                    $validated['payment_method'] ?? null,

                'payment_reference' =>
                    $validated['payment_reference'] ?? null,

                'status' => LpgSale::STATUS_DRAFT,
                'stock_movement_id' => null,
                'posted_at' => null,

                'created_by' => $userId,
                'updated_by' => null,
                'posted_by' => null,

                'notes' => $validated['notes'] ?? null,
            ]);

            /*
             * Audit the draft creation.
             */
            
            AuditLogger::log(
                action: 'created',
                module: 'lpg_sales',
                description:
                    'Created LPG sale draft ' . $sale->sale_number,
                record: $sale,
                oldValues: [],
                newValues: $sale->getAttributes()
            );


            return $sale->refresh();
        }, 3);
    }


    /**
     * Update an existing draft LPG sale.
     *
     * The original selling-price snapshot is preserved.
     * No inventory movement occurs.
     */
    public function updateDraft(
        int $saleId,
        array $data,
        int $userId
    ): LpgSale {
        return DB::transaction(function () use (
            $saleId,
            $data,
            $userId
        ) {
            $sale = LpgSale::query()
                ->lockForUpdate()
                ->findOrFail($saleId);

            if (!$sale->canBeEdited()) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Only draft LPG sales can be edited.',
                ]);
            }

            $validator = validator($data, [
                'sale_date' => ['required', 'date'],
                'branch_id' => [
                    'required', 'integer', 'exists:branches,id',
                ],
                'lpg_storage_unit_id' => [
                    'required',
                    'integer',
                    'exists:lpg_storage_units,id',
                ],
                'attendant_id' => [
                    'required', 'integer', 'exists:users,id',
                ],
                'quantity_kg' => [
                    'required', 'numeric', 'gt:0', 'decimal:0,3',
                ],
                'payment_method' => [
                    'nullable', 'string', 'max:30',
                ],
                'payment_reference' => [
                    'nullable', 'string', 'max:100',
                ],
                'notes' => ['nullable', 'string'],
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $validated = $validator->validated();
                        /*
             * Validate and normalize payment details
             * when editing an LPG draft.
             *
             * Preserve support for drafts without
             * payment information.
             */
            $paymentDetails = $this->validatePaymentDetails(
                $validated,
                false
            );

            $validated = array_merge(
                $validated,
                $paymentDetails
            );

            $branch = Branch::findOrFail(
                $validated['branch_id']
            );

            if ($branch->status !== 'active') {
                throw ValidationException::withMessages([
                    'branch_id' =>
                        'The selected branch is not active.',
                ]);
            }

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

            User::findOrFail($validated['attendant_id']);

            $quantity = bcadd(
                (string) $validated['quantity_kg'],
                '0',
                3
            );

            /*
            * Preserve the original selling price.
            * Do not retrieve the latest LPG price.
            */
            $sellingPrice = (string) $sale->selling_price_per_kg;

            $totalAmount = bcmul(
                $quantity,
                $sellingPrice,
                2
            );

            $oldValues = $sale->getAttributes();

            $sale->update([
                'sale_date' => $validated['sale_date'],
                'branch_id' => $branch->id,
                'lpg_storage_unit_id' => $storageUnit->id,
                'attendant_id' => $validated['attendant_id'],
                'quantity_kg' => $quantity,
                'total_amount' => $totalAmount,
                'payment_method' =>
                    $validated['payment_method'] ?? null,
                'payment_reference' =>
                    $validated['payment_reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'updated_by' => $userId,
            ]);

            AuditLogger::log(
                action: 'updated',
                module: 'lpg_sales',
                description:
                    'Updated LPG sale draft '
                    . $sale->sale_number,
                record: $sale,
                oldValues: $oldValues,
                newValues: $sale->getAttributes()
            );

            return $sale->refresh();
        }, 3);
    }


    /**
     * Cancel an existing draft LPG sale.
     *
     * Cancellation does not affect inventory.
     * Posted sales cannot be cancelled here.
     */
    public function cancelDraft(
        int $saleId,
        int $userId
    ): LpgSale {
        return DB::transaction(function () use (
            $saleId,
            $userId
        ) {
            $sale = LpgSale::query()
                ->lockForUpdate()
                ->findOrFail($saleId);

            /*
            * Only draft sales may be cancelled.
            */
            if (!$sale->canBeCancelled()) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Only draft LPG sales can be cancelled.',
                ]);
            }

            /*
            * Defensive check:
            * A draft must not already have a stock movement.
            */
            if (
                $sale->stock_movement_id !== null
                || $sale->posted_at !== null
            ) {
                throw ValidationException::withMessages([
                    'status' =>
                        'This sale already has posting information and cannot be cancelled.',
                ]);
            }

            $oldValues = $sale->getAttributes();

            /*
            * Preserve the transaction and mark it cancelled.
            */
            $sale->update([
                'status' => LpgSale::STATUS_CANCELLED,
                'updated_by' => $userId,
            ]);

            /*
            * Record cancellation in the audit log.
            */
            AuditLogger::log(
                action: 'cancelled',
                module: 'lpg_sales',
                description:
                    'Cancelled LPG sale draft '
                    . $sale->sale_number,
                record: $sale,
                oldValues: $oldValues,
                newValues: $sale->getAttributes()
            );

            return $sale->refresh();
        }, 3);
    }


    /**
     * Post a draft LPG sale into the stock ledger.
     *
     * Deducts inventory, records COGS and gross profit,
     * and prevents duplicate posting.
     */
    public function postSale(
        int $saleId,
        int $userId
    ): LpgSale {
        return DB::transaction(function () use ($saleId, $userId) {

            /*
            * Lock the sale against simultaneous posting,
            * editing or cancellation.
            */
            $sale = LpgSale::query()
                ->lockForUpdate()
                ->findOrFail($saleId);

            if (
                !$sale->canBePosted()
                || $sale->posted_at !== null
                || $sale->posted_by !== null
            ) {
                throw ValidationException::withMessages([
                    'sale' =>
                        'This sale cannot be posted because it is not an unposted draft.',
                ]);
            }

            /*
            * Lock the storage unit.
            *
            * All inventory-writing services must lock
            * this same storage-unit record.
            */
            $storageUnit = LpgStorageUnit::query()
                ->with('branch')
                ->lockForUpdate()
                ->findOrFail($sale->lpg_storage_unit_id);

            if ($storageUnit->status !== 'active') {
                throw ValidationException::withMessages([
                    'lpg_storage_unit_id' =>
                        'The storage unit must be active before posting.',
                ]);
            }

            if (
                !$storageUnit->branch
                || $storageUnit->branch->status !== 'active'
            ) {
                throw ValidationException::withMessages([
                    'branch_id' =>
                        'The branch must be active before posting.',
                ]);
            }

            if (
                (int) $sale->branch_id
                !== (int) $storageUnit->branch_id
            ) {
                throw ValidationException::withMessages([
                    'lpg_storage_unit_id' =>
                        'The selected storage unit does not belong to the sale branch.',
                ]);
            }

            /*
            * Validate sale quantity and selling price.
            */
            $quantity = bcadd(
                (string) $sale->quantity_kg,
                '0',
                3
            );

            $sellingPrice = bcadd(
                (string) $sale->selling_price_per_kg,
                '0',
                2
            );

            if (bccomp($quantity, '0', 3) <= 0) {
                throw ValidationException::withMessages([
                    'quantity_kg' =>
                        'Sale quantity must be greater than zero.',
                ]);
            }

            if (bccomp($sellingPrice, '0', 2) <= 0) {
                throw ValidationException::withMessages([
                    'selling_price_per_kg' =>
                        'Sale selling price must be greater than zero.',
                ]);
            }

            /*
            * Read inventory valuation while the
            * storage-unit record is locked.
            */
            $valuation = app(
                LpgInventoryCostingService::class
            )->getValuation($storageUnit->id);

            $availableQuantity = $valuation['quantity_kg'];
            $inventoryValue = $valuation['inventory_value'];

            if (
                bccomp(
                    $quantity,
                    $availableQuantity,
                    3
                ) > 0
            ) {
                throw ValidationException::withMessages([
                    'quantity_kg' =>
                        'Insufficient LPG stock to post this sale.',
                ]);
            }

            if (
                bccomp($availableQuantity, '0', 3) <= 0
            ) {
                throw ValidationException::withMessages([
                    'quantity_kg' =>
                        'No LPG stock is available for sale.',
                ]);
            }

            /*
            * Calculate the cost per kg using high
            * internal precision.
            */
            $unroundedUnitCost = bcdiv(
                $inventoryValue,
                $availableQuantity,
                12
            );

            $costPricePerKg = bcadd(
                $unroundedUnitCost,
                '0.00005',
                4
            );

            /*
            * Calculate COGS.
            *
            * Round to 4 decimal places using
            * non-negative half-up rounding.
            *
            * If selling all remaining stock, assign
            * the entire inventory value to COGS.
            */
            if (
                bccomp(
                    $quantity,
                    $availableQuantity,
                    3
                ) === 0
            ) {
                $costOfGoodsSold = $inventoryValue;
            } else {
                $unroundedCost = bcmul(
                    $quantity,
                    $unroundedUnitCost,
                    12
                );

                $costOfGoodsSold = bcadd(
                    $unroundedCost,
                    '0.00005',
                    4
                );

                /*
                * Never deduct more than the
                * available inventory value.
                */
                if (
                    bccomp(
                        $costOfGoodsSold,
                        $inventoryValue,
                        4
                    ) > 0
                ) {
                    $costOfGoodsSold = $inventoryValue;
                }
            }

            /*
            * Calculate revenue and gross profit.
            */
            $totalAmount = bcmul(
                $quantity,
                $sellingPrice,
                2
            );

            $grossProfit = bcsub(
                $totalAmount,
                $costOfGoodsSold,
                4
            );

            /*
            * Create exactly one outgoing movement.
            *
            * Movement total_cost is inventory cost,
            * not selling revenue.
            */
            $movement = LpgStockMovement::create([
                'branch_id' => $sale->branch_id,
                'lpg_storage_unit_id' =>
                    $sale->lpg_storage_unit_id,

                'movement_reference' =>
                    'SL-' . $sale->id,

                'movement_type' =>
                    LpgStockMovement::TYPE_SALE,

                'direction' =>
                    LpgStockMovement::DIRECTION_OUT,

                'quantity_kg' => $quantity,
                'unit_cost' => $costPricePerKg,
                'total_cost' => $costOfGoodsSold,

                'movement_date' =>
                    $sale->sale_date->format('Y-m-d'),

                'document_reference' =>
                    $sale->sale_number,

                'description' =>
                    'LPG sale ' . $sale->sale_number,

                'transaction_group' =>
                    $sale->sale_number,

                'created_by' => $userId,
            ]);

            /*
            * Update the sale with its final
            * financial and posting information.
            */
            $oldValues = $sale->getAttributes();

            $sale->status = LpgSale::STATUS_POSTED;
            $sale->stock_movement_id = $movement->id;
            $sale->cost_price_per_kg = $costPricePerKg;
            $sale->cost_of_goods_sold = $costOfGoodsSold;
            $sale->total_amount = $totalAmount;
            $sale->gross_profit = $grossProfit;
            $sale->posted_at = now();
            $sale->posted_by = $userId;
            $sale->updated_by = $userId;

            $sale->save();

            /*
            * Audit the outgoing stock movement.
            */
            AuditLogger::log(
                action: 'created',
                module: 'lpg_stock_movements',
                description:
                    'Posted LPG sale '
                    . $sale->sale_number
                    . ' and deducted '
                    . $quantity
                    . ' kg from storage unit '
                    . $storageUnit->storage_code,
                record: $movement,
                oldValues: [],
                newValues: $movement->getAttributes()
            );

            /*
            * Audit the sale posting.
            */
            AuditLogger::log(
                action: 'posted',
                module: 'lpg_sales',
                description:
                    'Posted LPG sale '
                    . $sale->sale_number,
                record: $sale,
                oldValues: $oldValues,
                newValues: $sale->getAttributes()
            );

            app(CashbookPostingService::class)
                ->recordLpgSale($sale);

            return $sale->load('stockMovement');

        }, 3);
    }
    
    /**
     * Create and finalize an LPG sale exactly once
     * for each transaction token.
     *
     * Draft creation, posting, inventory movements,
     * and audit records remain in one transaction.
     */
    public function processSale(
        array $data,
        int $userId,
        string $transactionToken
    ): LpgSale {
        if (!Str::isUuid($transactionToken)) {
            throw ValidationException::withMessages([
                'transaction_token' =>
                    'Invalid sales transaction token.',
            ]);
        }
                /*
         * Direct POS sales require an agent-confirmed
         * payment channel before processing.
         */
        $paymentDetails = $this->validatePaymentDetails(
            $data,
            true
        );

        $data = array_merge($data, $paymentDetails);
        return DB::transaction(function () use (
            $data,
            $userId,
            $transactionToken
        ) {
            /*
             * Serialize concurrent POS transactions
             * submitted by the same user.
             */
            $user = User::query()
                ->whereKey($userId)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$user->hasPermission('sales.create')) {
                abort(403, 'You are not authorized to process sales.');
            }

            /*
             * Check the central POS transaction registry.
             *
             * The Sales Agent's user row is already
             * locked by this transaction.
             */
            $registeredToken = DB::table('pos_transaction_tokens')
                ->where('transaction_token', $transactionToken)
                ->lockForUpdate()
                ->first();

            if ($registeredToken) {
                if (
                    (int) $registeredToken->user_id !== $userId
                    || $registeredToken->sale_type !== 'lpg'
                ) {
                    throw ValidationException::withMessages([
                        'transaction_token' =>
                            'This transaction token belongs to another transaction.',
                    ]);
                }

                /*
                 * Existing LPG sale verification
                 * continues below.
                 */
            } else {
                /*
                 * Claim this token before creating
                 * or posting the LPG sale.
                 */
                
                try {
                    DB::table('pos_transaction_tokens')->insert([
                        'transaction_token' => $transactionToken,
                        'user_id' => $userId,
                        'sale_type' => 'lpg',
                        'sale_id' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } catch (UniqueConstraintViolationException $e) {
                    throw ValidationException::withMessages([
                        'transaction_token' =>
                            'This transaction is already being processed or has been completed. Please check the sales register before trying again.',
                    ]);
                }

            }

            /*
             * Prevent a token already used for an
             * Accessories sale from being reused.
             */
            $accessoryTokenUsed = \App\Models\AccessorySale::query()
                ->where('transaction_token', $transactionToken)
                ->exists();

            if ($accessoryTokenUsed) {
                throw ValidationException::withMessages([
                    'transaction_token' =>
                        'This transaction token has already been used for an Accessories sale.',
                ]);
            }

            /*
             * Return the original LPG sale when
             * the same transaction is submitted again.
             */
            $existingSale = LpgSale::query()
                ->where('transaction_token', $transactionToken)
                ->first();

            /*
             * A previously registered transaction token
             * must have a corresponding LPG sale.
             *
             * Reject incomplete or inconsistent records
             * before any new sale or stock movement.
             */
            if ($registeredToken && !$existingSale) {
                throw ValidationException::withMessages([
                    'transaction_token' =>
                        'This transaction token is already registered, but its original LPG sale could not be found.',
                ]);
            }

            if ($existingSale) {
                abort_unless(
                    (int) $existingSale->attendant_id === $userId,
                    403
                );

                /*
                 * Verify that the central transaction registry
                 * identifies this exact completed LPG sale.
                 */
                if (
                    !$registeredToken
                    || $registeredToken->sale_id === null
                    || (int) $registeredToken->sale_id !== (int) $existingSale->id
                    || $existingSale->status !== LpgSale::STATUS_POSTED
                ) {
                    throw ValidationException::withMessages([
                        'transaction_token' =>
                            'The original LPG transaction record could not be verified.',
                    ]);
                }

                if (
                    (int) $existingSale->branch_id !==
                        (int) ($data['branch_id'] ?? 0)
                    ||
                    (int) $existingSale->lpg_storage_unit_id !==
                        (int) ($data['lpg_storage_unit_id'] ?? 0)
                    ||
                    bccomp(
                        (string) $existingSale->quantity_kg,
                        (string) ($data['quantity_kg'] ?? '0'),
                        3
                    ) !== 0
                    ||
                    $existingSale->payment_method !==
                        $data['payment_method']
                    ||
                    $existingSale->payment_reference !==
                        $data['payment_reference']
                ) {
                    throw ValidationException::withMessages([
                        'transaction_token' =>
                            'This transaction token has already been used for a different LPG sale.',
                    ]);
                }

                if ($existingSale->status !== LpgSale::STATUS_POSTED) {
                    throw ValidationException::withMessages([
                        'transaction_token' =>
                            'This transaction is not in a completed state.',
                    ]);
                }

                return $existingSale;
            }

            
            $sale = $this->createDraft($data, $userId);
            $sale->transaction_token = $transactionToken;
            $sale->save();

            /*
             * Post the LPG sale using the existing
             * inventory, costing and audit workflow.
             */
            $postedSale = $this->postSale($sale->id, $userId);

            /*
             * Link the posted LPG sale to its centrally
             * registered transaction token.
             */
            $updated = DB::table('pos_transaction_tokens')
                ->where('transaction_token', $transactionToken)
                ->where('user_id', $userId)
                ->where('sale_type', 'lpg')
                ->whereNull('sale_id')
                ->update([
                    'sale_id' => $postedSale->id,
                    'updated_at' => now(),
                ]);

            if ($updated !== 1) {
                throw ValidationException::withMessages([
                    'transaction_token' =>
                        'Unable to finalize the LPG transaction token.',
                ]);
            }

            return $postedSale;


        }, 3);
    }



}
