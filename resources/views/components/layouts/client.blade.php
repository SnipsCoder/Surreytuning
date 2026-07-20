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
                <!-- Logo -->
                <div class="flex items-center h-16 px-5 bg-black border-b border-white/5 flex-shrink-0">
                    @if ($settings && ($settings->portal_logo || $settings->logo_dark || $settings->logo_light))
                        <img src="{{ route('branding.logo') }}" alt="{{ $brandName }}" class="h-8 max-w-[160px] object-contain">
                    @else
                        <span class="text-white font-semibold text-lg tracking-tight">{{ $brandName }}</span>
                    @endif
                </div>

                <nav class="flex-1 overflow-y-auto px-3 py-4">
                    @php
                        $navSections = [
                            'FILE SERVICE' => [
                                [
                                    'label' => 'Dashboard',
                                    'route' => 'client.dashboard',
                                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
                                ],
                                [
                                    'label' => 'File Requests',
                                    'route' => 'client.file-requests.index',
                                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
                                ],
                                [
                                    'label' => 'Upload File',
                                    'route' => 'client.upload.create',
                                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>',
                                ],
                                [
                                    'label' => 'File Archive',
                                    'route' => 'client.file-requests.archive',
                                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>',
                                ],
                            ],
                            'FINANCIAL' => [
                                [
                                    'label' => 'File Credits',
                                    'route' => 'client.credits.file',
                                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                                ],
                                [
                                    'label' => 'EVC Credits',
                                    'route' => 'client.credits.evc',
                                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>',
                                ],
                                [
                                    'label' => 'Products',
                                    'route' => 'client.products.index',
                                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>',
                                ],
                                [
                                    'label' => 'Invoices',
                                    'route' => 'client.invoices.index',
                                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>',
                                ],
                            ],
                            'TOOLS & DATA' => [
                                [
                                    'label' => 'DTC Search',
                                    'route' => 'client.dtc-search.index',
                                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>',
                                ],
                                [
                                    'label' => 'Vehicle Stats',
                                    'route' => 'client.vehicle-stats.index',
                                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
                                ],
                                // Hidden until Bosch ECU data is loaded — keep route/controller/view intact.
                                // [
                                //     'label' => 'Bosch ECU',
                                //     'route' => 'client.bosch-ecu.index',
                                //     'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>',
                                // ],
                                [
                                    'label' => "What's New",
                                    'route' => 'client.whats-new.index',
                                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>',
                                ],
                            ],
                            'ACCOUNT' => [
                                [
                                    'label' => 'Portal Users',
                                    'route' => 'client.portal-users.index',
                                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
                                ],
                                [
                                    'label' => 'Settings',
                                    'route' => 'client.settings.index',
                                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
                                ],
                            ],
                        ];
                    @endphp

                    @foreach ($navSections as $sectionLabel => $links)
                        <div class="{{ !$loop->first ? 'mt-6' : '' }}">
                            <p class="px-3 mb-1 text-[10px] font-semibold tracking-widest text-slate-600 uppercase">{{ $sectionLabel }}</p>
                            @foreach ($links as $link)
                                @php $isActive = Route::has($link['route']) && request()->routeIs($link['route'].'*'); @endphp
                                <a
                                    href="{{ Route::has($link['route']) ? route($link['route']) : '#' }}"
                                    class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg mb-0.5 transition-colors border
                                        {{ $isActive
                                            ? 'text-white bg-brand/15 border-brand/30'
                                            : 'text-slate-400 hover:text-white hover:bg-white/5 border-transparent' }}"
                                >
                                    <svg class="w-4 h-4 flex-shrink-0 {{ $isActive ? 'text-brand' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        {!! $link['icon'] !!}
                                    </svg>
                                    {{ $link['label'] }}
                                </a>
                            @endforeach
                        </div>
                    @endforeach
                </nav>

                <!-- Need Help? card pinned to sidebar bottom -->
                <div class="p-3 flex-shrink-0">
                    <div class="bg-[#0f172a] border border-white/5 rounded-xl p-4">
                        <div class="flex items-center gap-2 mb-1">
                            <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <span class="text-sm font-semibold text-white">Need Help?</span>
                        </div>
                        <p class="text-xs text-slate-500 mb-3">Contact our support team for assistance with your account.</p>
                        @php $supportEmail = \App\Models\Setting::supportEmail(); @endphp
                        @if ($supportEmail)
                            <a href="mailto:{{ $supportEmail }}"
                               class="block w-full text-center px-3 py-1.5 bg-brand hover:bg-brand-dark text-white text-xs font-semibold rounded-lg transition-colors">
                                Contact Support
                            </a>
                        @endif
                    </div>
                </div>
            </aside>

            <!-- Main column -->
            <div class="flex-1 flex flex-col min-w-0">
                <!-- Header -->
                <header class="h-16 flex items-center justify-between px-6 bg-[#1e293b] border-b border-white/5 flex-shrink-0">
                    @php $dealer = auth()->user()->dealer ?? null; @endphp

                    <!-- Left spacer -->
                    <div class="flex items-center gap-4"></div>

                    <!-- Right: credit pills + notification bell + user avatar -->
                    <div class="flex items-center gap-4">
                        @if ($dealer)
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <span class="inline-flex items-center gap-2 pl-2.5 pr-3 py-1.5 rounded-lg whitespace-nowrap flex-shrink-0 bg-white/5 border border-white/10">
                                    <span class="w-1.5 h-1.5 rounded-full bg-sky-400 flex-shrink-0"></span>
                                    <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">File</span>
                                    <span class="tabular-nums text-sm font-bold text-white">{{ number_format($dealer->file_credit_balance ?? 0) }}</span>
                                </span>
                                <span class="inline-flex items-center gap-2 pl-2.5 pr-3 py-1.5 rounded-lg whitespace-nowrap flex-shrink-0 bg-white/5 border border-white/10">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 flex-shrink-0"></span>
                                    <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">EVC</span>
                                    <span class="tabular-nums text-sm font-bold text-white">{{ number_format($dealer->evc_credit_balance ?? 0) }}</span>
                                </span>
                            </div>
                            <div class="w-px h-6 bg-white/10"></div>
                        @endif

                        @php $unreadNoticeCount = \App\Models\Noticeboard::active()->count(); @endphp

                        <!-- Notification bell -->
                        <button type="button" class="relative p-1.5 text-slate-400 hover:text-white transition-colors rounded-lg hover:bg-white/5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            @if ($unreadNoticeCount > 0)
                                <span class="absolute -top-0.5 -right-0.5 w-4 h-4 flex items-center justify-center bg-brand text-white text-[10px] font-bold rounded-full">
                                    {{ $unreadNoticeCount > 9 ? '9+' : $unreadNoticeCount }}
                                </span>
                            @endif
                        </button>

                        <div class="w-px h-6 bg-white/10"></div>

                        <!-- User avatar + name/company -->
                        <div class="flex items-center gap-3">
                            <div class="text-right hidden sm:block">
                                <p class="text-sm font-medium text-white leading-tight">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
                                @if ($dealer)
                                    <p class="text-xs text-slate-500 leading-tight">{{ $dealer->company_name }}</p>
                                @endif
                            </div>
                            <div class="relative flex-shrink-0">
                                <div class="w-8 h-8 rounded-full bg-brand/20 border border-brand/30 flex items-center justify-center">
                                    <span class="text-xs font-bold text-brand">
                                        {{ strtoupper(substr(auth()->user()->first_name ?? 'U', 0, 1)) }}{{ strtoupper(substr(auth()->user()->last_name ?? '', 0, 1)) }}
                                    </span>
                                </div>
                                <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-green-500 border-2 border-[#1e293b] rounded-full"></span>
                            </div>

                            <!-- Logout -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-white transition-colors rounded-lg hover:bg-white/5" title="Log Out">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </header>

                @php
                    try {
                        $portalStatus = \App\Models\PortalStatus::find(1);
                    } catch (\Throwable $e) {
                        $portalStatus = null;
                    }
                    $statusValue  = $portalStatus?->status?->value ?? 'available';
                    $bannerClass  = match($statusValue) {
                        'busy', 'delayed'            => 'bg-amber-900/40 border-amber-700 text-amber-300',
                        'closed'                     => 'bg-red-900/40 border-red-700 text-red-300',
                        'support_only', 'files_only' => 'bg-blue-900/40 border-blue-700 text-blue-300',
                        default                      => null,
                    };
                @endphp
                @if ($bannerClass)
                    <div class="border-b px-6 py-3 text-sm font-medium {{ $bannerClass }}">
                        The portal is currently {{ $portalStatus->status->label() }}. Some services may be limited.
                    </div>
                @endif

                <main x-data="{}" class="flex-1 overflow-y-auto bg-[#0f172a] p-6 flex flex-col">
                    <x-flash-messages />
                    <div class="flex-1">
                        {{ $slot }}
                    </div>

                    <!-- Footer -->
                    <footer class="mt-8 pt-4 border-t border-white/5 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-600">
                        <p>&copy; {{ date('Y') }} Tuning Portal. All rights reserved.</p>
                        <p>Dealer Portal v1.0</p>
                    </footer>
                </main>
            </div>
        </div>
    </body>
</html>
