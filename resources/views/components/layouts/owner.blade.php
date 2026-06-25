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
    <body class="font-sans antialiased">
        <div class="min-h-screen flex bg-gray-50 dark:bg-gray-900">
            <!-- Sidebar -->
            <aside class="hidden lg:flex lg:flex-col flex-shrink-0" style="width: 256px; background-color: #1e293b;">
                <div class="flex items-center h-16 px-6 border-b border-white/10">
                    <x-application-logo class="w-8 h-8 fill-current text-white" />
                    <span class="ms-3 text-white font-semibold">Surrey Tuning</span>
                </div>

                <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
                    @php
                        $ownerNavLinks = [
                            ['label' => 'Dashboard', 'route' => 'owner.dashboard'],
                            ['label' => 'File Requests', 'route' => 'file-requests.index'],
                            ['label' => 'File Archive', 'route' => 'owner.file-requests.archive'],
                            ['label' => 'Dealers', 'route' => 'dealers.index'],
                            ['label' => 'Dealer Applications', 'route' => 'dealer-applications.index'],
                            ['label' => 'Invoices', 'route' => 'invoices.index'],
                            ['label' => 'WinOLS Bundles', 'route' => 'winols-bundles.index'],
                            ['label' => 'File Stages', 'route' => 'file-stages.index'],
                            ['label' => 'File Options', 'route' => 'file-options.index'],
                            ['label' => 'Tools', 'route' => 'tools.index'],
                            ['label' => 'Portal Users', 'route' => 'portal-users.index'],
                            ['label' => 'Noticeboard', 'route' => 'noticeboards.index'],
                            ['label' => 'Products', 'route' => 'products.index'],
                            ['label' => 'Vehicle Stats', 'route' => 'vehicle-stats.index'],
                            ['label' => 'Bosch ECU', 'route' => 'owner.bosch-ecu.index'],
                            ['label' => 'DTC Search', 'route' => 'owner.dtc-search.index'],
                            ["label" => "What's New", 'route' => 'whats-new.index'],
                            ['label' => 'Settings', 'route' => 'owner.settings.index'],
                        ];
                    @endphp

                    @foreach ($ownerNavLinks as $link)
                        @php $isActive = Route::has($link['route']) && request()->routeIs($link['route'].'*'); @endphp
                        <a
                            href="{{ Route::has($link['route']) ? route($link['route']) : '#' }}"
                            class="block px-3 py-2 rounded-md text-sm font-medium {{ $isActive ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}"
                        >
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                </nav>
            </aside>

            <div class="flex-1 flex flex-col min-w-0">
                <!-- Header -->
                <header class="h-16 flex items-center justify-between px-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-3" x-data="{ open: false }">
                        @php
                            $portalStatus = \Illuminate\Support\Facades\Cache::remember('portal_status', 300, fn () => \App\Models\PortalStatus::current());
                        @endphp
                        @if ($portalStatus)
                            <div class="relative">
                                <button type="button" x-on:click="open = !open" x-on:click.outside="open = false">
                                    <x-status-badge :status="$portalStatus->status->label()" :colour="$portalStatus->status->colour()" />
                                </button>
                                <div x-show="open" x-cloak class="absolute z-10 mt-2 w-44 rounded-md shadow-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                                    @foreach (\App\Enums\PortalStatusEnum::cases() as $case)
                                        <form method="POST" action="{{ route('owner.portal-status.update') }}">
                                            @csrf
                                            <input type="hidden" name="status" value="{{ $case->value }}">
                                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                {{ $case->label() }}
                                            </button>
                                        </form>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center gap-4">
                        <button type="button" x-on:click="dark = !dark" class="text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-white">
                            <span x-show="!dark">🌙</span>
                            <span x-show="dark" x-cloak>☀️</span>
                        </button>

                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
                            {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
                        </span>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-sm text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-white">
                                {{ __('Log Out') }}
                            </button>
                        </form>
                    </div>
                </header>

                <main class="flex-1 overflow-y-auto bg-gray-50 dark:bg-gray-900 p-6">
                    <x-flash-messages />

                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
