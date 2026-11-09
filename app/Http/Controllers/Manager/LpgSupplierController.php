<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\LpgSupplier;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LpgSupplierController extends Controller
{
    /**
     * Display the central LPG Supplier Register
     * for authorized Branch Managers.
     *
     * Read-only access.
     */
    public function index(Request $request): View
    {
        $manager = $request->user();

        /*
         * Only authorized Managers can access
         * the supplier directory.
         */
        abort_unless(
            $manager
                && $manager->hasRole('manager')
                && $manager->hasPermission('inventory.view'),
            403
        );

        /*
         * The Manager must have at least one
         * assigned active branch.
         *
         * Suppliers are centrally registered,
         * so supplier records are not filtered
         * by branch.
         */
        $hasActiveBranch = $manager->branches()
            ->where('branches.status', 'active')
            ->exists();

        abort_unless(
            $hasActiveBranch,
            403,
            'No active branch is assigned to this Manager.'
        );

        /*
         * Validate supported filters.
         */
        $filters = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:150',
            ],

            'status' => [
                'nullable',
                'in:active,inactive',
            ],
        ]);

        /*
         * Operational supplier information only.
         *
         * Registration numbers, tax identifiers,
         * internal notes and audit fields are
         * excluded from this interface.
         */
        $query = LpgSupplier::query()
            ->select([
                'id',
                'supplier_code',
                'supplier_name',
                'contact_person',
                'phone',
                'alternate_phone',
                'email',
                'address',
                'city',
                'state',
                'status',
            ]);

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['status'])) {
            $query->withStatus($filters['status']);
        }

        /*
         * Summary statistics represent the
         * central supplier directory.
         */
        $summary = [
            'total_suppliers' => LpgSupplier::query()
                ->count(),

            'active_suppliers' => LpgSupplier::query()
                ->active()
                ->count(),

            'inactive_suppliers' => LpgSupplier::query()
                ->inactive()
                ->count(),
        ];

        /*
         * Paginated read-only supplier register.
         */
        $suppliers = $query
            ->orderBy('supplier_name')
            ->paginate(20)
            ->withQueryString();

        return view('manager.inventory.suppliers', [
            'suppliers' => $suppliers,
            'summary' => $summary,
            'filters' => $filters,
        ]);
    }
}
