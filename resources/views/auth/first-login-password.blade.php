<x-guest-layout>

    <div class="mb-6 text-center">
        <h2 class="text-xl font-bold text-green-800">
            Intisar Oil &amp; Gas
        </h2>

        <p class="mt-2 text-sm text-gray-500">
            First Login Password Change
        </p>
    </div>

    <div class="mb-5 rounded-lg border border-yellow-200 bg-yellow-50 p-4">
        <p class="text-sm text-yellow-800">
            For security reasons, you must change your initial password
            before accessing LPG-SIMS.
        </p>
    </div>

    <form method="POST" action="{{ route('password.first-change.update') }}">

        @csrf
        @method('PUT')

        <div>
            <x-input-label for="current_password" value="Current Password" />

            <x-text-input id="current_password" name="current_password" type="password" class="mt-1 block w-full"
                required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="New Password" />

            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required
                minlength="6" autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />

            <p class="mt-2 text-xs text-gray-500">
                Minimum 6 characters.
            </p>
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Confirm New Password" />

            <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                class="mt-1 block w-full" required minlength="6" autocomplete="new-password" />
        </div>

        <div class="mt-6">
            <x-primary-button class="w-full justify-center">
                Change Password &amp; Continue
            </x-primary-button>
        </div>

    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-4 text-center">

        @csrf

        <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">
            Logout
        </button>
    </form>

</x-guest-layout>
