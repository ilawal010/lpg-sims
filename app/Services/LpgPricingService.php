<?php

namespace App\Services;

use App\Models\LpgPrice;
use App\Models\LpgPriceHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LpgPricingService
{
    /**
     * Set or update the LPG selling price per kilogram.
     */
    public function updatePrice(
        string $newPrice,
        string $reason,
        User $user
    ): LpgPrice {
        if (!$user->hasPermission('prices.manage')) {
            abort(403, 'You are not authorized to change LPG prices.');
        }

        $newPrice = trim($newPrice);
        $reason = trim($reason);

        if (
            !preg_match('/^\d{1,13}(?:\.\d{1,2})?$/', $newPrice) ||
            !preg_match('/[1-9]/', $newPrice)
        ) {
            throw ValidationException::withMessages([
                'selling_price_per_kg' =>
                    'Enter a valid selling price greater than zero, with at most two decimal places.',
            ]);
        }

        if (mb_strlen($reason) < 5 || mb_strlen($reason) > 2000) {
            throw ValidationException::withMessages([
                'reason' => 'Provide a reason between 5 and 2000 characters.',
            ]);
        }

        return DB::transaction(function () use (
            $newPrice,
            $reason,
            $user
        ) {
            // Lock the singleton price record to serialize price changes.
            $price = LpgPrice::query()
                ->whereKey(1)
                ->lockForUpdate()
                ->firstOrFail();

            $previousPrice = $price->selling_price_per_kg;

            $normalizedNewPrice = number_format(
                (float) $newPrice,
                2,
                '.',
                ''
            );

            if (
                $previousPrice !== null &&
                bccomp(
                    (string) $previousPrice,
                    $normalizedNewPrice,
                    2
                ) === 0
            ) {
                throw ValidationException::withMessages([
                    'selling_price_per_kg' =>
                        'The new selling price must differ from the current price.',
                ]);
            }

            $effectiveAt = now();

            $price->selling_price_per_kg = $normalizedNewPrice;
            $price->updated_by = $user->id;
            $price->effective_at = $effectiveAt;
            $price->save();

            LpgPriceHistory::create([
                'lpg_price_id' => $price->id,
                'previous_price_per_kg' => $previousPrice,
                'new_price_per_kg' => $normalizedNewPrice,
                'reason' => $reason,
                'changed_by' => $user->id,
                'effective_at' => $effectiveAt,
            ]);

            AuditLogger::log(
                action: 'price_updated',
                module: 'lpg_pricing',
                description: 'Changed LPG selling price per kg',
                record: $price,
                oldValues: [
                    'selling_price_per_kg' => $previousPrice,
                ],
                newValues: [
                    'selling_price_per_kg' => $normalizedNewPrice,
                    'reason' => $reason,
                ]
            );

            return $price->refresh();
        });
    }
}
