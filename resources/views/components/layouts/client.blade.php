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
                            'FILE SERVICE' => [
                                ['label' => 'Dashboard',     'route' => 'client.dashboard'],
                                ['label' => 'File Requests', 'route' => 'client.file-requests.index'],
                                ['label' => 'Upload File',   'route' => 'client.upload.create'],
                                ['label' => 'File Archive',  'route' => 'client.file-requests.archive'],
                            ],
                            'FINANCIAL' => [
                                ['label' => 'Slave Credits', 'route' => 'client.credits.slave'],
                                ['label' => 'EVC Credits',   'route' => 'client.credits.evc'],
                                ['label' => 'Products',      'route' => 'client.products.index'],
                                ['label' => 'Invoices',      'route' => 'client.invoices.index'],
                            ],
                            'TOOLS & DATA' => [
                                ['label' => 'DTC Search',    'route' => 'client.dtc-search.index'],
                                ['label' => 'Vehicle Stats', 'route' => 'client.vehicle-stats.index'],
                                ['label' => 'Bosch ECU',     'route' => 'client.bosch-ecu.index'],
                                ["label" => "What's New",    'route' => 'client.whats-new.index'],
                            ],
                            'ACCOUNT' => [
                                ['label' => 'Portal Users',  'route' => 'client.portal-users.index'],
                                ['label' => 'Settings',      'route' => 'client.settings.index'],
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
                <header class="h-16 flex items-center justify-between px-6 bg-[#1e293b] border-b border-white/10 flex-shrink-0">
                    <div class="flex items-center gap-3">
                        @php $dealer = auth()->user()->dealer ?? null; @endphp
                        @if ($dealer)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-700 text-slate-200 border border-slate-600">
                                Slave: {{ number_format($dealer->slave_credit_balance ?? 0) }}
                            </span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-700 text-slate-200 border border-slate-600">
                                EVC: {{ number_format($dealer->evc_credit_balance ?? 0) }}
                            </span>
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

                @php
                    $portalStatus = \App\Models\PortalStatus::find(1);
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

                <main class="flex-1 overflow-y-auto bg-[#0f172a] p-6">
                    <x-flash-messages />
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
