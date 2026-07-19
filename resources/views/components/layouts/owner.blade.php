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

        @php
            try {
                $settings = \App\Models\Setting::first();
            } catch (\Throwable $e) {
                $settings = null;
            }
            $brandName = \App\Models\Setting::brandName();
        @endphp
        <x-brand-styles />

        {{ $head ?? '' }}
    </head>
    <body class="font-sans antialiased bg-[#0f172a]">
        <div class="min-h-screen flex">
            <!-- Sidebar -->
            <aside class="hidden lg:flex lg:flex-col flex-shrink-0 w-64 bg-[#1e293b] border-r border-white/5">
                <div class="flex items-center h-16 px-5 bg-black border-b border-white/5 flex-shrink-0">
                    @if ($settings && $settings->logo_dark)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('r2')->url($settings->logo_dark) }}" alt="{{ $brandName }}" class="h-8 max-w-[148px] object-contain">
                    @else
                        <img src="{{ asset('images/logo.png') }}" alt="{{ $brandName }}" class="h-8 max-w-[148px] object-contain" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                        <span class="ms-3 text-white font-semibold hidden">{{ $brandName }}</span>
                    @endif
                </div>

                <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-6">
                    @php
                        $navSections = [
                            'OPERATIONS' => [
                                ['label' => 'Dashboard',           'route' => 'owner.dashboard',            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>'],
                                ['label' => 'File Requests',       'route' => 'file-requests.index',        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'],
                                ['label' => 'File Archive',        'route' => 'owner.file-requests.archive','icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>'],
                                ['label' => 'Dealers',             'route' => 'dealers.index',              'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>'],
                                ['label' => 'Dealer Applications', 'route' => 'dealer-applications.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>'],
                                ['label' => 'Invoices',            'route' => 'invoices.index',             'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21l-7-7-7 7V5a2 2 0 012-2h10a2 2 0 012 2v16z"/>'],
                                ['label' => 'Reports',             'route' => 'owner.reports.index',        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3v18h18M18.75 17V9.5M13.5 17V6.5M8.25 17v-3.5"/>'],
                            ],
                            'CONFIGURATION' => [
                                ['label' => 'WinOLS Bundles',      'route' => 'winols-bundles.index',       'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>'],
                                ['label' => 'File Stages',         'route' => 'file-stages.index',          'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/>'],
                                ['label' => 'File Options',        'route' => 'file-options.index',         'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>'],
                                ['label' => 'Tools',               'route' => 'tools.index',                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>'],
                                ['label' => 'Products',            'route' => 'products.index',             'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>'],
                                ['label' => 'Noticeboard',         'route' => 'noticeboards.index',         'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>'],
                                ['label' => "What's New",          'route' => 'whats-new.index',            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>'],
                            ],
                            'TOOLS & DATA' => [
                                ['label' => 'Vehicle Stats',       'route' => 'vehicle-stats.index',        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>'],
                                // Hidden until Bosch ECU data is loaded — keep route/controller/view intact.
                                // ['label' => 'Bosch ECU',           'route' => 'owner.bosch-ecu.index',      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>'],
                                ['label' => 'DTC Search',          'route' => 'owner.dtc-search.index',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>'],
                            ],
                            'ACCOUNT' => [
                                ['label' => 'Portal Users',        'route' => 'portal-users.index',         'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>'],
                                ['label' => 'Settings',            'route' => 'owner.settings.index',       'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>'],
                            ],
                        ];
                    @endphp

                    @foreach ($navSections as $sectionLabel => $links)
                        <div>
                            <p class="px-3 mb-1 text-[10px] font-semibold tracking-widest text-slate-600 uppercase">{{ $sectionLabel }}</p>
                            @foreach ($links as $link)
                                @php $isActive = Route::has($link['route']) && request()->routeIs($link['route'].'*'); @endphp
                                <a href="{{ Route::has($link['route']) ? route($link['route']) : '#' }}"
                                   class="flex items-center gap-2.5 px-3 py-2 text-sm font-medium rounded-lg mb-0.5 transition-colors
                                       {{ $isActive
                                           ? 'bg-brand/15 text-brand border border-brand/30'
                                           : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent' }}">
                                    <svg class="w-4 h-4 flex-shrink-0 {{ $isActive ? 'text-brand' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        {!! $link['icon'] !!}
                                    </svg>
                                    {{ $link['label'] }}
                                </a>
                            @endforeach
                        </div>
                    @endforeach
                </nav>
            </aside>

            <!-- Main column -->
            <div class="flex-1 flex flex-col min-w-0">
                <!-- Header -->
                <header class="h-16 flex items-center justify-between px-6 bg-[#1e293b] border-b border-white/5 flex-shrink-0" x-data="{ open: false }">
                    <!-- Left: portal status pill -->
                    <div class="flex items-center gap-4">
                        @php
                            try {
                                $portalStatus = \App\Models\PortalStatus::find(1);
                            } catch (\Throwable $e) {
                                $portalStatus = null;
                            }
                        @endphp
                        @if ($portalStatus)
                            <div class="relative">
                                <button type="button" x-on:click="open = !open" x-on:click.outside="open = false" class="focus:outline-none">
                                    <x-status-badge :status="$portalStatus->status->label()" :colour="$portalStatus->status->colour()" />
                                </button>
                                <div x-show="open" x-cloak
                                     class="absolute z-20 mt-2 w-44 rounded-lg shadow-xl bg-[#1e293b] border border-white/10 overflow-hidden">
                                    @foreach (\App\Enums\PortalStatusEnum::cases() as $case)
                                        <form method="POST" action="{{ route('owner.portal-status.update') }}">
                                            @csrf
                                            <input type="hidden" name="status" value="{{ $case->value }}">
                                            <button type="submit" class="block w-full text-left px-4 py-2.5 text-sm text-slate-300 hover:bg-white/5 hover:text-white transition-colors">
                                                {{ $case->label() }}
                                            </button>
                                        </form>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Right: notification bell + user + logout -->
                    <div class="flex items-center gap-4">
                        @php
                            try {
                                $pendingApplications = \App\Models\DealerApplication::where('status', 'pending')->count();
                            } catch (\Throwable $e) {
                                $pendingApplications = 0;
                            }
                        @endphp

                        <!-- Notification bell -->
                        <a href="{{ Route::has('dealer-applications.index') ? route('dealer-applications.index') : '#' }}"
                           class="relative p-1.5 text-slate-400 hover:text-white transition-colors rounded-lg hover:bg-white/5" title="Pending dealer applications">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            @if ($pendingApplications > 0)
                                <span class="absolute -top-0.5 -right-0.5 w-4 h-4 flex items-center justify-center bg-brand text-white text-[10px] font-bold rounded-full">
                                    {{ $pendingApplications > 9 ? '9+' : $pendingApplications }}
                                </span>
                            @endif
                        </a>

                        <div class="w-px h-6 bg-white/10"></div>

                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-semibold text-white leading-tight">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
                            <p class="text-xs text-slate-500 leading-tight">Owner</p>
                        </div>
                        @php
                            $initials = strtoupper(substr(auth()->user()->first_name ?? '', 0, 1) . substr(auth()->user()->last_name ?? '', 0, 1));
                        @endphp
                        <div class="relative">
                            <div class="w-9 h-9 rounded-full bg-brand/20 flex items-center justify-center text-sm font-bold text-brand">
                                {{ $initials }}
                            </div>
                            <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-400 border-2 border-[#1e293b] rounded-full"></span>
                        </div>
                        <div class="w-px h-6 bg-white/10"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" title="Log out"
                                class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </header>

                <main x-data="{}" class="flex-1 overflow-y-auto bg-[#0f172a] p-6 flex flex-col">
                    <x-flash-messages />
                    <div class="flex-1">
                        {{ $slot }}
                    </div>

                    <!-- Footer -->
                    <footer class="mt-8 pt-4 border-t border-white/5 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-600">
                        <p>&copy; {{ date('Y') }} {{ $brandName }}. All rights reserved.</p>
                        <p>Owner Portal v1.0</p>
                    </footer>
                </main>
            </div>
        </div>
    </body>
</html>
