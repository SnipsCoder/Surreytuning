<x-layouts.owner>
    <x-page-header title="Settings" subtitle="Manage portal configuration" />

    @if (session('success'))
        <div class="mb-6 rounded-md bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif

    <div x-data="{ tab: '{{ session('active_tab', 'account') }}' }">
        <div class="border-b border-gray-200 dark:border-[#2a2a2a] mb-6">
            <nav class="-mb-px flex space-x-6 overflow-x-auto">
                @foreach ([
                    'account' => 'Account',
                    'hours' => 'Opening Hours',
                    'branding' => 'Branding',
                    'dealer' => 'Dealer',
                    'invoice' => 'Invoice',
                    'terms' => 'T&Cs',
                ] as $key => $label)
                    <button
                        type="button"
                        x-on:click="tab = '{{ $key }}'"
                        :class="tab === '{{ $key }}' ? 'border-brand text-brand' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                        class="whitespace-nowrap border-b-2 py-3 px-1 text-sm font-medium"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </nav>
        </div>

        {{-- Account Tab --}}
        <div x-show="tab === 'account'" x-cloak>
            <form method="POST" action="{{ route('owner.settings.update') }}" class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow p-6 space-y-4">
                @csrf
                @method('PATCH')
                <input type="hidden" name="active_tab" value="account">

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Invoice Address</label>
                    <textarea name="invoice_address" rows="3" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm">{{ old('invoice_address', $settings->invoice_address) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Returns Address</label>
                    <textarea name="returns_address" rows="3" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm">{{ old('returns_address', $settings->returns_address) }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">VAT Number</label>
                        <input type="text" name="vat_number" value="{{ old('vat_number', $settings->vat_number) }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">VAT Rate (%)</label>
                        <input type="number" step="0.01" name="vat_rate" value="{{ old('vat_rate', $settings->vat_rate) }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company Number</label>
                        <input type="text" name="company_number" value="{{ old('company_number', $settings->company_number) }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">BCC Invoice Email</label>
                        <input type="email" name="bcc_invoice_email" value="{{ old('bcc_invoice_email', $settings->bcc_invoice_email) }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm">
                    </div>
                </div>

                <input type="hidden" name="dealer_auto_onboard" value="{{ $settings->dealer_auto_onboard ? 1 : 0 }}">
                <input type="hidden" name="invoice_start_number" value="{{ $settings->invoice_start_number }}">
                <input type="hidden" name="invoice_reference_prefix" value="{{ $settings->invoice_reference_prefix }}">
                <input type="hidden" name="terms_and_conditions" value="{{ $settings->terms_and_conditions }}">

                <div class="pt-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md bg-brand text-white text-sm font-medium hover:bg-[#c92a0f]">
                        Save Account Settings
                    </button>
                </div>
            </form>
        </div>

        {{-- Opening Hours Tab --}}
        <div x-show="tab === 'hours'" x-cloak>
            <form method="POST" action="{{ route('owner.settings.hours') }}" class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow p-6 space-y-4">
                @csrf
                @method('PATCH')

                @php
                    $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                @endphp

                @foreach ($openingHours as $i => $hour)
                    <div class="flex flex-wrap items-center gap-4 py-2 border-b border-gray-100 dark:border-[#2a2a2a] last:border-0">
                        <input type="hidden" name="hours[{{ $i }}][id]" value="{{ $hour->id }}">
                        <div class="w-28 text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ $days[$hour->day_of_week] ?? $hour->day_of_week }}
                        </div>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <input type="checkbox" name="hours[{{ $i }}][is_open]" value="1" {{ $hour->is_open ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 text-brand focus:ring-brand">
                            Open
                        </label>
                        <input type="time" name="hours[{{ $i }}][open_time]" value="{{ \Illuminate\Support\Carbon::parse($hour->open_time)->format('H:i') }}" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm">
                        <span class="text-gray-400">to</span>
                        <input type="time" name="hours[{{ $i }}][close_time]" value="{{ \Illuminate\Support\Carbon::parse($hour->close_time)->format('H:i') }}" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm">
                    </div>
                @endforeach

                <div class="pt-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md bg-brand text-white text-sm font-medium hover:bg-[#c92a0f]">
                        Save Opening Hours
                    </button>
                </div>
            </form>
        </div>

        {{-- Branding Tab --}}
        <div x-show="tab === 'branding'" x-cloak>
            <form method="POST" action="{{ route('owner.settings.branding') }}" enctype="multipart/form-data" class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow p-6 space-y-4">
                @csrf
                @method('PATCH')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Logo (Light)</label>
                        @if ($settings->logo_light)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Current: {{ $settings->logo_light }}</p>
                        @endif
                        <input type="file" name="logo_light" accept="image/*" class="w-full text-sm text-gray-700 dark:text-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Logo (Dark)</label>
                        @if ($settings->logo_dark)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Current: {{ $settings->logo_dark }}</p>
                        @endif
                        <input type="file" name="logo_dark" accept="image/*" class="w-full text-sm text-gray-700 dark:text-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Login Background</label>
                        @if ($settings->login_background)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Current: {{ $settings->login_background }}</p>
                        @endif
                        <input type="file" name="login_background" accept="image/*" class="w-full text-sm text-gray-700 dark:text-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Theme Colour</label>
                        <input type="color" name="theme_colour" value="{{ old('theme_colour', $settings->theme_colour) }}" class="h-10 w-20 rounded-md border-gray-300 dark:border-gray-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Brand Name</label>
                        <input type="text" name="brand_name" value="{{ old('brand_name', $settings->brand_name) }}" placeholder="Dealer Portal" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-[#0f0f0f] dark:text-gray-200 text-sm">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Shown across the portal, invoices and legal pages.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Support Email</label>
                        <input type="email" name="support_email" value="{{ old('support_email', $settings->support_email) }}" placeholder="support@example.com" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-[#0f0f0f] dark:text-gray-200 text-sm">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Used for the Contact Support link and legal contact details.</p>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md bg-brand text-white text-sm font-medium hover:bg-[#c92a0f]">
                        Save Branding
                    </button>
                </div>
            </form>
        </div>

        {{-- Dealer Tab --}}
        <div x-show="tab === 'dealer'" x-cloak>
            <form method="POST" action="{{ route('owner.settings.update') }}" class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow p-6 space-y-4">
                @csrf
                @method('PATCH')

                <input type="hidden" name="invoice_address" value="{{ $settings->invoice_address }}">
                <input type="hidden" name="returns_address" value="{{ $settings->returns_address }}">
                <input type="hidden" name="vat_number" value="{{ $settings->vat_number }}">
                <input type="hidden" name="vat_rate" value="{{ $settings->vat_rate }}">
                <input type="hidden" name="company_number" value="{{ $settings->company_number }}">
                <input type="hidden" name="bcc_invoice_email" value="{{ $settings->bcc_invoice_email }}">
                <input type="hidden" name="invoice_start_number" value="{{ $settings->invoice_start_number }}">
                <input type="hidden" name="invoice_reference_prefix" value="{{ $settings->invoice_reference_prefix }}">
                <input type="hidden" name="terms_and_conditions" value="{{ $settings->terms_and_conditions }}">
                <input type="hidden" name="active_tab" value="dealer">

                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <input type="checkbox" name="dealer_auto_onboard" value="1" {{ $settings->dealer_auto_onboard ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 text-brand focus:ring-brand">
                    Automatically onboard new dealer applications
                </label>

                <div class="pt-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md bg-brand text-white text-sm font-medium hover:bg-[#c92a0f]">
                        Save Dealer Settings
                    </button>
                </div>
            </form>
        </div>

        {{-- Invoice Tab --}}
        <div x-show="tab === 'invoice'" x-cloak>
            <form method="POST" action="{{ route('owner.settings.update') }}" class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow p-6 space-y-4">
                @csrf
                @method('PATCH')

                <input type="hidden" name="invoice_address" value="{{ $settings->invoice_address }}">
                <input type="hidden" name="returns_address" value="{{ $settings->returns_address }}">
                <input type="hidden" name="vat_number" value="{{ $settings->vat_number }}">
                <input type="hidden" name="vat_rate" value="{{ $settings->vat_rate }}">
                <input type="hidden" name="company_number" value="{{ $settings->company_number }}">
                <input type="hidden" name="dealer_auto_onboard" value="{{ $settings->dealer_auto_onboard ? 1 : 0 }}">
                <input type="hidden" name="terms_and_conditions" value="{{ $settings->terms_and_conditions }}">
                <input type="hidden" name="active_tab" value="invoice">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Invoice Start Number</label>
                        <input type="number" name="invoice_start_number" value="{{ old('invoice_start_number', $settings->invoice_start_number) }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Invoice Reference Prefix</label>
                        <input type="text" name="invoice_reference_prefix" value="{{ old('invoice_reference_prefix', $settings->invoice_reference_prefix) }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">BCC Invoice Email</label>
                        <input type="email" name="bcc_invoice_email" value="{{ old('bcc_invoice_email', $settings->bcc_invoice_email) }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md bg-brand text-white text-sm font-medium hover:bg-[#c92a0f]">
                        Save Invoice Settings
                    </button>
                </div>
            </form>
        </div>

        {{-- T&Cs Tab --}}
        <div x-show="tab === 'terms'" x-cloak>
            <form method="POST" action="{{ route('owner.settings.update') }}" class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow p-6 space-y-4">
                @csrf
                @method('PATCH')

                <input type="hidden" name="invoice_address" value="{{ $settings->invoice_address }}">
                <input type="hidden" name="returns_address" value="{{ $settings->returns_address }}">
                <input type="hidden" name="vat_number" value="{{ $settings->vat_number }}">
                <input type="hidden" name="vat_rate" value="{{ $settings->vat_rate }}">
                <input type="hidden" name="company_number" value="{{ $settings->company_number }}">
                <input type="hidden" name="bcc_invoice_email" value="{{ $settings->bcc_invoice_email }}">
                <input type="hidden" name="dealer_auto_onboard" value="{{ $settings->dealer_auto_onboard ? 1 : 0 }}">
                <input type="hidden" name="invoice_start_number" value="{{ $settings->invoice_start_number }}">
                <input type="hidden" name="invoice_reference_prefix" value="{{ $settings->invoice_reference_prefix }}">
                <input type="hidden" name="active_tab" value="terms">

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Terms &amp; Conditions</label>
                    <textarea name="terms_and_conditions" rows="12" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm">{{ old('terms_and_conditions', $settings->terms_and_conditions) }}</textarea>
                </div>

                <div class="pt-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md bg-brand text-white text-sm font-medium hover:bg-[#c92a0f]">
                        Save Terms &amp; Conditions
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.owner>
