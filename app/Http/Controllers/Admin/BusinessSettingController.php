<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BusinessSettingController extends Controller
{
    public function edit(Request $request): View
    {
        $this->authorizeOwner($request);

        $settings = BusinessSetting::current();

        return view('admin.settings.edit', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorizeOwner($request);

        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'trading_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'receipt_heading' => ['nullable', 'string', 'max:255'],
            'receipt_footer' => ['nullable', 'string', 'max:2000'],
            'receipt_prefix' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Za-z0-9-]+$/',
            ],
            'low_stock_threshold_kg' => [
                'required',
                'numeric',
                'min:0',
                'max:999999999.999',
            ],
            'timezone' => [
                'required',
                Rule::in(['Africa/Lagos']),
            ],
            'date_format' => [
                'required',
                Rule::in(['d/m/Y', 'd-m-Y', 'Y-m-d']),
            ],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $settings = BusinessSetting::query()
                ->lockForUpdate()
                ->firstOrFail();

            $fields = array_keys($validated);

            $oldValues = $settings->only($fields);

            $settings->fill($validated);
            $settings->updated_by = $request->user()->id;

            $newValues = $settings->only($fields);

            $changedOld = [];
            $changedNew = [];

            foreach ($fields as $field) {
                if (
                    (string) ($oldValues[$field] ?? '') !==
                    (string) ($newValues[$field] ?? '')
                ) {
                    $changedOld[$field] = $oldValues[$field] ?? null;
                    $changedNew[$field] = $newValues[$field] ?? null;
                }
            }

            if (empty($changedNew)) {
                return;
            }

            $settings->save();

            AuditLogger::log(
                action: 'updated',
                module: 'settings',
                description: 'Updated LPG-SIMS business settings',
                record: $settings,
                oldValues: $changedOld,
                newValues: $changedNew
            );
        });

        return redirect()
            ->route('admin.settings.edit')
            ->with('success', 'Business settings updated successfully.');
    }

    /**
     * Upload or replace the company logo.
     */
    public function uploadLogo(Request $request): RedirectResponse
    {
        $this->authorizeOwner($request);

        $request->validate([
            'logo' => [
                'required',
                'image',
                'mimes:png,jpg,jpeg,webp',
                'max:2048',
            ],
        ]);

        $newPath = $request->file('logo')
            ->store('business-logos', 'public');

        try {
            $oldPath = DB::transaction(function () use ($request, $newPath) {
                $settings = BusinessSetting::query()
                    ->lockForUpdate()
                    ->firstOrFail();

                $oldPath = $settings->logo_path;

                $settings->logo_path = $newPath;
                $settings->updated_by = $request->user()->id;
                $settings->save();

                AuditLogger::log(
                    action: 'logo_updated',
                    module: 'settings',
                    description: 'Updated the business logo',
                    record: $settings,
                    oldValues: ['logo_path' => $oldPath],
                    newValues: ['logo_path' => $newPath]
                );

                return $oldPath;
            });
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($newPath);
            throw $exception;
        }

        if ($oldPath && $oldPath !== $newPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()
            ->route('admin.settings.edit')
            ->with('success', 'Company logo uploaded successfully.');
    }

    /**
     * Remove the current company logo.
     */
    public function removeLogo(Request $request): RedirectResponse
    {
        $this->authorizeOwner($request);

        $oldPath = DB::transaction(function () use ($request) {
            $settings = BusinessSetting::query()
                ->lockForUpdate()
                ->firstOrFail();

            $oldPath = $settings->logo_path;

            if (!$oldPath) {
                return null;
            }

            $settings->logo_path = null;
            $settings->updated_by = $request->user()->id;
            $settings->save();

            AuditLogger::log(
                action: 'logo_removed',
                module: 'settings',
                description: 'Removed the business logo',
                record: $settings,
                oldValues: ['logo_path' => $oldPath],
                newValues: ['logo_path' => null]
            );

            return $oldPath;
        });

        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()
            ->route('admin.settings.edit')
            ->with('success', 'Company logo removed successfully.');
    }

    private function authorizeOwner(Request $request): void
    {
        abort_unless(
            $request->user()?->hasRole('owner'),
            403,
            'Only the Owner can manage business settings.'
        );
    }
}
