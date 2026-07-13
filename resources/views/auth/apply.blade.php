@extends('layouts.auth')

@section('content')
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold text-white">Dealer Application</h1>
        <p class="mt-1 text-sm text-gray-400">Apply to become an authorised {{ \App\Models\Setting::brandName() }} dealer</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-md bg-red-900/40 border border-red-700 p-3 text-sm text-red-300 space-y-1">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('apply.store') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Company Name</label>
            <input type="text" name="company_name" value="{{ old('company_name') }}" required
                class="block w-full rounded-md border-gray-600 bg-gray-800 text-gray-100 text-sm shadow-sm focus:border-brand focus:ring-brand">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Contact Name</label>
            <input type="text" name="contact_name" value="{{ old('contact_name') }}" required
                class="block w-full rounded-md border-gray-600 bg-gray-800 text-gray-100 text-sm shadow-sm focus:border-brand focus:ring-brand">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Email Address</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                class="block w-full rounded-md border-gray-600 bg-gray-800 text-gray-100 text-sm shadow-sm focus:border-brand focus:ring-brand">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Phone Number</label>
            <input type="text" name="phone" value="{{ old('phone') }}"
                class="block w-full rounded-md border-gray-600 bg-gray-800 text-gray-100 text-sm shadow-sm focus:border-brand focus:ring-brand">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Country</label>
            <input type="text" name="country" value="{{ old('country', 'United Kingdom') }}" required
                class="block w-full rounded-md border-gray-600 bg-gray-800 text-gray-100 text-sm shadow-sm focus:border-brand focus:ring-brand">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Message (optional)</label>
            <textarea name="message" rows="3"
                class="block w-full rounded-md border-gray-600 bg-gray-800 text-gray-100 text-sm shadow-sm focus:border-brand focus:ring-brand">{{ old('message') }}</textarea>
        </div>

        @if ($terms)
            <div class="rounded-md bg-gray-800 border border-gray-600 p-3 max-h-36 overflow-y-auto text-xs text-gray-400 whitespace-pre-wrap">{{ $terms }}</div>
        @endif

        <label class="flex items-start gap-3">
            <input type="checkbox" name="terms_accepted" value="1" @checked(old('terms_accepted'))
                class="mt-0.5 rounded border-gray-600 text-brand focus:ring-brand">
            <span class="text-sm text-gray-300">I agree to the terms and conditions</span>
        </label>

        <button type="submit" class="w-full px-4 py-2 rounded-md bg-brand text-white text-sm font-medium hover:bg-[#c92a0f]">
            Submit Application
        </button>
    </form>
@endsection
