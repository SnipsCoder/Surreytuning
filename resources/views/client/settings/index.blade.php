<x-layouts.client>
    <x-page-header title="Settings" subtitle="Manage your account and preferences" />

    <x-flash-messages />

    <div
        x-data="{ tab: '{{ old('_tab', 'account') }}' }"
        class="space-y-6"
    >
        {{-- Tabs --}}
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex gap-6">
                @foreach (['account' => 'Account', 'profile' => 'Profile', 'security' => 'Security', 'notifications' => 'Notifications'] as $key => $label)
                    <button type="button" x-on:click="tab = '{{ $key }}'"
                        :class="tab === '{{ $key }}' ? 'border-brand text-brand' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                        class="py-3 text-sm font-medium border-b-2 whitespace-nowrap">
                        {{ $label }}
                    </button>
                @endforeach
            </nav>
        </div>

        {{-- Account Tab --}}
        <div x-show="tab === 'account'">
            <form method="POST" action="{{ route('client.settings.update') }}" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 space-y-4 max-w-xl">
                @csrf
                @method('PATCH')
                <input type="hidden" name="_tab" value="account">

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company Name</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $dealer->company_name) }}" required
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Country</label>
                    <input type="text" name="country" value="{{ old('country', $dealer->country) }}" required
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Invoice Address</label>
                    <textarea name="invoice_address" rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm">{{ old('invoice_address', $dealer->invoice_address) }}</textarea>
                </div>

                <div class="pt-2">
                    <button type="submit" class="px-4 py-2 rounded-md bg-brand text-white text-sm font-medium hover:bg-[#c92a0f]">Save Changes</button>
                </div>
            </form>
        </div>

        {{-- Profile Tab --}}
        <div x-show="tab === 'profile'">
            <form method="POST" action="{{ route('client.settings.update') }}" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 space-y-4 max-w-xl">
                @csrf
                @method('PATCH')
                <input type="hidden" name="_tab" value="profile">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">First Name</label>
                        <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" required
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Last Name</label>
                        <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" required
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">WhatsApp Number</label>
                    <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $user->whatsapp_number) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm">
                </div>

                <div class="pt-2">
                    <button type="submit" class="px-4 py-2 rounded-md bg-brand text-white text-sm font-medium hover:bg-[#c92a0f]">Save Changes</button>
                </div>
            </form>
        </div>

        {{-- Security Tab --}}
        <div x-show="tab === 'security'">
            <form method="POST" action="{{ route('client.settings.update') }}" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 space-y-4 max-w-xl">
                @csrf
                @method('PATCH')
                <input type="hidden" name="_tab" value="security">

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Current Password</label>
                    <input type="password" name="current_password" required
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">New Password</label>
                    <input type="password" name="password" required
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm New Password</label>
                    <input type="password" name="password_confirmation" required
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm">
                </div>

                <div class="pt-2">
                    <button type="submit" class="px-4 py-2 rounded-md bg-brand text-white text-sm font-medium hover:bg-[#c92a0f]">Update Password</button>
                </div>
            </form>
        </div>

        {{-- Notifications Tab --}}
        <div x-show="tab === 'notifications'">
            <form method="POST" action="{{ route('client.settings.update') }}" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 space-y-4 max-w-xl">
                @csrf
                @method('PATCH')
                <input type="hidden" name="_tab" value="notifications">

                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Email Notifications</h3>

                <label class="flex items-center gap-3">
                    <input type="hidden" name="notify_comments_email" value="0">
                    <input type="checkbox" name="notify_comments_email" value="1" @checked(old('notify_comments_email', $user->notify_comments_email))
                        class="rounded border-gray-300 text-brand shadow-sm">
                    <span class="text-sm text-gray-700 dark:text-gray-300">New comment on my file orders</span>
                </label>

                <label class="flex items-center gap-3">
                    <input type="hidden" name="notify_file_requests_email" value="0">
                    <input type="checkbox" name="notify_file_requests_email" value="1" @checked(old('notify_file_requests_email', $user->notify_file_requests_email))
                        class="rounded border-gray-300 text-brand shadow-sm">
                    <span class="text-sm text-gray-700 dark:text-gray-300">File request status updates (email)</span>
                </label>

                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 pt-2">SMS Notifications</h3>

                <label class="flex items-center gap-3">
                    <input type="hidden" name="notify_file_requests_sms" value="0">
                    <input type="checkbox" name="notify_file_requests_sms" value="1" @checked(old('notify_file_requests_sms', $user->notify_file_requests_sms))
                        class="rounded border-gray-300 text-brand shadow-sm">
                    <span class="text-sm text-gray-700 dark:text-gray-300">File request status updates (SMS)</span>
                </label>

                <div class="pt-2">
                    <button type="submit" class="px-4 py-2 rounded-md bg-brand text-white text-sm font-medium hover:bg-[#c92a0f]">Save Preferences</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.client>
