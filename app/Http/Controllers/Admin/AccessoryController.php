<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accessory;
use Illuminate\Http\Request;

class AccessoryController extends Controller
{
    /**
     * Display the accessories inventory register.
     */
    public function index(Request $request)
    {
        abort_unless(
            $request->user()
                && $request->user()->hasRole('owner')
                && $request->user()->hasPermission('inventory.view'),
            403
        );

        $query = Accessory::query()
            ->with('branch');

        // Search by accessory number or name.
        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where('accessory_number', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Filter by category.
        if ($request->filled('category')) {
            $query->where(
                'category',
                $request->input('category')
            );
        }

        // Filter by branch.
        if ($request->filled('branch_id')) {
            $query->where(
                'branch_id',
                $request->input('branch_id')
            );
        }

        // Filter by status.
        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->input('status')
            );
        }

        $accessories = $query
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $categories = Accessory::query()
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('admin.accessories.index', [
            'accessories' => $accessories,
            'categories' => $categories,
        ]);
    }
    /**
     * Display the form for registering a new accessory.
     */
    public function create(Request $request)
    {
        abort_unless(
            $request->user()
                && $request->user()->hasRole('owner')
                && $request->user()->hasPermission('inventory.view'),
            403
        );

        $branches = \App\Models\Branch::query()
            ->orderBy('branch_name')
            ->get();

        $categories = [
            'Cylinder',
            'Regulator',
            'Hose',
            'Burner',
            'Valve',
            'Cooker',
            'Other',
        ];

        $units = [
            'piece',
            'set',
            'pack',
            'pair',
            'metre',
        ];

        return view('admin.accessories.create', [
            'branches' => $branches,
            'categories' => $categories,
            'units' => $units,
        ]);
    }
    /**
     * Save a newly registered accessory.
     */
    public function store(Request $request)
    {
        abort_unless(
            $request->user()
                && $request->user()->hasRole('owner')
                && $request->user()->hasPermission('inventory.view'),
            403
        );

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],

            'category' => [
                'required',
                'string',
                'in:Cylinder,Regulator,Hose,Burner,Valve,Cooker,Other',
            ],

            'branch_id' => [
                'required',
                'integer',
                'exists:branches,id',
            ],

            'unit' => [
                'required',
                'string',
                'in:piece,set,pack,pair,metre',
            ],

            'quantity' => [
                'required',
                'numeric',
                'min:0',
                'max:999999999999.999',
            ],

            'reorder_level' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999999.999',
            ],

            'unit_buying_price' => [
                'required',
                'numeric',
                'min:0',
                'max:9999999999999999.99',
            ],

            'unit_selling_price' => [
                'required',
                'numeric',
                'min:0',
                'max:9999999999999999.99',
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'status' => [
                'required',
                'in:active,inactive',
            ],
        ]);

        $validated['reorder_level'] =
            $validated['reorder_level'] ?? 0;

        $accessory = \Illuminate\Support\Facades\DB::transaction(
            function () use ($validated) {
                return Accessory::create($validated);
            }
        );

        return redirect()
            ->route('admin.accessories.index')
            ->with(
                'success',
                "Accessory {$accessory->accessory_number} registered successfully."
            );
    }

    /**
     * Display the form for editing an accessory.
     *
     * The accessory number and current stock balance
     * are displayed but cannot be directly modified.
     */
    public function edit(Request $request, Accessory $accessory)
    {
        abort_unless(
            $request->user()
                && $request->user()->hasRole('owner')
                && $request->user()->hasPermission('inventory.view'),
            403
        );

        $branches = \App\Models\Branch::query()
            ->orderBy('branch_name')
            ->get();

        $categories = [
            'Cylinder',
            'Regulator',
            'Hose',
            'Burner',
            'Valve',
            'Cooker',
            'Other',
        ];

        $units = [
            'piece',
            'set',
            'pack',
            'pair',
            'metre',
        ];

        return view('admin.accessories.edit', compact(
            'accessory',
            'branches',
            'categories',
            'units'
        ));
    }


    /**
     * Update accessory details and safely add restocked quantity.
     */
    public function update(Request $request, Accessory $accessory)
    {
        abort_unless(
            $request->user()
                && $request->user()->hasRole('owner')
                && $request->user()->hasPermission('inventory.view'),
            403
        );

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category' => [
                'required',
                'in:Cylinder,Regulator,Hose,Burner,Valve,Cooker,Other',
            ],
            'branch_id' => [
                'required',
                'integer',
                'exists:branches,id',
            ],
            'unit' => [
                'required',
                'in:piece,set,pack,pair,metre',
            ],
            'reorder_level' => [
                'required',
                'numeric',
                'min:0',
                'max:999999999999.999',
            ],
            'restock_quantity' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999999.999',
            ],
            'unit_buying_price' => [
                'required',
                'numeric',
                'min:0',
                'max:9999999999999999.99',
            ],
            'unit_selling_price' => [
                'required',
                'numeric',
                'min:0',
                'max:9999999999999999.99',
            ],
            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'status' => [
                'required',
                'in:active,inactive',
            ],
        ]);

        $restockQuantity = (string) ($validated['restock_quantity'] ?? '0');

        unset($validated['restock_quantity']);

        \Illuminate\Support\Facades\DB::transaction(
            function () use ($accessory, $validated, $restockQuantity, $request) {

                $lockedAccessory = Accessory::query()
                    ->whereKey($accessory->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $previousQuantity = (string) $lockedAccessory->quantity;

                // Calculate quantities in thousandths to avoid
                // floating-point rounding errors.
                $toThousandths = static function (string $value): int {
                    $parts = explode('.', $value, 2);

                    $whole = (int) $parts[0];
                    $fraction = (int) str_pad(
                        substr($parts[1] ?? '', 0, 3),
                        3,
                        '0'
                    );

                    return ($whole * 1000) + $fraction;
                };

                $previousUnits = $toThousandths($previousQuantity);
                $addedUnits = $toThousandths($restockQuantity);

                $newUnits = $previousUnits + $addedUnits;

                if ($newUnits > 999999999999999) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'restock_quantity' =>
                            'The resulting stock quantity exceeds the allowed limit.',
                    ]);
                }

                $formatQuantity = static function (int $units): string {
                    return intdiv($units, 1000)
                        . '.'
                        . str_pad((string) ($units % 1000), 3, '0', STR_PAD_LEFT);
                };

                // Update accessory information.
                // Never modify accessory_number.
                $lockedAccessory->fill($validated);

                if ($addedUnits > 0) {
                    $lockedAccessory->quantity = $formatQuantity($newUnits);
                }

                $lockedAccessory->save();

                // Create an auditable restocking record.
                if ($addedUnits > 0) {
                    \Illuminate\Support\Facades\DB::table(
                        'accessory_stock_movements'
                    )->insert([
                        'accessory_id' => $lockedAccessory->id,
                        'movement_type' => 'restock',
                        'previous_quantity' => $formatQuantity($previousUnits),
                        'quantity_added' => $formatQuantity($addedUnits),
                        'new_quantity' => $formatQuantity($newUnits),
                        'unit_buying_price' => $validated['unit_buying_price'],
                        'remarks' => 'Accessory restocked through Edit Accessory.',
                        'created_by' => $request->user()->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        );

        return redirect()
            ->route('admin.accessories.index')
            ->with(
                'success',
                "Accessory {$accessory->accessory_number} updated successfully."
            );
    }

    /**
     * Display the restocking history of a specific accessory.
     */
    public function history(Request $request, Accessory $accessory)
    {
        abort_unless(
            $request->user()
                && $request->user()->hasRole('owner')
                && $request->user()->hasPermission('inventory.view'),
            403
        );

        $movements = \Illuminate\Support\Facades\DB::table(
            'accessory_stock_movements'
        )
            ->leftJoin(
                'users',
                'accessory_stock_movements.created_by',
                '=',
                'users.id'
            )
            ->where(
                'accessory_stock_movements.accessory_id',
                $accessory->id
            )
            ->select(
                'accessory_stock_movements.id',
                'accessory_stock_movements.movement_type',
                'accessory_stock_movements.previous_quantity',
                'accessory_stock_movements.quantity_added',
                'accessory_stock_movements.new_quantity',
                'accessory_stock_movements.unit_buying_price',
                'accessory_stock_movements.remarks',
                'accessory_stock_movements.created_at',
                'users.name as performed_by'
            )
            ->orderByDesc('accessory_stock_movements.id')
            ->paginate(20);

        $totalRestocked = \Illuminate\Support\Facades\DB::table(
            'accessory_stock_movements'
        )
            ->where('accessory_id', $accessory->id)
            ->where('movement_type', 'restock')
            ->sum('quantity_added');

        $restockCount = \Illuminate\Support\Facades\DB::table(
            'accessory_stock_movements'
        )
            ->where('accessory_id', $accessory->id)
            ->where('movement_type', 'restock')
            ->count();

        return view('admin.accessories.history', compact(
            'accessory',
            'movements',
            'totalRestocked',
            'restockCount'
        ));
    }


}
