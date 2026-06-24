<x-layouts.owner>
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
        <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
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
                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                    <textarea id="notes" name="notes" rows="5" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $dealer->notes) }}</textarea>
                </div>

                <button type="submit" class="px-4 py-2 rounded-md bg-gray-900 dark:bg-gray-700 text-white text-sm font-medium hover:bg-gray-800 dark:hover:bg-gray-600">
                    Save Notes
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
                        <p class="text-sm text-gray-500 dark:text-gray-400">Slave Credits</p>
                        <p class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format((float) $dealer->slave_credit_balance, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">EVC Credits</p>
                        <p class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format((float) $dealer->evc_credit_balance, 2) }}</p>
                    </div>
                </div>

                <button type="button" @click="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'adjust-credits' }))" class="px-4 py-2 rounded-md bg-gray-900 dark:bg-gray-700 text-white text-sm font-medium hover:bg-gray-800 dark:hover:bg-gray-600">
                    Adjust Credits
                </button>
            </div>

            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mt-6 mb-2">Slave Credit Transactions</h3>
            <x-data-table :headers="['Amount', 'Reason', 'Date']">
                @forelse ($dealer->slaveCreditTransactions as $transaction)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ number_format((float) $transaction->amount, 2) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $transaction->reason }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $transaction->created_at->format('d M Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No slave credit transactions found.</td>
                    </tr>
                @endforelse
            </x-data-table>

            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mt-6 mb-2">EVC Credit Transactions</h3>
            <x-data-table :headers="['Amount', 'Reason', 'Date']">
                @forelse ($dealer->evcCreditTransactions as $transaction)
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
        </div>
    </div>

    <x-modal id="adjust-credits" title="Adjust Credits">
        <form method="POST" action="{{ route('owner.dealers.credits', $dealer) }}" class="space-y-4">
            @csrf

            <div>
                <label for="credit_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Credit Type</label>
                <select id="credit_type" name="credit_type" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm shadow-sm">
                    <option value="slave">Slave Credits</option>
                    <option value="evc">EVC Credits</option>
                </select>
            </div>

            <div>
                <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount (use negative to deduct)</label>
                <input type="number" step="0.01" id="amount" name="amount" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm shadow-sm">
            </div>

            <div>
                <label for="reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reason</label>
                <input type="text" id="reason" name="reason" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm shadow-sm">
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" @click="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'adjust-credits' }))" class="px-4 py-2 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 rounded-md bg-gray-900 dark:bg-gray-700 text-white text-sm font-medium hover:bg-gray-800 dark:hover:bg-gray-600">
                    Save
                </button>
            </div>
        </form>
    </x-modal>
</x-layouts.owner>
