<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LpgPrice;
use App\Models\LpgPriceHistory;
use App\Services\LpgPricingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LpgPricingController extends Controller
{
    /**
     * Display current LPG pricing and price history.
     */
    public function index(Request $request): View
    {
        $this->authorizeOwner($request);

        $price = LpgPrice::current()->load('updatedBy');

        $histories = LpgPriceHistory::query()
            ->with('changedBy')
            ->latest('effective_at')
            ->latest('id')
            ->paginate(20);

        return view('admin.lpg-pricing.index', compact(
            'price',
            'histories'
        ));
    }

    /**
     * Update the approved LPG selling price.
     */
    public function update(
        Request $request,
        LpgPricingService $pricingService
    ): RedirectResponse {
        $this->authorizeOwner($request);

        $validated = $request->validate([
            'selling_price_per_kg' => [
                'required',
                'regex:/^\d{1,13}(?:\.\d{1,2})?$/',
            ],
            'reason' => [
                'required',
                'string',
                'min:5',
                'max:2000',
            ],
        ]);

        $pricingService->updatePrice(
            newPrice: $validated['selling_price_per_kg'],
            reason: $validated['reason'],
            user: $request->user()
        );

        return redirect()
            ->route('admin.lpg-pricing.index')
            ->with(
                'success',
                'LPG selling price updated successfully.'
            );
    }

    /**
     * Only the Owner can manage LPG pricing.
     */
    private function authorizeOwner(Request $request): void
    {
        abort_unless(
            $request->user()?->hasPermission('prices.manage'),
            403,
            'You are not authorized to manage LPG selling prices.'
        );
    }
}
