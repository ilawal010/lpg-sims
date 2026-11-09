<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccessorySale;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccessorySaleController extends Controller
{
    /**
     * Display the receipt for a completed accessory sale.
     *
     * Owner:
     *   Access posted receipts across all branches.
     *
     * Manager:
     *   Access posted receipts belonging only to
     *   assigned active branches.
     *
     * Sales Agent:
     *   Access only personally processed posted
     *   sales from assigned active branches.
     */
    public function receipt(
        Request $request,
        AccessorySale $sale
    ): View {
        $user = $request->user();

        abort_unless($user, 403);

        $canViewSales = $user->hasPermission('sales.view');
        $canCreateSales = $user->hasPermission('sales.create');

        abort_unless(
            $canViewSales || $canCreateSales,
            403
        );

        /*
         * Only posted transactions have receipts.
         */
        abort_unless(
            $sale->status === AccessorySale::STATUS_POSTED,
            404,
            'Receipts are available only for posted accessory sales.'
        );

        /*
         * Manager:
         * Restrict receipts to assigned active branches.
         */
        if ($user->hasRole('manager')) {

            abort_unless(
                $canViewSales,
                403
            );

            abort_unless(
                $user->branches()
                    ->where('branches.id', $sale->branch_id)
                    ->where('branches.status', 'active')
                    ->exists(),
                403
            );

        /*
         * Sales Agent:
         * Require ownership and an active branch assignment.
         */
        } elseif ($user->hasRole('attendant')) {

            abort_unless(
                $canCreateSales,
                403
            );

            abort_unless(
                (int) $sale->attendant_id === (int) $user->id,
                403
            );

            abort_unless(
                $user->branches()
                    ->where('branches.id', $sale->branch_id)
                    ->where('branches.status', 'active')
                    ->exists(),
                403
            );

        /*
         * Owner:
         * Access posted receipts from every branch.
         */
        } else {

            abort_unless(
                $user->hasRole('owner') && $canViewSales,
                403
            );
        }

        /*
         * Load receipt information only after
         * authorization has succeeded.
         */
        $sale->load([
            'branch',
            'accessory',
            'attendant',
        ]);

        return view('admin.accessory-sales.receipt', [
            'sale' => $sale,
        ]);
    }
}
