<x-layouts.client>
    <x-page-header title="Payment Successful" />

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 max-w-lg text-sm text-gray-700 dark:text-gray-300">
        <p class="mb-4">Thank you — your payment has been received and is being processed. Your balance or order will update shortly.</p>
        <a href="{{ route('client.dashboard') }}" class="text-orange-600 hover:text-orange-800">Return to Dashboard</a>
    </div>
</x-layouts.client>
