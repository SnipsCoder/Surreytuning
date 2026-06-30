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

        @php $settings = \App\Models\Setting::first(); @endphp
        <style>:root { --brand: {{ $settings->theme_colour ?? '#e63012' }}; }</style>

        {{ $head ?? '' }}
    </head>
    <body class="font-sans antialiased bg-[#0f172a]">
        <div class="min-h-screen flex">
            <!-- Sidebar -->
            <aside class="hidden lg:flex lg:flex-col flex-shrink-0 w-64 bg-[#1e293b]">
                <div class="flex items-center h-16 px-6 border-b border-white/10">
                    @if ($settings && $settings->logo_dark)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('r2')->url($settings->logo_dark) }}" alt="{{ config('app.name') }}" class="h-8 max-w-[160px] object-contain">
                    @else
                        <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="h-8 max-w-[160px] object-contain" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                        <span class="ms-3 text-white font-semibold hidden">Surrey Tuning</span>
                    @endif
                </div>

                <nav class="flex-1 overflow-y-auto px-3 py-4">
                    @php
                        $navSections = [
                            'OPERATIONS' => [
                                ['label' => 'Dashboard',            'route' => 'owner.dashboard'],
                                ['label' => 'File Requests',        'route' => 'file-requests.index'],
                                ['label' => 'File Archive',         'route' => 'owner.file-requests.archive'],
                                ['label' => 'Dealers',              'route' => 'dealers.index'],
                                ['label' => 'Dealer Applications',  'route' => 'dealer-applications.index'],
                                ['label' => 'Invoices',             'route' => 'invoices.index'],
                            ],
                            'CONFIGURATION' => [
                                ['label' => 'WinOLS Bundles',       'route' => 'winols-bundles.index'],
                                ['label' => 'File Stages',          'route' => 'file-stages.index'],
                                ['label' => 'File Options',         'route' => 'file-options.index'],
                                ['label' => 'Tools',                'route' => 'tools.index'],
                                ['label' => 'Products',             'route' => 'products.index'],
                                ['label' => 'Noticeboard',          'route' => 'noticeboards.index'],
                                ["label" => "What's New",           'route' => 'whats-new.index'],
                            ],
                            'TOOLS & DATA' => [
                                ['label' => 'Vehicle Stats',        'route' => 'vehicle-stats.index'],
                                ['label' => 'Bosch ECU',            'route' => 'owner.bosch-ecu.index'],
                                ['label' => 'DTC Search',           'route' => 'owner.dtc-search.index'],
                            ],
                            'ACCOUNT' => [
                                ['label' => 'Portal Users',         'route' => 'portal-users.index'],
                                ['label' => 'Settings',             'route' => 'owner.settings.index'],
                            ],
                        ];
                    @endphp

                    @foreach ($navSections as $sectionLabel => $links)
                        <div class="{{ !$loop->first ? 'mt-6' : '' }}">
                            <p class="px-3 mb-1 text-[10px] font-semibold tracking-widest text-slate-500 uppercase">{{ $sectionLabel }}</p>
                            @foreach ($links as $link)
                                @php $isActive = Route::has($link['route']) && request()->routeIs($link['route'].'*'); @endphp
                                <a
                                    href="{{ Route::has($link['route']) ? route($link['route']) : '#' }}"
                                    class="flex items-center px-3 py-2 text-sm font-medium rounded-md mb-0.5 transition-colors
                                        {{ $isActive
                                            ? 'border-l-2 border-[#e63012] text-[#e63012] bg-[#e63012]/10 rounded-l-none pl-[10px]'
                                            : 'text-slate-400 hover:text-white hover:bg-white/5' }}"
                                >
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
                <header class="h-16 flex items-center justify-between px-6 bg-[#1e293b] border-b border-white/10 flex-shrink-0" x-data="{ open: false }">
                    <div class="flex items-center gap-3">
                        @php $portalStatus = \App\Models\PortalStatus::find(1); @endphp
                        @if ($portalStatus)
                            <div class="relative">
                                <button type="button" x-on:click="open = !open" x-on:click.outside="open = false" class="focus:outline-none">
                                    <x-status-badge :status="$portalStatus->status->label()" :colour="$portalStatus->status->colour()" />
                                </button>
                                <div x-show="open" x-cloak class="absolute z-20 mt-2 w-48 rounded-lg shadow-xl bg-[#1e293b] border border-white/10 overflow-hidden">
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

                    <div class="flex items-center gap-4">
                        <span class="text-sm font-medium text-slate-300">
                            {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
                        </span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-sm text-slate-400 hover:text-white transition-colors">
                                Log Out
                            </button>
                        </form>
                    </div>
                </header>

                <main class="flex-1 overflow-y-auto bg-[#0f172a] p-6">
                    <x-flash-messages />
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
