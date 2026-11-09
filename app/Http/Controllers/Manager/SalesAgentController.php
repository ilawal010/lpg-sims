<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesAgentController extends Controller
{
    /**
     * Display Sales Agents assigned to the
     * authenticated Manager's active branches.
     *
     * Read-only access.
     */
    public function index(Request $request): View
    {
        $manager = $request->user();

        abort_unless(
            $manager
                && $manager->hasRole('manager')
                && $manager->hasPermission('sales.view'),
            403
        );

        /*
         * Retrieve only active branches
         * assigned to this Manager.
         */
        $branches = $manager->branches()
            ->where('branches.status', 'active')
            ->orderBy('branches.branch_name')
            ->get();

        $branchIds = $branches
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $filters = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:100',
            ],
            'branch_id' => [
                'nullable',
                'integer',
                'exists:branches,id',
            ],
        ]);

        /*
         * Reject attempts to access an
         * unauthorized branch through filters.
         */
        if (!empty($filters['branch_id'])) {

            abort_unless(
                in_array(
                    (int) $filters['branch_id'],
                    $branchIds,
                    true
                ),
                403
            );

            $branchIds = [
                (int) $filters['branch_id'],
            ];
        }

        /*
         * Find only Sales Agents assigned to
         * the Manager's authorized branches.
         */
        $agentsQuery = User::query()
            ->whereHas('role', function ($query) {
                $query->where('name', 'attendant');
            })
            ->whereHas('branches', function ($query) use ($branchIds) {
                $query->whereIn(
                    'branches.id',
                    $branchIds
                );
            });

        /*
         * Search by name, email,
         * telephone or staff number.
         */
        if (!empty($filters['search'])) {

            $search = '%' . $filters['search'] . '%';

            $agentsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', $search)
                    ->orWhere('email', 'like', $search)
                    ->orWhere('phone', 'like', $search)
                    ->orWhere('staff_number', 'like', $search);
            });
        }

        /*
         * Load only branches that the Manager
         * is authorized to supervise.
         */
        $agents = $agentsQuery
            ->with([
                'branches' => function ($query) use ($branchIds) {
                    $query
                        ->whereIn('branches.id', $branchIds)
                        ->where('branches.status', 'active')
                        ->orderBy('branches.branch_name');
                },
            ])
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('manager.agents.index', [
            'agents' => $agents,
            'branches' => $branches,
            'filters' => $filters,
        ]);
    }
}
