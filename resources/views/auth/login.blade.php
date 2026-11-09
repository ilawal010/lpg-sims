<x-guest-layout>

    <div class="mb-6 text-center">
        <h2 class="text-xl font-bold text-green-800">
            Intisar Oil &amp; Gas
        </h2>

        <p class="mt-2 text-sm text-gray-500">
            LPG Sales, Inventory &amp; Management System
        </p>

        <p class="mt-1 text-xs text-gray-400">
            Authorized Personnel Login
        </p>
    </div>

    <x-auth-session-status
        class="mb-4"
        :status="session('status')"
    />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label
                for="login"
                value="Email Address or Phone Number"
            />

            <x-text-input
                id="login"
                class="block mt-1 w-full"
                type="text"
                name="login"
                :value="old('login')"
                required
                autofocus
                autocomplete="username"
                placeholder="Enter email or phone number"
            />

            <x-input-error
                :messages="$errors->get('login')"
                class="mt-2"
            />
        </div>

        <div class="mt-4">
            <x-input-label
                for="password"
                value="Password"
            />

            <x-text-input
                id="password"
                class="block mt-1 w-full"
                type="password"
                name="password"
                required
                autocomplete="current-password"
            />

            <x-input-error
                :messages="$errors->get('password')"
                class="mt-2"
            />
        </div>

        <div class="block mt-4">
            <label for="remember_me"
                   class="inline-flex items-center">

                <input
                    id="remember_me"
                    type="checkbox"
                    name="remember"
                    class="rounded border-gray-300 text-green-700 shadow-sm focus:ring-green-600"
                >

                <span class="ms-2 text-sm text-gray-600">
                    Remember me
                </span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-primary-button>
                Sign In
            </x-primary-button>
        </div>

    </form>

    <div class="mt-6 text-center text-xs text-gray-400">
        Intisar Oil &amp; Gas — Authorized Personnel Only
    </div>

</x-guest-layout>
