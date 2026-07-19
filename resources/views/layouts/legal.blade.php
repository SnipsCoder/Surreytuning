<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ ($title ?? '') ? $title.' — ' : '' }}{{ \App\Models\Setting::brandName() }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <x-brand-styles />
    </head>
    <body class="font-sans antialiased min-h-screen bg-[#0f172a] py-10 px-4">
        <div class="mx-auto w-full max-w-3xl">
            <div class="mb-8 flex justify-center">
                @php $settings = \App\Models\Setting::first(); $brandName = \App\Models\Setting::brandName(); @endphp
                @if ($settings && $settings->logo_dark)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('r2')->url($settings->logo_dark) }}" alt="{{ $brandName }}" class="h-12 object-contain">
                @else
                        <span class="text-2xl font-bold text-white">{{ $brandName }}</span>
                @endif
            </div>

            <div class="bg-[#1e293b] border border-gray-700 rounded-xl shadow-2xl p-8">
                <h1 class="text-2xl font-bold text-white">@yield('heading')</h1>
                <div class="mt-6 space-y-4 text-sm text-gray-300 leading-relaxed">
                    @yield('content')
                </div>
            </div>

            <p class="mt-6 text-center text-xs text-gray-500">
                &copy; {{ now()->year }} {{ $brandName }}.
                <a href="{{ route('legal.terms') }}" class="text-gray-400 hover:text-white underline">Terms</a>
                &middot;
                <a href="{{ route('legal.privacy') }}" class="text-gray-400 hover:text-white underline">Privacy</a>
            </p>
        </div>
    </body>
</html>
