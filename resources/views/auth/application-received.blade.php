@extends('layouts.auth')

@section('content')
    <div class="text-center space-y-4">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-900/40 border border-green-700">
            <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-white">Application Received</h1>

        <p class="text-sm text-gray-400">
            Thank you, {{ session('contact_name') }}. Your application has been received.
            We'll review it and be in touch shortly.
        </p>

        <p class="text-sm text-gray-500">
            If you have any questions in the meantime, please contact us directly.
        </p>
    </div>
@endsection
