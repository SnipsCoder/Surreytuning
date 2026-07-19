<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ \App\Models\Setting::brandName() }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-brand-styles />
    @stack('head')
</head>
<body class="bg-[#0f172a] text-white antialiased font-sans">

@php
    use App\Models\Setting;
    use App\Models\FileRequest;
    $settings = Setting::first();
    $brandName = Setting::brandName();
    $activeFileRequestCount = FileRequest::where('dealer_id', auth()->user()->dealer_id)
        ->whereNotIn('status', ['closed', 'void'])
        ->count();
    $navHelper = function (string $route, string $match = null): string {
        if (! Route::has($route)) return '#';
        $active = request()->routeIs(($match ?? $route) . '*');
        return $active
            ? 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium bg-brand/15 text-brand border border-brand/30 transition-colors'
            : 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:bg-white/5 hover:text-white transition-colors';
    };
    $navUrl = function (string $route): string {
        return Route::has($route) ? route($route) : '#';
    };
@endphp

<!-- Sidebar -->
<aside class="fixed inset-y-0 left-0 w-64 bg-[#111827] flex flex-col z-50">

    <!-- Logo -->
    <div class="flex items-center h-16 px-5 border-b border-white/5 flex-shrink-0">
        @if ($settings && $settings->logo_dark)
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('r2')->url($settings->logo_dark) }}" alt="{{ $brandName }}" class="h-8 w-auto">
        @else
            <span class="text-white font-semibold text-lg tracking-tight">{{ $brandName }}</span>
        @endif
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-0.5">

        <!-- Dashboard -->
        <a href="{{ $navUrl('client.dashboard') }}" class="{{ $navHelper('client.dashboard') }}">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
        </a>

        <!-- FILE SERVICE -->
        <p class="px-3 pt-5 pb-1.5 text-[10px] font-semibold text-slate-600 uppercase tracking-widest">File Service</p>

        <a href="{{ $navUrl('client.file-requests.index') }}" class="{{ $navHelper('client.file-requests.index', 'client.file-requests') }}">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span class="flex-1">File Requests</span>
            @if ($activeFileRequestCount > 0)
                <span class="text-[10px] font-bold bg-brand text-white rounded-full px-1.5 py-0.5 leading-none">
                    {{ $activeFileRequestCount }}
                </span>
            @endif
        </a>

        <a href="{{ $navUrl('client.upload.create') }}" class="{{ $navHelper('client.upload.create') }}">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            Upload File
        </a>

        <a href="{{ $navUrl('client.file-requests.archive') }}" class="{{ $navHelper('client.file-requests.archive') }}">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
            </svg>
            File Archive
        </a>

        <!-- FINANCIAL -->
        <p class="px-3 pt-5 pb-1.5 text-[10px] font-semibold text-slate-600 uppercase tracking-widest">Financial</p>

        <a href="{{ $navUrl('client.credits.file') }}" class="{{ $navHelper('client.credits.file') }}">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            File Credits
        </a>

        <a href="{{ $navUrl('client.credits.evc') }}" class="{{ $navHelper('client.credits.evc') }}">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            EVC Credits
        </a>

        <a href="{{ $navUrl('client.products.index') }}" class="{{ $navHelper('client.products.index') }}">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            Products
        </a>

        <a href="{{ $navUrl('client.invoices.index') }}" class="{{ $navHelper('client.invoices.index') }}">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
            </svg>
            Invoices
        </a>

        <!-- TOOLS & DATA -->
        <p class="px-3 pt-5 pb-1.5 text-[10px] font-semibold text-slate-600 uppercase tracking-widest">Tools &amp; Data</p>

        <a href="{{ $navUrl('client.dtc-search.index') }}" class="{{ $navHelper('client.dtc-search.index') }}">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            DTC Search
        </a>

        <a href="{{ $navUrl('client.vehicle-stats.index') }}" class="{{ $navHelper('client.vehicle-stats.index') }}">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Vehicle Stats
        </a>

        {{-- Hidden until Bosch ECU data is loaded — keep route/controller/view intact.
        <a href="{{ $navUrl('client.bosch-ecu.index') }}" class="{{ $navHelper('client.bosch-ecu.index') }}">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>
            </svg>
            Bosch ECU
        </a>
        --}}

        <!-- ACCOUNT -->
        <p class="px-3 pt-5 pb-1.5 text-[10px] font-semibold text-slate-600 uppercase tracking-widest">Account</p>

        <a href="{{ $navUrl('client.portal-users.index') }}" class="{{ $navHelper('client.portal-users.index') }}">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            Portal Users
        </a>

        <a href="{{ $navUrl('client.settings.index') }}" class="{{ $navHelper('client.settings.index') }}">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Settings
        </a>

        <a href="{{ $navUrl('client.whats-new.index') }}" class="{{ $navHelper('client.whats-new.index') }}">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
            </svg>
            What's New
        </a>

    </nav>

    <!-- Need Help Widget -->
    @if ($settings?->whatsapp_business_number)
        <div class="flex-shrink-0 px-3 pb-4">
            <div class="bg-[#0f172a] rounded-xl p-4">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 rounded-full bg-green-500/10 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-white">Need Help?</p>
                        <p class="text-[10px] text-slate-500">Chat with us on WhatsApp</p>
                    </div>
                </div>
                <a href="https://wa.me/{{ preg_replace('/\D/', '', $settings->whatsapp_business_number) }}"
                   target="_blank" rel="noopener noreferrer"
                   class="flex items-center justify-center gap-2 w-full py-2 bg-green-500 hover:bg-green-400 text-white text-xs font-semibold rounded-lg transition-colors">
                    Start Chat
                </a>
            </div>
        </div>
    @endif

</aside>

<!-- Header -->
<header class="fixed top-0 left-64 right-0 h-16 bg-[#0f172a] border-b border-gray-800 z-40 flex items-center justify-between px-6">
    <div></div>
    <div class="flex items-center gap-4">

        <!-- Notification bell -->
        <button type="button" class="text-slate-500 hover:text-white transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
        </button>

        <div class="w-px h-6 bg-white/10"></div>

        <!-- Avatar + name + company -->
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-brand flex items-center justify-center text-xs font-bold text-white flex-shrink-0">
                {{ strtoupper(substr(auth()->user()->first_name ?? '', 0, 1)) }}{{ strtoupper(substr(auth()->user()->last_name ?? '', 0, 1)) }}
            </div>
            <div class="hidden sm:block">
                <p class="text-sm font-semibold text-white leading-tight">
                    {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
                </p>
                @if (auth()->user()->dealer?->company_name)
                    <p class="text-xs text-slate-500 leading-tight">{{ auth()->user()->dealer->company_name }}</p>
                @endif
            </div>
        </div>

        <!-- Logout -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" title="Sign out" class="text-slate-500 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </button>
        </form>

    </div>
</header>

<!-- Main content -->
<main class="ml-64 pt-16 min-h-screen bg-[#0f172a]">
    <x-flash-messages />
    @if (session('recovery_codes'))
        <div class="px-6 pt-6">
            <div class="rounded-xl border border-amber-500/40 bg-amber-500/10 p-4 text-amber-100">
                <h2 class="text-sm font-semibold">Save your two-factor recovery codes</h2>
                <p class="mt-1 text-sm text-amber-200/90">Store these somewhere safe. Each code can be used once to sign in if you lose access to your authenticator or email. They will not be shown again.</p>
                <ul class="mt-3 grid grid-cols-2 gap-2 font-mono text-sm sm:grid-cols-4">
                    @foreach (session('recovery_codes') as $recoveryCode)
                        <li class="rounded bg-black/30 px-2 py-1">{{ $recoveryCode }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
    @yield('content')
</main>

@stack('scripts')
</body>
</html>
