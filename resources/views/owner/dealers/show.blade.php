<x-layouts.owner>
    <a href="{{ route('dealers.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Dealers
    </a>

    <x-page-header :title="$dealer->company_name" subtitle="Dealer account">
        <x-status-badge :status="$dealer->status->label()" :colour="$dealer->status->colour()" />

        @if ($dealer->status->value === 'suspended')
            <form method="POST" action="{{ route('owner.dealers.reactivate', $dealer) }}">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-md bg-green-600 text-white text-sm font-medium hover:bg-green-700">
                    Reactivate
                </button>
            </form>
        @else
            <form method="POST" action="{{ route('owner.dealers.suspend', $dealer) }}" onsubmit="return confirm('Suspend this dealer?');">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-md bg-red-600 text-white text-sm font-medium hover:bg-red-700">
                    Suspend
                </button>
            </form>
        @endif
    </x-page-header>

    <div x-data="{ tab: 'overview' }">
        <div class="border-b border-gray-200 dark:border-[#2a2a2a] mb-6">
            <nav class="flex gap-6 -mb-px">
                <button @click="tab = 'overview'" :class="tab === 'overview' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 dark:text-gray-400'" class="py-3 border-b-2 text-sm font-medium">Overview</button>
                <button @click="tab = 'users'" :class="tab === 'users' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 dark:text-gray-400'" class="py-3 border-b-2 text-sm font-medium">Users</button>
                <button @click="tab = 'file-requests'" :class="tab === 'file-requests' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 dark:text-gray-400'" class="py-3 border-b-2 text-sm font-medium">File Requests</button>
                <button @click="tab = 'invoices'" :class="tab === 'invoices' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 dark:text-gray-400'" class="py-3 border-b-2 text-sm font-medium">Invoices</button>
                <button @click="tab = 'credits'" :class="tab === 'credits' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 dark:text-gray-400'" class="py-3 border-b-2 text-sm font-medium">Credits</button>
            </nav>
        </div>

        <div x-show="tab === 'overview'">
            <form method="POST" action="{{ route('dealers.update', $dealer) }}" class="space-y-4 max-w-2xl">
                @csrf
                @method('PATCH')

                <dl class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Country</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $dealer->country }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Joined</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $dealer->created_at->format('d M Y') }}</dd>
                    </div>
                </dl>

                <div>
                    <label for="discount_percentage" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Discount (%)</label>
                    <div class="relative max-w-[10rem]">
                        <input type="number" id="discount_percentage" name="discount_percentage"
                            min="0" max="100" step="0.01"
                            value="{{ old('discount_percentage', number_format((float) $dealer->discount_percentage, 2, '.', '')) }}"
                            class="w-full rounded-md border-gray-300 dark:border-[#2a2a2a] dark:bg-[#1a1a1a] dark:text-gray-100 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pr-8">
                        <span class="absolute inset-y-0 right-3 flex items-center text-sm text-gray-400">%</span>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Reduces the price this dealer pays across file credits, EVC bundles and products. Credits and goods received are unchanged.</p>
                    @error('discount_percentage')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                    <textarea id="notes" name="notes" rows="5" class="w-full rounded-md border-gray-300 dark:border-[#2a2a2a] dark:bg-[#1a1a1a] dark:text-gray-100 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $dealer->notes) }}</textarea>
                </div>

                <button type="submit" class="px-4 py-2 rounded-md bg-[#0d0d0d] dark:bg-gray-700 text-white text-sm font-medium hover:bg-[#1a1a1a] dark:hover:bg-gray-600">
                    Save Changes
                </button>
            </form>
        </div>

        <div x-show="tab === 'users'">
            <x-data-table :headers="['Name', 'Email', 'Role', 'Primary Contact']">
                @forelse ($dealer->users as $user)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $user->first_name }} {{ $user->last_name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $user->email }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $user->role->label() }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $user->is_primary_contact ? 'Yes' : 'No' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No users found.</td>
                    </tr>
                @endforelse
            </x-data-table>
        </div>

        <div x-show="tab === 'file-requests'">
            <x-data-table :headers="['Reference', 'Status', 'Created']">
                @forelse ($dealer->fileRequests as $fileRequest)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $fileRequest->request_number }}</td>
                        <td class="px-4 py-3 text-sm">
                            <x-status-badge :status="$fileRequest->status->label()" :colour="$fileRequest->status->colour()" />
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $fileRequest->created_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No file requests found.</td>
                    </tr>
                @endforelse
            </x-data-table>
        </div>

        <div x-show="tab === 'invoices'">
            <x-data-table :headers="['Number', 'Status', 'Total', 'Created']">
                @forelse ($dealer->invoices as $invoice)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $invoice->invoice_number ?? $invoice->id }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $invoice->status }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ number_format((float) $invoice->amount_gross, 2) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $invoice->created_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No invoices found.</td>
                    </tr>
                @endforelse
            </x-data-table>
        </div>

        <div x-show="tab === 'credits'">
            <div class="flex items-center justify-between mb-4">
                <div class="flex gap-8">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">File Credits</p>
                        <p class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format((float) $dealer->file_credit_balance, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">EVC Credits</p>
                        <p class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format((float) $dealer->evc_credit_balance, 2) }}</p>
                    </div>
                </div>

                <button type="button" @click="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'adjust-credits' }))" class="px-4 py-2 rounded-md bg-[#0d0d0d] dark:bg-gray-700 text-white text-sm font-medium hover:bg-[#1a1a1a] dark:hover:bg-gray-600">
                    Adjust Credits
                </button>
            </div>

            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mt-6 mb-2">File Credit Transactions</h3>
            <x-data-table :headers="['Amount', 'Reason', 'Date']">
                @forelse ($fileTransactions as $transaction)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ number_format((float) $transaction->amount, 2) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $transaction->reason }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $transaction->created_at->format('d M Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No file credit transactions found.</td>
                    </tr>
                @endforelse
            </x-data-table>
            @if ($fileTransactions->hasPages())
                <div class="mt-3">{{ $fileTransactions->links() }}</div>
            @endif

            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mt-6 mb-2">EVC Credit Transactions</h3>
            <x-data-table :headers="['Amount', 'Reason', 'Date']">
                @forelse ($evcTransactions as $transaction)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ number_format((float) $transaction->amount, 2) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $transaction->reason }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $transaction->created_at->format('d M Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No EVC credit transactions found.</td>
                    </tr>
                @endforelse
            </x-data-table>
            @if ($evcTransactions->hasPages())
                <div class="mt-3">{{ $evcTransactions->links() }}</div>
            @endif
        </div>
    </div>

    <x-modal id="adjust-credits" title="Adjust Credits">
        <form method="POST" action="{{ route('owner.dealers.credits', $dealer) }}" class="space-y-4">
            @csrf

            <div>
                <label for="credit_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Credit Type</label>
                <select id="credit_type" name="credit_type" class="w-full rounded-md border-gray-300 dark:border-[#2a2a2a] dark:bg-[#1a1a1a] dark:text-gray-100 text-sm shadow-sm">
                    <option value="file">File Credits</option>
                    <option value="evc">EVC Credits</option>
                </select>
            </div>

            <div>
                <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount (use negative to deduct)</label>
                <input type="number" step="0.01" id="amount" name="amount" class="w-full rounded-md border-gray-300 dark:border-[#2a2a2a] dark:bg-[#1a1a1a] dark:text-gray-100 text-sm shadow-sm">
            </div>

            <div>
                <label for="reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reason</label>
                <input type="text" id="reason" name="reason" class="w-full rounded-md border-gray-300 dark:border-[#2a2a2a] dark:bg-[#1a1a1a] dark:text-gray-100 text-sm shadow-sm">
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" @click="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'adjust-credits' }))" class="px-4 py-2 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 rounded-md bg-[#0d0d0d] dark:bg-gray-700 text-white text-sm font-medium hover:bg-[#1a1a1a] dark:hover:bg-gray-600">
                    Save
                </button>
            </div>
        </form>
    </x-modal>
</x-layouts.owner>
