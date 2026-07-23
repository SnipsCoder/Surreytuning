<x-layouts.owner>
    <x-page-header title="Portal Users" subtitle="Owner and tuner accounts">
        <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-portal-user' }))"
            class="px-4 py-2 rounded-md bg-brand text-white text-sm font-medium hover:bg-[#c92a0f]">
            Invite User
        </button>
    </x-page-header>

    <x-data-table :headers="['Name', 'Email', 'Role', 'Status', '']">
        @forelse ($portalUsers as $portalUser)
            <tr class="hover:bg-gray-50 dark:hover:bg-[#1a1a1a]/60">
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $portalUser->first_name }} {{ $portalUser->last_name }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $portalUser->email }}</td>
                <td class="px-4 py-3 text-sm">
                    <x-status-badge :status="$portalUser->role->label()" colour="bg-blue-100 text-blue-800" />
                </td>
                <td class="px-4 py-3 text-sm">
                    @if ($portalUser->status === \App\Enums\UserStatus::Active)
                        <x-status-badge status="Active" colour="bg-green-100 text-green-800" />
                    @else
                        <x-status-badge status="Inactive" colour="bg-gray-100 text-gray-800" />
                    @endif
                </td>
                <td class="px-4 py-3 text-sm text-right">
                    @if ($portalUser->status === \App\Enums\UserStatus::Active)
                        <form method="POST" action="{{ route('portal-users.reset-password', $portalUser) }}" class="inline" onsubmit="return confirm('Send a password reset email to this user?')">
                            @csrf
                            <button type="submit" class="text-brand hover:underline">Reset password</button>
                        </form>
                        <form method="POST" action="{{ route('portal-users.destroy', $portalUser) }}" class="inline ml-3" onsubmit="return confirm('Deactivate this user?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">Deactivate</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No portal users found.</td>
            </tr>
        @endforelse
    </x-data-table>

    <x-modal id="create-portal-user" title="Invite User">
        <form method="POST" action="{{ route('portal-users.store') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">First Name</label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" required
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-[#2a2a2a] dark:bg-[#0d0d0d] dark:text-gray-100 text-sm shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Last Name</label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" required
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-[#2a2a2a] dark:bg-[#0d0d0d] dark:text-gray-100 text-sm shadow-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-[#2a2a2a] dark:bg-[#0d0d0d] dark:text-gray-100 text-sm shadow-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                <select name="role" required
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-[#2a2a2a] dark:bg-[#0d0d0d] dark:text-gray-100 text-sm shadow-sm">
                    @foreach ([\App\Enums\UserRole::Owner, \App\Enums\UserRole::Technician, \App\Enums\UserRole::Tuner] as $roleOption)
                        <option value="{{ $roleOption->value }}" @selected(old('role', \App\Enums\UserRole::Owner->value) === $roleOption->value)>
                            {{ $roleOption->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" x-on:click="open = false" class="px-4 py-2 rounded-md text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 rounded-md bg-brand text-white text-sm font-medium hover:bg-[#c92a0f]">
                    Save
                </button>
            </div>
        </form>
    </x-modal>
</x-layouts.owner>
