<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ dark: localStorage.getItem('dark') === 'true' }" x-init="$watch('dark', value => localStorage.setItem('dark', value))" :class="{ 'dark': dark }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Surrey Tuning Services') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased min-h-screen bg-gray-900 flex items-center justify-center">
        <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-8">
            <div class="text-center mb-6">
                <span class="text-2xl font-bold" style="color: #e63012;">Surrey Tuning Services</span>
            </div>

            {{ $slot ?? '' }}
            @yield('content')
        </div>
    </body>
</html>
