<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * Display the audit trail.
     *
     * Audit logs are read-only and accessible only
     * to the Owner / Super Admin.
     */
    public function index(Request $request): View
    {
        abort_unless(
            $request->user()?->hasRole('owner'),
            403,
            'Only the Owner can access audit logs.'
        );

        $validated = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'module' => ['nullable', 'string', 'max:100'],
            'action' => ['nullable', 'string', 'max:50'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ]);

        $query = AuditLog::query()
            ->with('user')
            ->when(
                $validated['user_id'] ?? null,
                fn ($q, $value) => $q->where('user_id', $value)
            )
            ->when(
                $validated['module'] ?? null,
                fn ($q, $value) => $q->where('module', $value)
            )
            ->when(
                $validated['action'] ?? null,
                fn ($q, $value) => $q->where('action', $value)
            )
            ->when(
                $validated['date_from'] ?? null,
                fn ($q, $value) => $q->whereDate('created_at', '>=', $value)
            )
            ->when(
                $validated['date_to'] ?? null,
                fn ($q, $value) => $q->whereDate('created_at', '<=', $value)
            );

        $logs = $query
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $users = User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'staff_number']);

        $modules = AuditLog::query()
            ->distinct()
            ->orderBy('module')
            ->pluck('module');

        $actions = AuditLog::query()
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $totalLogs = AuditLog::count();

        $todayLogs = AuditLog::query()
            ->whereDate('created_at', today())
            ->count();

        $uniqueUsers = AuditLog::query()
            ->whereNotNull('user_id')
            ->distinct()
            ->count('user_id');

        return view('admin.audit-logs.index', compact(
            'logs',
            'users',
            'modules',
            'actions',
            'totalLogs',
            'todayLogs',
            'uniqueUsers'
        ));
    }

    /**
     * Display an individual audit record.
     */
    public function show(Request $request, AuditLog $auditLog): View
    {
        abort_unless(
            $request->user()?->hasRole('owner'),
            403,
            'Only the Owner can access audit logs.'
        );

        $auditLog->load('user');

        return view('admin.audit-logs.show', compact('auditLog'));
    }
}
