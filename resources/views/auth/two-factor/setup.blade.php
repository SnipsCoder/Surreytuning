<x-auth-layout>
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Two-Factor Authentication</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Add an extra layer of security to your account. Choose your preferred method below.
        </p>
    </div>

    @if (session('info'))
        <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded text-sm text-blue-800 dark:text-blue-300">
            {{ session('info') }}
        </div>
    @endif

    @if (session('success'))
        <div class="mb-4 p-3 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 rounded text-sm text-green-800 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif

    @if (session('email_otp_sent'))
        {{-- Email OTP confirm form --}}
        <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded text-sm text-blue-800 dark:text-blue-300">
            A 6-digit code has been sent to <strong>{{ auth()->user()->email }}</strong>. Enter it below to confirm.
        </div>

        <form method="POST" action="{{ route('two-factor.confirm') }}">
            @csrf
            <div>
                <x-input-label for="code" value="Verification Code" />
                <x-text-input id="code" name="code" type="text" inputmode="numeric" maxlength="6"
                    class="block mt-1 w-full tracking-widest text-center text-xl" autofocus autocomplete="one-time-code" />
                <x-input-error :messages="$errors->get('code')" class="mt-2" />
            </div>
            <div class="mt-4 flex items-center gap-3">
                <x-primary-button>Confirm & Enable</x-primary-button>
                <form method="POST" action="{{ route('two-factor.resend') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-sm text-gray-600 dark:text-gray-400 underline hover:text-gray-900 dark:hover:text-gray-100">
                        Resend code
                    </button>
                </form>
            </div>
        </form>

    @elseif (session('totp_secret'))
        {{-- TOTP QR + confirm form --}}
        <p class="mb-3 text-sm text-gray-600 dark:text-gray-400">
            Scan this QR code with your authenticator app (Google Authenticator, Authy, etc.), then enter the 6-digit code below.
        </p>

        <div class="flex justify-center mb-4 p-4 bg-white rounded-lg">
            {!! \App\Support\TwoFactor::generateQrSvg(session('totp_qr_url')) !!}
        </div>

        <p class="mb-3 text-xs text-center text-gray-500 dark:text-gray-400">
            Can't scan? Enter this key manually: <span class="font-mono font-semibold">{{ session('totp_secret') }}</span>
        </p>

        <form method="POST" action="{{ route('two-factor.confirm') }}">
            @csrf
            <div>
                <x-input-label for="code" value="6-digit code from your app" />
                <x-text-input id="code" name="code" type="text" inputmode="numeric" maxlength="6"
                    class="block mt-1 w-full tracking-widest text-center text-xl" autofocus autocomplete="one-time-code" />
                <x-input-error :messages="$errors->get('code')" class="mt-2" />
            </div>
            <div class="mt-4">
                <x-primary-button>Confirm & Enable</x-primary-button>
            </div>
        </form>

    @else
        {{-- Method selection --}}
        <div class="space-y-3">
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-1">Authenticator App (TOTP)</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                    Use Google Authenticator, Authy, or any TOTP app. Works offline and is the most secure option.
                </p>
                <form method="POST" action="{{ route('two-factor.setup.totp') }}">
                    @csrf
                    <x-primary-button>Set up authenticator app</x-primary-button>
                </form>
            </div>

            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-1">Email Code</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                    Receive a one-time code by email each time you log in. Codes expire after 10 minutes.
                </p>
                <form method="POST" action="{{ route('two-factor.setup.email') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Set up email code
                    </button>
                </form>
            </div>
        </div>
    @endif
</x-auth-layout>
