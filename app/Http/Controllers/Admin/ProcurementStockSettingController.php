<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accessory;
use App\Models\Branch;
use App\Models\LpgStorageUnit;
use App\Models\ProcurementStockSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProcurementStockSettingController extends Controller
{
    /**
     * Display procurement threshold configurations.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless(
            $user
            && (
                $user->hasRole('owner')
                || (
                    $user->hasRole('manager')
                    && $user->hasPermission('reports.view')
                )
            ),
            403
        );

        $allowedBranchIds = $user->hasRole('owner')
            ? null
            : $user->branches()->pluck('branches.id')->all();

        $branches = Branch::query()
            ->when(
                $allowedBranchIds !== null,
                fn ($query) => $query->whereIn(
                    'id',
                    $allowedBranchIds
                )
            )
            ->orderBy('branch_name')
            ->get();

        $settings = ProcurementStockSetting::query()
            ->with('branch')
            ->when(
                $allowedBranchIds !== null,
                fn ($query) => $query->whereIn(
                    'branch_id',
                    $allowedBranchIds
                )
            )
            ->orderBy('branch_id')
            ->orderBy('item_type')
            ->orderBy('item_id')
            ->get();

        $storageUnits = LpgStorageUnit::query()
            ->where('status', 'active')
            ->when(
                $allowedBranchIds !== null,
                fn ($query) => $query->whereIn(
                    'branch_id',
                    $allowedBranchIds
                )
            )
            ->orderBy('storage_name')
            ->get();

        $accessories = Accessory::query()
            ->where('status', 'active')
            ->when(
                $allowedBranchIds !== null,
                fn ($query) => $query->whereIn(
                    'branch_id',
                    $allowedBranchIds
                )
            )
            ->orderBy('name')
            ->get();

        return view('admin.reports.procurement.settings', [
            'branches' => $branches,
            'settings' => $settings,
            'storageUnits' => $storageUnits,
            'accessories' => $accessories,
            'canManage' => $user->hasRole('owner'),
        ]);
    }

    /**
     * Create or update a procurement stock threshold.
     * Only the Owner may change procurement settings.
     */
    public function save(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless(
            $user && $user->hasRole('owner'),
            403
        );

        $data = $request->validate([
            'branch_id' => [
                'required',
                'integer',
                'exists:branches,id',
            ],
            'item_type' => [
                'required',
                'in:lpg,accessory',
            ],
            'item_id' => [
                'required',
                'integer',
                'min:1',
            ],
            'minimum_stock' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'target_stock' => [
                'required',
                'numeric',
                'gt:0',
            ],
        ]);

        $branchId = (int) $data['branch_id'];
        $itemId = (int) $data['item_id'];
        $target = (float) $data['target_stock'];

        if ($data['item_type'] === 'lpg') {
            $item = LpgStorageUnit::query()
                ->whereKey($itemId)
                ->where('branch_id', $branchId)
                ->where('status', 'active')
                ->first();

            if (!$item) {
                throw ValidationException::withMessages([
                    'item_id' => 'Invalid LPG storage unit for this branch.',
                ]);
            }

            $minimum = $data['minimum_stock'] ?? null;

            if ($minimum === null) {
                throw ValidationException::withMessages([
                    'minimum_stock' => 'LPG minimum stock is required.',
                ]);
            }

            $minimum = (float) $minimum;

            if (
                $minimum > $target
                || $target > (float) $item->capacity_kg
            ) {
                throw ValidationException::withMessages([
                    'target_stock' =>
                        'LPG thresholds must satisfy: minimum stock <= target stock <= storage capacity.',
                ]);
            }
        } else {
            $item = Accessory::query()
                ->whereKey($itemId)
                ->where('branch_id', $branchId)
                ->where('status', 'active')
                ->first();

            if (!$item) {
                throw ValidationException::withMessages([
                    'item_id' => 'Invalid accessory for this branch.',
                ]);
            }

            $minimum = null;

            if ($target < (float) $item->reorder_level) {
                throw ValidationException::withMessages([
                    'target_stock' =>
                        'Target stock cannot be below the accessory reorder level.',
                ]);
            }
        }

       
        DB::transaction(function () use (
            $data,
            $branchId,
            $itemId,
            $minimum,
            $user
        ) {
            $setting = ProcurementStockSetting::query()
                ->firstOrNew([
                    'branch_id' => $branchId,
                    'item_type' => $data['item_type'],
                    'item_id' => $itemId,
                ]);

            if (!$setting->exists) {
                $setting->created_by = $user->id;
            }

            $setting->minimum_stock = $minimum;
            $setting->target_stock = $data['target_stock'];
            $setting->is_active = true;
            $setting->updated_by = $user->id;

            $setting->save();
        });


        return back()->with(
            'success',
            'Procurement stock thresholds saved successfully.'
        );
    }
}
