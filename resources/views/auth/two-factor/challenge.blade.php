<x-auth-layout>
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Two-Factor Authentication</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            @if ($user->two_factor_method === 'email')
                A 6-digit code has been sent to <strong>{{ $user->email }}</strong>. Enter it below to continue.
            @else
                Open your authenticator app and enter the current 6-digit code for this account.
            @endif
        </p>
    </div>

    @if (session('success'))
        <div class="mb-4 p-3 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 rounded text-sm text-green-800 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('two-factor.verify') }}">
        @csrf
        <div>
            <x-input-label for="code" value="Verification Code" />
            <x-text-input id="code" name="code" type="text" inputmode="numeric" maxlength="6"
                class="block mt-1 w-full tracking-widest text-center text-xl" autofocus autocomplete="one-time-code" />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="mt-4 flex items-center justify-between">
            <x-primary-button>Verify</x-primary-button>

            @if ($user->two_factor_method === 'email')
                <form method="POST" action="{{ route('two-factor.resend') }}">
                    @csrf
                    <button type="submit" class="text-sm text-gray-600 dark:text-gray-400 underline hover:text-gray-900 dark:hover:text-gray-100">
                        Resend code
                    </button>
                </form>
            @endif
        </div>
    </form>

    <div class="mt-6 text-center">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm text-gray-500 dark:text-gray-400 underline hover:text-gray-700 dark:hover:text-gray-300">
                Log out and use a different account
            </button>
        </form>
    </div>
</x-auth-layout>
