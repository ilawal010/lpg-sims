<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuditLogger
{
    /**
     * Fields that must never be stored in audit values.
     */
    private const SENSITIVE_FIELDS = [
        'password',
        'password_confirmation',
        'current_password',
        'password_hash',
        'remember_token',
        'api_token',
        'access_token',
        'refresh_token',
        'secret',
        'secret_key',
        'authorization',
        'credit_card',
        'card_number',
        'cvv',
        'pin',
    ];

    /**
     * Record a business or administrative activity.
     *
     * Example:
     *
     * AuditLogger::log(
     *     action: 'created',
     *     module: 'staff',
     *     description: 'Created a staff account',
     *     record: $user
     * );
     */
    public static function log(
        string $action,
        string $module,
        ?string $description = null,
        ?Model $record = null,
        array $oldValues = [],
        array $newValues = []
    ): AuditLog {
        $request = request();

        return AuditLog::create([
            'user_id' => Auth::id(),
            'action' => Str::lower(trim($action)),
            'module' => Str::lower(trim($module)),
            'description' => $description,
            'record_type' => $record
                ? $record->getMorphClass()
                : null,
            'record_id' => $record?->getKey(),
            'old_values' => self::sanitize($oldValues),
            'new_values' => self::sanitize($newValues),
            'ip_address' => $request?->ip(),
            'user_agent' => $request
                ? Str::limit(
                    (string) $request->userAgent(),
                    1000,
                    ''
                )
                : null,
        ]);
    }

    /**
     * Remove sensitive information recursively.
     */
    private static function sanitize(array $values): array
    {
        $clean = [];

        foreach ($values as $key => $value) {
            $normalizedKey = Str::lower(
                preg_replace(
                    '/[^a-z0-9]/i',
                    '_',
                    (string) $key
                )
            );

            if (self::isSensitive($normalizedKey)) {
                $clean[$key] = '[REDACTED]';
                continue;
            }

            if (is_array($value)) {
                $clean[$key] = self::sanitize($value);
                continue;
            }

            if (is_object($value)) {
                $clean[$key] = '[OBJECT]';
                continue;
            }

            $clean[$key] = $value;
        }

        return $clean;
    }

    /**
     * Check whether a field contains sensitive data.
     */
    private static function isSensitive(string $key): bool
    {
        foreach (self::SENSITIVE_FIELDS as $field) {
            if (
                $key === $field ||
                str_contains($key, $field)
            ) {
                return true;
            }
        }

        return false;
    }
}
