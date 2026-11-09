<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class FirstLoginPasswordController extends Controller
{
    /**
     * Determine the correct landing page for each role.
     */
    private function destinationFor($user): string
    {
        if ($user->hasRole('attendant')) {
            return 'agent.dashboard';
        }

        if ($user->hasRole('manager')) {
            return 'manager.dashboard';
        }

        return 'dashboard';
    }

    /**
     * Display the mandatory first-login password form.
     */
    public function edit(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user->must_change_password) {
            return redirect()->route(
                $this->destinationFor($user)
            );
        }

        return view('auth.first-login-password');
    }

    /**
     * Process the mandatory password change.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => [
                'required',
                'current_password',
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(12),
            ],
        ]);

        $user = $request->user();

        if (Hash::check($validated['password'], $user->password)) {
            return back()->withErrors([
                'password' => 'Your new password must differ from your current password.',
            ]);
        }

        $user->password = $validated['password'];
        $user->must_change_password = false;
        $user->save();

        $request->session()->regenerate();

        return redirect()
            ->route($this->destinationFor($user))
            ->with(
                'success',
                'Your password has been changed successfully.'
            );
    }
}
