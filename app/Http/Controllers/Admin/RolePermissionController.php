<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Services\AuditLogger;

class RolePermissionController extends Controller
{
    /**
     * Display all roles and their permission counts.
     */
    public function index(): View
    {
        $this->authorizeOwner();
        $roles = Role::query()
            ->withCount(['users', 'permissions'])
            ->orderByRaw(
                "CASE name
                    WHEN 'owner' THEN 1
                    WHEN 'manager' THEN 2
                    WHEN 'attendant' THEN 3
                    ELSE 4
                END"
            )
            ->get();

        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Display the permission matrix for a role.
     */
    public function edit(Role $role): View
    {
        $this->authorizeOwner();
        $role->load('permissions');

        $permissions = Permission::query()
            ->orderBy('module')
            ->orderBy('display_name')
            ->get()
            ->groupBy(fn (Permission $permission) =>
                $permission->module ?: 'General'
            );

        $assignedPermissions = $role->permissions
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return view('admin.roles.edit', compact(
            'role',
            'permissions',
            'assignedPermissions'
        ));
    }

   
    /**
     * Update Manager or Attendant permissions
     * and record the change in the audit trail.
     */
    public function update(
        Request $request,
        Role $role
    ): RedirectResponse {
        $this->authorizeOwner();

        if ($role->name === 'owner') {
            abort(403, 'Owner permissions cannot be modified.');
        }

        if (! in_array($role->name, ['manager', 'attendant'], true)) {
            abort(403, 'This role cannot be modified.');
        }

        $validated = $request->validate([
            'permissions' => [
                'nullable',
                'array',
            ],
            'permissions.*' => [
                'integer',
                'distinct',
                Rule::exists('permissions', 'id'),
            ],
        ]);

        $permissionIds = array_map(
            'intval',
            $validated['permissions'] ?? []
        );

        DB::transaction(function () use ($role, $permissionIds) {

            // Capture permissions before the change.
            $oldPermissions = $role->permissions()
                ->pluck('permissions.name')
                ->sort()
                ->values()
                ->all();

            // Apply the new permissions.
            $role->permissions()->sync($permissionIds);

            // Capture permissions after the change.
            $newPermissions = $role->permissions()
                ->pluck('permissions.name')
                ->sort()
                ->values()
                ->all();

            // Record only actual changes.
            if ($oldPermissions !== $newPermissions) {

                AuditLogger::log(
                    action: 'permissions_updated',
                    module: 'roles',
                    description: 'Updated permissions for role: '
                        . $role->display_name,
                    record: $role,
                    oldValues: [
                        'permissions' => $oldPermissions,
                    ],
                    newValues: [
                        'permissions' => $newPermissions,
                    ]
                );
            }
        });

        return redirect()
            ->route('admin.roles.index')
            ->with(
                'success',
                $role->display_name
                    . ' permissions updated successfully.'
            );
    }

    private function authorizeOwner(): void
    {
        abort_unless(auth()->user()?->hasRole('owner'), 403);
    }

    
}
