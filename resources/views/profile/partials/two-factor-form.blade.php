<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Two-Factor Authentication</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            @if (auth()->user()->two_factor_confirmed_at)
                Two-factor authentication is <strong class="text-green-600 dark:text-green-400">enabled</strong>
                via {{ auth()->user()->two_factor_method === 'totp' ? 'authenticator app' : 'email code' }}.
            @else
                Two-factor authentication is <strong class="text-gray-500">not enabled</strong>.
                @if (in_array(auth()->user()->role, [\App\Enums\UserRole::Owner, \App\Enums\UserRole::Tuner]))
                    It is required for your account.
                @endif
            @endif
        </p>
    </header>

    @if (session('success') && str_contains(session('success'), 'two-factor'))
        <div class="mt-3 p-3 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 rounded text-sm text-green-800 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif

    <div class="mt-4 flex items-center gap-4">
        @if (auth()->user()->two_factor_confirmed_at)
            <a href="{{ route('two-factor.setup') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Change Method
            </a>

            <form method="POST" action="{{ route('two-factor.disable') }}">
                @csrf
                <div class="flex items-center gap-2">
                    <x-text-input type="password" name="password" placeholder="Confirm password" class="text-sm py-1.5" required />
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Disable
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </form>
        @else
            <a href="{{ route('two-factor.setup') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Enable Two-Factor Authentication
            </a>
        @endif
    </div>
</section>
