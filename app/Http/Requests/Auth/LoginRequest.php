<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login' => [
                'required',
                'string',
                'max:255',
            ],

            'password' => [
                'required',
                'string',
            ],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $login = trim((string) $this->input('login'));
        $password = (string) $this->input('password');

        /*
         * Owner authenticates with the seeded email.
         * Staff authenticate with their registered phone.
         */
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $credentials = [
                'email' => $login,
                'password' => $password,
            ];

            $ownerOnly = true;
        } else {
            $phone = $this->normalizePhone($login);

            $credentials = [
                'phone' => $phone,
                'password' => $password,
            ];

            $ownerOnly = false;
        }

        $authenticated = Auth::attempt(
            $credentials,
            $this->boolean('remember')
        );

        /*
         * Enforce login identifier rules.
         * An owner cannot use phone login,
         * and staff cannot use email login.
         */
        if ($authenticated) {
            $user = Auth::user();

            $validRole = $ownerOnly
                ? $user->hasRole('owner')
                : (
                    $user->hasRole('manager') ||
                    $user->hasRole('attendant')
                );

            if (! $validRole) {
                Auth::logout();
                $authenticated = false;
            }
        }

        if (! $authenticated) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        $login = trim((string) $this->input('login'));

        if (! filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $login = $this->normalizePhone($login);
        }

        return Str::transliterate(
            Str::lower($login) . '|' . $this->ip()
        );
    }

    private function normalizePhone(string $phone): string
    {
        $phone = trim($phone);

        if (str_starts_with($phone, '+234')) {
            return '0' . substr($phone, 4);
        }

        return $phone;
    }
}
