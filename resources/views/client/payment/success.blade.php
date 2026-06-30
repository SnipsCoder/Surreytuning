<x-layouts.client>
    <x-page-header title="{{ $verified ? 'Payment Successful' : 'Payment Received' }}" />

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 max-w-lg text-sm text-gray-700 dark:text-gray-300">
        @if ($verified)
            <p class="mb-4">Thank you — your payment has been confirmed. Your balance or order will update shortly.</p>
        @else
            <p class="mb-4">Your payment is being processed. Your balance or order will update once the payment is confirmed.</p>
        @endif
        <a href="{{ route('client.dashboard') }}" class="text-orange-600 hover:text-orange-800">Return to Dashboard</a>
    </div>
</x-layouts.client>
