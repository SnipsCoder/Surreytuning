<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Surrey Tuning Services') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased min-h-screen bg-[#0f172a] flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <div class="mb-8 flex justify-center">
                @php $settings = \App\Models\Setting::first(); @endphp
                @if ($settings && $settings->logo_dark)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('r2')->url($settings->logo_dark) }}" alt="{{ config('app.name') }}" class="h-12 object-contain">
                @else
                    <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="h-12 object-contain" onerror="this.onerror=null;this.src='';this.alt='Surrey Tuning Services';">
                @endif
            </div>

            <div class="bg-[#1e293b] border border-gray-700 rounded-xl shadow-2xl p-8">
                {{ $slot ?? '' }}
                @yield('content')
            </div>
        </div>
    </body>
</html>
