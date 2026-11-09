<?php

namespace App\Services;

use App\Models\Accessory;
use App\Models\AccessorySale;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AccessorySaleService
{
    /**
     * Process an accessory sale once per transaction token.
     *
     * Payment details are stored with the sale and verified
     * whenever an existing transaction token is reused.
     */
    public function processSale(
        int $accessoryId,
        string $quantity,
        int $userId,
        string $transactionToken,
        string $paymentMethod,
        ?string $paymentReference = null
    ): AccessorySale {
        if (!Str::isUuid($transactionToken)) {
            throw ValidationException::withMessages([
                'transaction_token' =>
                    'Invalid sales transaction token.',
            ]);
        }

        if (!array_key_exists(
            $paymentMethod,
            AccessorySale::paymentMethods()
        )) {
            throw ValidationException::withMessages([
                'payment_method' =>
                    'Select a valid payment method.',
            ]);
        }

        $paymentReference = $paymentReference !== null
            ? trim($paymentReference)
            : null;

        if ($paymentReference === '') {
            $paymentReference = null;
        }

        if (
            $paymentReference !== null &&
            mb_strlen($paymentReference) > 100
        ) {
            throw ValidationException::withMessages([
                'payment_reference' =>
                    'Payment reference cannot exceed 100 characters.',
            ]);
        }

   
        return DB::transaction(function () use (
            $accessoryId,
            $quantity,
            $userId,
            $transactionToken,
            $paymentMethod,
            $paymentReference
        ) {
            /*
             * Lock the sales agent's user row.
             *
             * This serializes concurrent sales requests
             * from the same account.
             */
            $user = User::query()
                ->whereKey($userId)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$user->hasPermission('sales.create')) {
                abort(
                    403,
                    'You are not authorized to process sales.'
                );
            }

            /*
             * Inspect the central POS transaction-token
             * registry while the user row is locked.
             */
            $registeredToken = DB::table('pos_transaction_tokens')
                ->where('transaction_token', $transactionToken)
                ->lockForUpdate()
                ->first();

            if ($registeredToken) {
                if (
                    (int) $registeredToken->user_id !== $userId ||
                    $registeredToken->sale_type !== 'accessory'
                ) {
                    throw ValidationException::withMessages([
                        'transaction_token' =>
                            'This transaction token belongs to another transaction.',
                    ]);
                }
            } else {
                /*
                 * Claim the token before changing stock.
                 */
                try {
                    DB::table('pos_transaction_tokens')->insert([
                        'transaction_token' => $transactionToken,
                        'user_id' => $userId,
                        'sale_type' => 'accessory',
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
             * Prevent cross-use of LPG transaction tokens.
             */
            $lpgTokenUsed = \App\Models\LpgSale::query()
                ->where('transaction_token', $transactionToken)
                ->exists();

            if ($lpgTokenUsed) {
                throw ValidationException::withMessages([
                    'transaction_token' =>
                        'This transaction token has already been used for an LPG sale.',
                ]);
            }

            /*
             * Check for an already completed transaction.
             */
            $existingSale = AccessorySale::query()
                ->where('transaction_token', $transactionToken)
                ->first();

            if ($registeredToken && !$existingSale) {
                throw ValidationException::withMessages([
                    'transaction_token' =>
                        'This transaction token is already registered, but its original sale could not be found.',
                ]);
            }

            if ($existingSale) {
                abort_unless(
                    (int) $existingSale->attendant_id === $userId,
                    403
                );

                if (
                    !$registeredToken ||
                    $registeredToken->sale_id === null ||
                    (int) $registeredToken->sale_id !==
                        (int) $existingSale->id ||
                    $existingSale->status !==
                        AccessorySale::STATUS_POSTED
                ) {
                    throw ValidationException::withMessages([
                        'transaction_token' =>
                            'The original transaction record could not be verified.',
                    ]);
                }

                /*
                 * A retry must match the original product,
                 * quantity and payment information.
                 */
                if (
                    (int) $existingSale->accessory_id !==
                        $accessoryId ||
                    bccomp(
                        (string) $existingSale->quantity,
                        $quantity,
                        3
                    ) !== 0 ||
                    $existingSale->payment_method !==
                        $paymentMethod ||
                    $existingSale->payment_reference !==
                        $paymentReference
                ) {
                    throw ValidationException::withMessages([
                        'transaction_token' =>
                            'This transaction token has already been used for a different sale or payment.',
                    ]);
                }

                return $existingSale;
            }

            /*
             * Resolve the agent's single active branch.
             */
            $branches = $user->branches()
                ->where('branches.status', 'active')
                ->get();

            if ($branches->count() !== 1) {
                throw ValidationException::withMessages([
                    'branch' =>
                        'Your account must have exactly one active assigned branch.',
                ]);
            }

            $branch = $branches->first();

            $activeBranch = Branch::query()
                ->whereKey($branch->id)
                ->where('status', 'active')
                ->firstOrFail();

            /*
             * Lock the accessory before stock validation.
             */
            $accessory = Accessory::query()
                ->whereKey($accessoryId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($accessory->status !== 'active') {
                throw ValidationException::withMessages([
                    'accessory_id' =>
                        'This accessory is not active.',
                ]);
            }

            if (
                (int) $accessory->branch_id !==
                (int) $activeBranch->id
            ) {
                throw ValidationException::withMessages([
                    'accessory_id' =>
                        'This accessory does not belong to your assigned branch.',
                ]);
            }

            /*
             * Validate quantities using thousandths.
             */
            if (!preg_match(
                '/^\d{1,12}(?:\.\d{1,3})?$/',
                $quantity
            )) {
                throw ValidationException::withMessages([
                    'quantity' =>
                        'Enter a valid quantity with up to three decimal places.',
                ]);
            }

            [$whole, $fraction] = array_pad(
                explode('.', $quantity, 2),
                2,
                ''
            );

            $requestedUnits =
                ((int) $whole * 1000) +
                (int) str_pad($fraction, 3, '0');

            if ($requestedUnits <= 0) {
                throw ValidationException::withMessages([
                    'quantity' =>
                        'Sale quantity must be greater than zero.',
                ]);
            }

            $availableUnits = (int) bcmul(
                (string) $accessory->quantity,
                '1000',
                0
            );

            if ($requestedUnits > $availableUnits) {
                throw ValidationException::withMessages([
                    'quantity' =>
                        'Insufficient accessory stock for this sale.',
                ]);
            }

            $formatQuantity = static function (
                int $units
            ): string {
                return intdiv($units, 1000)
                    . '.'
                    . str_pad(
                        (string) ($units % 1000),
                        3,
                        '0',
                        STR_PAD_LEFT
                    );
            };

            $soldQuantity = $formatQuantity(
                $requestedUnits
            );

            $previousQuantity = $formatQuantity(
                $availableUnits
            );

            $newQuantity = $formatQuantity(
                $availableUnits - $requestedUnits
            );

            /*
             * Calculate prices using server-controlled
             * accessory values.
             */
            $sellingPrice =
                (string) $accessory->unit_selling_price;

            $costPrice =
                (string) $accessory->unit_buying_price;

            if (bccomp($sellingPrice, '0', 2) <= 0) {
                throw ValidationException::withMessages([
                    'accessory_id' =>
                        'A valid selling price must be configured before selling this accessory.',
                ]);
            }

            $totalAmount = bcmul(
                $soldQuantity,
                $sellingPrice,
                2
            );

            $costOfGoodsSold = bcmul(
                $soldQuantity,
                $costPrice,
                2
            );

            $grossProfit = bcsub(
                $totalAmount,
                $costOfGoodsSold,
                2
            );

            /*
             * Deduct stock.
             */
            $accessory->quantity = $newQuantity;
            $accessory->save();

            /*
             * Record the outgoing stock movement.
             */
            $movementId = DB::table(
                'accessory_stock_movements'
            )->insertGetId([
                'accessory_id' => $accessory->id,
                'movement_type' => 'sale',
                'previous_quantity' => $previousQuantity,
                'quantity_added' => '-' . $soldQuantity,
                'new_quantity' => $newQuantity,
                'unit_buying_price' => $costPrice,
                'remarks' => 'Accessory sold through POS.',
                'created_by' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $saleNumber = 'AS-' . str_pad(
                (string) $movementId,
                8,
                '0',
                STR_PAD_LEFT
            );

            /*
             * Record the accessory sale and payment
             * information in the same transaction.
             */
            $sale = AccessorySale::query()->create([
                'sale_number' => $saleNumber,
                'transaction_token' => $transactionToken,
                'sale_date' => now()->toDateString(),
                'branch_id' => $activeBranch->id,
                'accessory_id' => $accessory->id,
                'attendant_id' => $user->id,
                'quantity' => $soldQuantity,
                'unit_selling_price' => $sellingPrice,
                'total_amount' => $totalAmount,
                'payment_method' => $paymentMethod,
                'payment_reference' => $paymentReference,
                'unit_cost_price' => $costPrice,
                'cost_of_goods_sold' => $costOfGoodsSold,
                'gross_profit' => $grossProfit,
                'stock_movement_id' => $movementId,
                'status' => AccessorySale::STATUS_POSTED,
                'posted_at' => now(),
                'created_by' => $user->id,
            ]);

            /*
             * Finalize the centrally registered
             * transaction token.
             */
            $updated = DB::table('pos_transaction_tokens')
                ->where('transaction_token', $transactionToken)
                ->where('user_id', $userId)
                ->where('sale_type', 'accessory')
                ->whereNull('sale_id')
                ->update([
                    'sale_id' => $sale->id,
                    'updated_at' => now(),
                ]);

            if ($updated !== 1) {
                throw ValidationException::withMessages([
                    'transaction_token' =>
                        'Unable to finalize the POS transaction token.',
                ]);
            }

            app(CashbookPostingService::class)
                ->recordAccessorySale($sale);

            return $sale;
        }, 3);
    }
}
