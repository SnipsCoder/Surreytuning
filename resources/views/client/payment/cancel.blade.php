<x-layouts.client>
    <x-page-header title="Payment Cancelled" />

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 max-w-lg text-sm text-gray-700 dark:text-gray-300">
        <p class="mb-4">Your payment was cancelled and no charge was made. You can try again at any time.</p>
        <a href="{{ route('client.dashboard') }}" class="text-orange-600 hover:text-orange-800">Return to Dashboard</a>
    </div>
</x-layouts.client>
