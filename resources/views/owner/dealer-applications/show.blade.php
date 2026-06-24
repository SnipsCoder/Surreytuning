<x-layouts.owner>
    <x-page-header :title="$application->company_name" subtitle="Dealer application">
        <x-status-badge :status="$application->status->label()" :colour="$application->status->colour()" />
    </x-page-header>

    <div class="max-w-2xl space-y-6">
        <dl class="grid grid-cols-2 gap-4">
            <div>
                <dt class="text-sm text-gray-500 dark:text-gray-400">Contact Name</dt>
                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $application->contact_name }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500 dark:text-gray-400">Email</dt>
                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $application->email }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500 dark:text-gray-400">Phone</dt>
                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $application->phone ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500 dark:text-gray-400">Country</dt>
                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $application->country }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500 dark:text-gray-400">Applied</dt>
                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $application->created_at->format('d M Y H:i') }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500 dark:text-gray-400">Terms Accepted</dt>
                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $application->terms_accepted_at?->format('d M Y H:i') ?? '—' }}</dd>
            </div>
        </dl>

        <div>
            <dt class="text-sm text-gray-500 dark:text-gray-400 mb-1">Message</dt>
            <dd class="text-sm text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ $application->message ?? '—' }}</dd>
        </div>

        @if ($application->status->value !== 'pending')
            <dl class="grid grid-cols-2 gap-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                <div>
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Reviewed By</dt>
                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $application->reviewedBy?->first_name }} {{ $application->reviewedBy?->last_name }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Reviewed At</dt>
                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $application->reviewed_at?->format('d M Y H:i') }}</dd>
                </div>
                @if ($application->rejection_reason)
                    <div class="col-span-2">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Rejection Reason</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $application->rejection_reason }}</dd>
                    </div>
                @endif
            </dl>
        @endif

        @if ($application->status->value === 'pending')
            <div x-data="{}" class="flex gap-3 border-t border-gray-200 dark:border-gray-700 pt-6">
                <form method="POST" action="{{ route('owner.dealer-applications.approve', $application) }}" onsubmit="return confirm('Approve this application and create a dealer account?');">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-md bg-green-600 text-white text-sm font-medium hover:bg-green-700">
                        Approve
                    </button>
                </form>

                <button type="button" @click="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'reject-application' }))" class="px-4 py-2 rounded-md bg-red-600 text-white text-sm font-medium hover:bg-red-700">
                    Reject
                </button>
            </div>
        @endif
    </div>

    @if ($application->status->value === 'pending')
        <x-modal id="reject-application" title="Reject Application">
            <form method="POST" action="{{ route('owner.dealer-applications.reject', $application) }}" class="space-y-4">
                @csrf

                <div>
                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reason</label>
                    <textarea id="rejection_reason" name="rejection_reason" rows="4" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" @click="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'reject-application' }))" class="px-4 py-2 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-md bg-red-600 text-white text-sm font-medium hover:bg-red-700">
                        Reject Application
                    </button>
                </div>
            </form>
        </x-modal>
    @endif
</x-layouts.owner>
