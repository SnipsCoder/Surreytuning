<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ \App\Models\Setting::brandName() }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <x-brand-styles />
    </head>
    @php $settings = \App\Models\Setting::first(); $brandName = \App\Models\Setting::brandName(); @endphp
    <body class="font-sans antialiased min-h-screen bg-[#0f172a] flex items-center justify-center p-4"
        @if ($settings && $settings->login_background)
            style="background-image: linear-gradient(rgba(15,23,42,0.4), rgba(15,23,42,0.4)), url('{{ route('branding.login-background') }}'); background-size: cover; background-position: center; background-attachment: fixed;"
        @endif>
        <div class="w-full max-w-md">
            <div class="bg-[#1e293b] border border-gray-700 rounded-xl shadow-2xl overflow-hidden">
                <div class="bg-black px-6 py-5 flex items-center justify-center">
                    @if ($settings && ($settings->portal_logo || $settings->logo_dark))
                        <img src="{{ route('branding.logo') }}" alt="{{ $brandName }}" class="h-12 object-contain">
                    @else
                        <span class="text-2xl font-bold text-white">{{ $brandName }}</span>
                    @endif
                </div>
                <div class="p-8">
                    {{ $slot ?? '' }}
                    @yield('content')
                </div>
            </div>
        </div>
    </body>
</html>
