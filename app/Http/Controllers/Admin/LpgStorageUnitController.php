<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\LpgStorageUnit;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LpgStorageUnitController extends Controller
{
    /**
     * Display registered LPG storage units.
     */
    public function index(Request $request): View
    {
        $this->authorizeManagement($request);

        $query = LpgStorageUnit::query()
            ->with(['branch', 'updatedBy']);

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where('storage_code', 'like', "%{$search}%")
                    ->orWhere('storage_name', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhereHas('branch', function ($branchQuery) use ($search) {
                        $branchQuery
                            ->where('branch_name', 'like', "%{$search}%")
                            ->orWhere('branch_code', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }

        if (in_array($request->input('storage_type'), [
            'tank',
            'cylinder',
        ], true)) {
            $query->where(
                'storage_type',
                $request->input('storage_type')
            );
        }

        if (in_array($request->input('status'), [
            'active',
            'inactive',
            'maintenance',
            'decommissioned',
        ], true)) {
            $query->where('status', $request->input('status'));
        }

        $storageUnits = $query
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $branches = Branch::query()
            ->orderBy('branch_name')
            ->get();

        return view(
            'admin.storage-units.index',
            compact('storageUnits', 'branches')
        );
    }

    /**
     * Display storage unit registration form.
     */
    public function create(Request $request): View
    {
        $this->authorizeManagement($request);

        $branches = Branch::query()
            ->active()
            ->orderBy('branch_name')
            ->get();

        return view(
            'admin.storage-units.create',
            compact('branches')
        );
    }

    /**
     * Register a new LPG storage unit.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManagement($request);

        $validated = $this->validatedData($request);

        DB::transaction(function () use ($validated, $request) {

            /*
             * Serialize automatic storage code generation.
             */
            $settings = DB::table('business_settings')
                ->where('id', 1)
                ->lockForUpdate()
                ->first();

            if (!$settings) {
                throw new \RuntimeException(
                    'Business settings must be initialized before registering storage units.'
                );
            }

            /*
             * Lock and verify the selected branch.
             */
            $branch = Branch::query()
                ->whereKey($validated['branch_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if (!$branch->isActive()) {
                throw ValidationException::withMessages([
                    'branch_id' => 'Storage units can only be registered under active branches.',
                ]);
            }

            $storageCode = $this->generateStorageCode(
                $validated['storage_type']
            );

            $storageUnit = LpgStorageUnit::create([
                ...$validated,
                'storage_code' => $storageCode,
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);

            AuditLogger::log(
                action: 'created',
                module: 'lpg_storage_units',
                description: 'Registered LPG storage unit '
                    . $storageUnit->storage_name
                    . ' (' . $storageUnit->storage_code . ')',
                record: $storageUnit,
                oldValues: [],
                newValues: $storageUnit->getAttributes()
            );

        }, 3);

        return redirect()
            ->route('admin.storage-units.index')
            ->with('success', 'LPG storage unit registered successfully.');
    }

    
    /**
     * Display storage unit editing form.
     */
    public function edit(
        Request $request,
        LpgStorageUnit $storageUnit
    ): View {
        $this->authorizeManagement($request);

        /*
        * Load the storage unit's branch information.
        */
        $storageUnit->load('branch');

        /*
        * Include active branches and the storage unit's
        * existing branch, even if it has become inactive.
        */
        $branches = Branch::query()
            ->where(function ($query) use ($storageUnit) {
                $query->where('status', 'active')
                    ->orWhere('id', $storageUnit->branch_id);
            })
            ->orderBy('branch_name')
            ->get();

        return view(
            'admin.storage-units.edit',
            compact('storageUnit', 'branches')
        );
    }


    /**
     * Update storage unit information.
     */
    public function update(
        Request $request,
        LpgStorageUnit $storageUnit
    ): RedirectResponse {
        $this->authorizeManagement($request);

        $validated = $this->validatedData($request);

        /*
         * Storage units must not be silently moved between branches.
         * Future stock-transfer workflows will handle relocation.
         */
        if ((int) $validated['branch_id'] !== (int) $storageUnit->branch_id) {
            throw ValidationException::withMessages([
                'branch_id' => 'Changing the branch of an existing storage unit is not permitted.',
            ]);
        }

        /*
         * Preserve the registered storage type.
         * Type changes could invalidate future inventory history.
         */
        if ($validated['storage_type'] !== $storageUnit->storage_type) {
            throw ValidationException::withMessages([
                'storage_type' => 'The storage type cannot be changed after registration.',
            ]);
        }

        DB::transaction(function () use (
            $validated,
            $request,
            $storageUnit
        ) {
            $oldValues = $storageUnit->only(
                array_keys($validated)
            );

            $storageUnit->fill($validated);

            $changes = $storageUnit->getDirty();

            if (empty($changes)) {
                return;
            }

            $storageUnit->updated_by = $request->user()->id;
            $storageUnit->save();

            AuditLogger::log(
                action: 'updated',
                module: 'lpg_storage_units',
                description: 'Updated LPG storage unit '
                    . $storageUnit->storage_name
                    . ' (' . $storageUnit->storage_code . ')',
                record: $storageUnit,
                oldValues: array_intersect_key(
                    $oldValues,
                    $changes
                ),
                newValues: $changes
            );

        }, 3);

        return redirect()
            ->route('admin.storage-units.index')
            ->with('success', 'LPG storage unit updated successfully.');
    }

    /**
     * Validate registration and updates.
     */
    private function validatedData(Request $request): array
    {
        return $request->validate([
            'branch_id' => [
                'required',
                'integer',
                Rule::exists('branches', 'id'),
            ],

            'storage_type' => [
                'required',
                Rule::in(['tank', 'cylinder']),
            ],

            'storage_name' => [
                'required',
                'string',
                'max:150',
            ],

            'capacity_kg' => [
                'required',
                'numeric',
                'gt:0',
                'decimal:0,3',
            ],

            'manufacturer' => [
                'nullable',
                'string',
                'max:150',
            ],

            'serial_number' => [
                'nullable',
                'string',
                'max:100',
            ],

            'installation_date' => [
                'nullable',
                'date',
            ],

            'status' => [
                'required',
                Rule::in([
                    'active',
                    'inactive',
                    'maintenance',
                    'decommissioned',
                ]),
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);
    }

    /**
     * Generate automatic storage codes.
     *
     * Tanks:     INT-T001, INT-T002...
     * Cylinders: INT-C001, INT-C002...
     */
    private function generateStorageCode(string $type): string
    {
        $prefix = $type === 'tank'
            ? 'INT-T'
            : 'INT-C';

        $latestNumber = LpgStorageUnit::query()
            ->where('storage_code', 'REGEXP', '^' . $prefix . '[0-9]+$')
            ->selectRaw(
                'MAX(CAST(SUBSTRING(storage_code, 6) AS UNSIGNED)) AS last_number'
            )
            ->value('last_number');

        $nextNumber = ((int) $latestNumber) + 1;

        return $prefix . str_pad(
            (string) $nextNumber,
            3,
            '0',
            STR_PAD_LEFT
        );
    }

    /**
     * Restrict storage unit management to the Owner.
     */
    private function authorizeManagement(Request $request): void
    {
        abort_unless(
            $request->user()?->hasRole('owner'),
            403,
            'Only the Owner can manage LPG storage units.'
        );
    }
}
