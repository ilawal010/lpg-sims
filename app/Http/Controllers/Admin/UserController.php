<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display registered users.
     */
    public function index(): View
    {
        $users = User::query()
            ->with(['role', 'branches'])
            ->orderBy('id')
            ->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show staff registration form.
     */
    public function create(): View
    {
        $roles = $this->staffRoles();
        $branches = $this->activeBranches();

        return view(
            'admin.users.create',
            compact('roles', 'branches')
        );
    }

    /**
     * Register a new staff member.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'phone' => [
                'required',
                'string',
                'regex:/^(?:0[789][01]\d{8}|\+234[789][01]\d{8})$/',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],

            'password' => [
                'required',
                'string',
                'min:12',
                'confirmed',
            ],

            'role_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id')
                    ->whereIn('name', ['manager', 'attendant']),
            ],

            'branch_id' => [
                'required',
                'integer',
                Rule::exists('branches', 'id')
                    ->where('status', 'active'),
            ],
        ]);

        $phone = $this->normalizePhone($validated['phone']);

        if (User::where('phone', $phone)->exists()) {
            return back()
                ->withErrors([
                    'phone' => 'This phone number is already registered.',
                ])
                ->withInput();
        }

        DB::transaction(function () use ($validated, $phone) {

            /*
             * Preserve existing staff-number allocation.
             */
            User::query()
                ->whereHas('role', function ($query) {
                    $query->where('name', 'owner');
                })
                ->orderBy('id')
                ->lockForUpdate()
                ->firstOrFail();

            $lastNumber = User::query()
                ->where('staff_number', 'like', 'INT-%')
                ->orderByRaw(
                    "CAST(SUBSTRING(staff_number, 5) AS UNSIGNED) DESC"
                )
                ->value('staff_number');

            $sequence = $lastNumber
                ? (int) substr($lastNumber, 4) + 1
                : 1;

            $staffNumber = 'INT-' . str_pad(
                (string) $sequence,
                4,
                '0',
                STR_PAD_LEFT
            );

            $user = new User();

            $user->name = $validated['name'];
            $user->staff_number = $staffNumber;
            $user->phone = $phone;
            $user->email = $validated['email'] ?? null;
            $user->password = $validated['password'];
            $user->role_id = $validated['role_id'];
            $user->must_change_password = true;

            $user->save();

            /*
             * Assign the staff member to one branch.
             */
            $user->branches()->sync([
                (int) $validated['branch_id'],
            ]);

            AuditLogger::log(
                action: 'created',
                module: 'staff',
                description: 'Registered staff account: '
                    . $user->name
                    . ' (' . $user->staff_number . ')',
                record: $user,
                newValues: [
                    'name' => $user->name,
                    'staff_number' => $user->staff_number,
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'role_id' => (int) $user->role_id,
                    'branch_id' => (int) $validated['branch_id'],
                    'must_change_password' => true,
                ]
            );
        });

        return redirect()
            ->route('admin.users.index')
            ->with(
                'success',
                'Staff account created successfully.'
            );
    }

    /**
     * Show staff editing form.
     */
    public function edit(User $user): View
    {
        if ($user->hasRole('owner')) {
            abort(403, 'The Owner account cannot be edited here.');
        }

        $roles = $this->staffRoles();
        $branches = $this->activeBranches();

        $user->load('branches');

        return view(
            'admin.users.edit',
            compact('user', 'roles', 'branches')
        );
    }

    /**
     * Update staff account and branch assignment.
     */
    public function update(
        Request $request,
        User $user
    ): RedirectResponse {
        if ($user->hasRole('owner')) {
            abort(403, 'The Owner account cannot be edited here.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'phone' => [
                'required',
                'string',
                'regex:/^(?:0[789][01]\d{8}|\+234[789][01]\d{8})$/',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'password' => [
                'nullable',
                'string',
                'min:12',
                'confirmed',
            ],

            'role_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id')
                    ->whereIn('name', ['manager', 'attendant']),
            ],

            'branch_id' => [
                'required',
                'integer',
                Rule::exists('branches', 'id')
                    ->where('status', 'active'),
            ],
        ]);

        $phone = $this->normalizePhone($validated['phone']);

        if (
            User::where('phone', $phone)
                ->where('id', '!=', $user->id)
                ->exists()
        ) {
            return back()
                ->withErrors([
                    'phone' => 'This phone number is already registered.',
                ])
                ->withInput();
        }

        DB::transaction(function () use (
            $user,
            $validated,
            $phone
        ) {
            /*
             * Capture existing non-sensitive values.
             */
            $oldValues = [
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'role_id' => (int) $user->role_id,
            ];

            $oldBranchIds = $user->branches()
                ->pluck('branches.id')
                ->map(fn ($id) => (int) $id)
                ->sort()
                ->values()
                ->all();

            /*
             * Update staff information.
             */
            $user->name = $validated['name'];
            $user->phone = $phone;
            $user->email = $validated['email'] ?? null;
            $user->role_id = $validated['role_id'];

            $passwordReset = ! empty($validated['password']);

            if ($passwordReset) {
                $user->password = $validated['password'];
                $user->must_change_password = true;
            }

            $user->save();

            /*
             * Replace existing branch assignment.
             */
            $newBranchId = (int) $validated['branch_id'];

            $user->branches()->sync([$newBranchId]);

            $newValues = [
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'role_id' => (int) $user->role_id,
            ];

            $changedOld = [];
            $changedNew = [];

            foreach ($oldValues as $field => $oldValue) {
                $newValue = $newValues[$field];

                if ($oldValue !== $newValue) {
                    $changedOld[$field] = $oldValue;
                    $changedNew[$field] = $newValue;
                }
            }

            /*
             * Audit branch assignment changes.
             */
            if ($oldBranchIds !== [$newBranchId]) {
                $changedOld['branch_ids'] = $oldBranchIds;
                $changedNew['branch_ids'] = [$newBranchId];
            }

            if (! empty($changedNew)) {
                AuditLogger::log(
                    action: 'updated',
                    module: 'staff',
                    description: 'Updated staff account: '
                        . $user->name
                        . ' (' . $user->staff_number . ')',
                    record: $user,
                    oldValues: $changedOld,
                    newValues: $changedNew
                );
            }

            if ($passwordReset) {
                AuditLogger::log(
                    action: 'password_reset',
                    module: 'staff',
                    description: 'Reset password for staff: '
                        . $user->name
                        . ' (' . $user->staff_number . ')',
                    record: $user,
                    newValues: [
                        'must_change_password' => true,
                    ]
                );
            }
        });

        return redirect()
            ->route('admin.users.index')
            ->with(
                'success',
                'Staff account updated successfully.'
            );
    }

    /**
     * Roles available for staff registration.
     */
    private function staffRoles()
    {
        return Role::query()
            ->whereIn('name', ['manager', 'attendant'])
            ->orderBy('display_name')
            ->get();
    }

    /**
     * Active branches available for staff assignment.
     */
    private function activeBranches()
    {
        return Branch::query()
            ->where('status', 'active')
            ->orderBy('branch_name')
            ->get();
    }

    /**
     * Standardize Nigerian phone numbers.
     */
    private function normalizePhone(string $phone): string
    {
        $phone = trim($phone);

        if (str_starts_with($phone, '+234')) {
            return '0' . substr($phone, 4);
        }

        return $phone;
    }
}
