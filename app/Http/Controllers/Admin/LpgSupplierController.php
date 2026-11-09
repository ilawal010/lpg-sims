<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LpgSupplier;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LpgSupplierController extends Controller
{
    /**
     * Display the LPG Supplier Register.
     */
    public function index(Request $request): View
    {
        $this->authorizeManagement($request);

        $query = LpgSupplier::query()
            ->with('updatedBy');

        if ($request->filled('search')) {
            $query->search(
                (string) $request->input('search')
            );
        }

        if (in_array(
            $request->input('status'),
            ['active', 'inactive'],
            true
        )) {
            $query->withStatus(
                $request->input('status')
            );
        }

        $suppliers = $query
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $totalSuppliers = LpgSupplier::count();

        $activeSuppliers = LpgSupplier::active()->count();

        $inactiveSuppliers = LpgSupplier::inactive()->count();

        return view(
            'admin.suppliers.index',
            compact(
                'suppliers',
                'totalSuppliers',
                'activeSuppliers',
                'inactiveSuppliers'
            )
        );
    }

    /**
     * Display supplier registration form.
     */
    public function create(Request $request): View
    {
        $this->authorizeManagement($request);

        return view('admin.suppliers.create');
    }

    /**
     * Register a new LPG supplier.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManagement($request);

        $validated = $this->validatedData($request);

        DB::transaction(function () use ($validated, $request) {

            /*
             * Serialize supplier code generation using
             * the existing business settings singleton.
             */
            $settings = DB::table('business_settings')
                ->where('id', 1)
                ->lockForUpdate()
                ->first();

            if (!$settings) {
                throw new \RuntimeException(
                    'Business settings must be initialized before registering suppliers.'
                );
            }

            $supplierCode = $this->generateSupplierCode();

            $supplier = LpgSupplier::create([
                ...$validated,
                'supplier_code' => $supplierCode,
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);

            AuditLogger::log(
                action: 'created',
                module: 'lpg_suppliers',
                description:
                    'Registered LPG supplier '
                    . $supplier->supplier_name
                    . ' ('
                    . $supplier->supplier_code
                    . ')',
                record: $supplier,
                oldValues: [],
                newValues: $supplier->getAttributes()
            );

        }, 3);

        return redirect()
            ->route('admin.suppliers.index')
            ->with(
                'success',
                'LPG supplier registered successfully.'
            );
    }

    /**
     * Display supplier editing form.
     */
    public function edit(
        Request $request,
        LpgSupplier $supplier
    ): View {
        $this->authorizeManagement($request);

        return view(
            'admin.suppliers.edit',
            compact('supplier')
        );
    }

    /**
     * Update an existing LPG supplier.
     */
    public function update(
        Request $request,
        LpgSupplier $supplier
    ): RedirectResponse {
        $this->authorizeManagement($request);

        $validated = $this->validatedData($request);

        DB::transaction(function () use (
            $validated,
            $request,
            $supplier
        ) {
            $supplier = LpgSupplier::query()
                ->lockForUpdate()
                ->findOrFail($supplier->id);

            $oldValues = $supplier->only(
                array_keys($validated)
            );

            $supplier->fill($validated);

            $changes = $supplier->getDirty();

            if (empty($changes)) {
                return;
            }

            $supplier->updated_by = $request->user()->id;

            $supplier->save();

            AuditLogger::log(
                action: 'updated',
                module: 'lpg_suppliers',
                description:
                    'Updated LPG supplier '
                    . $supplier->supplier_name
                    . ' ('
                    . $supplier->supplier_code
                    . ')',
                record: $supplier,
                oldValues: array_intersect_key(
                    $oldValues,
                    $changes
                ),
                newValues: $changes
            );

        }, 3);

        return redirect()
            ->route('admin.suppliers.index')
            ->with(
                'success',
                'LPG supplier updated successfully.'
            );
    }

    /**
     * Validate supplier information.
     *
     * Supplier codes and accountability fields are
     * deliberately excluded from user input.
     */
    private function validatedData(Request $request): array
    {
        return $request->validate([
            'supplier_name' => [
                'required',
                'string',
                'max:180',
            ],

            'contact_person' => [
                'nullable',
                'string',
                'max:150',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'alternate_phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'email' => [
                'nullable',
                'email',
                'max:150',
            ],

            'address' => [
                'nullable',
                'string',
            ],

            'city' => [
                'nullable',
                'string',
                'max:100',
            ],

            'state' => [
                'nullable',
                'string',
                'max:100',
            ],

            'registration_number' => [
                'nullable',
                'string',
                'max:100',
            ],

            'tax_identification_number' => [
                'nullable',
                'string',
                'max:100',
            ],

            'status' => [
                'required',
                Rule::in(['active', 'inactive']),
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ]);
    }

    /**
     * Generate the next unique supplier code.
     *
     * Examples:
     * INT-S001
     * INT-S002
     * INT-S003
     */
    private function generateSupplierCode(): string
    {
        $latestNumber = LpgSupplier::query()
            ->where(
                'supplier_code',
                'REGEXP',
                '^INT-S[0-9]+$'
            )
            ->selectRaw(
                'MAX(CAST(SUBSTRING(supplier_code, 6) AS UNSIGNED)) AS last_number'
            )
            ->value('last_number');

        $nextNumber = ((int) $latestNumber) + 1;

        return 'INT-S'
            . str_pad(
                (string) $nextNumber,
                3,
                '0',
                STR_PAD_LEFT
            );
    }

    /**
     * Restrict supplier management to the Owner.
     */
    private function authorizeManagement(Request $request): void
    {
        abort_unless(
            $request->user()?->hasRole('owner'),
            403,
            'Only the Owner can manage LPG suppliers.'
        );
    }
}
