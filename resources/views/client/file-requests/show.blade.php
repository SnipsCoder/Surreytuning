<x-layouts.client>
    <x-page-header :title="$fileRequest->request_number_formatted" :subtitle="$fileRequest->make.' '.$fileRequest->model.' ('.$fileRequest->year.')'">
        <x-status-badge :status="$fileRequest->status->label()" :colour="$fileRequest->status->colour()" />
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- LEFT: VEHICLE DETAILS --}}
        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Vehicle Details</h3>
                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Make / Model</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $fileRequest->make }} {{ $fileRequest->model }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Year</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $fileRequest->year }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Registration</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $fileRequest->registration ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">VIN</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $fileRequest->vin_number ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Engine</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $fileRequest->engine }} {{ $fileRequest->engine_code ? '('.$fileRequest->engine_code.')' : '' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Fuel / Transmission</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $fileRequest->fuel->label() }} / {{ $fileRequest->transmission->label() }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">BHP Before</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $fileRequest->bhp_before ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Submitted</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $fileRequest->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
                @if ($fileRequest->client_notes)
                    <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                        <dt class="text-gray-500 dark:text-gray-400 text-sm">Notes</dt>
                        <dd class="text-gray-900 dark:text-gray-100 text-sm mt-1">{{ $fileRequest->client_notes }}</dd>
                    </div>
                @endif
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Service Selection</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500 dark:text-gray-400">File Stage</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $fileRequest->fileStage->name ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500 dark:text-gray-400">Tuning Tool</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $fileRequest->tool->name ?? '-' }}</dd>
                    </div>
                </dl>
                @if ($fileRequest->fileRequestOptions->isNotEmpty())
                    <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700 space-y-2">
                        @foreach ($fileRequest->fileRequestOptions as $requestOption)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">{{ $requestOption->fileOption->name }}</span>
                                <span class="text-gray-900 dark:text-gray-100">&pound;{{ number_format($requestOption->price_net, 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
                @if ($fileRequest->dtcCodes->isNotEmpty())
                    <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                        <dt class="text-gray-500 dark:text-gray-400 text-sm mb-1">DTC Codes</dt>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($fileRequest->dtcCodes as $dtcCode)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">{{ $dtcCode->code }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Files</h3>
                @if ($fileRequest->attachments->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400">No files yet.</p>
                @else
                    <div class="space-y-2">
                        @foreach ($fileRequest->attachments as $attachment)
                            <div class="flex items-center justify-between rounded-md border border-gray-200 dark:border-gray-700 px-4 py-2">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $attachment->attachment_type->label() }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $attachment->original_filename }}</p>
                                </div>
                                <a href="{{ route('client.download', $attachment) }}" class="text-orange-600 hover:text-orange-800 text-sm font-medium">Download</a>
                            </div>
                        @endforeach
                    </div>
                @endif
                @if ($fileRequest->client_downloaded_at)
                    <p class="mt-3 text-xs text-gray-400">Downloaded at: {{ $fileRequest->client_downloaded_at->format('d/m/Y H:i') }}</p>
                @endif
            </div>

            @if ($invoices->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Invoices</h3>
                    <div class="space-y-2">
                        @foreach ($invoices as $invoice)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-900 dark:text-gray-100">{{ $invoice->invoice_number ?? ('Invoice #'.$invoice->id) }}</span>
                                <span class="text-gray-500 dark:text-gray-400">&pound;{{ number_format($invoice->total_gross ?? $invoice->amount ?? 0, 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- RIGHT: MESSAGE THREAD --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow flex flex-col h-[calc(100vh-220px)]">
            <div class="border-b border-gray-200 dark:border-gray-700 px-5 py-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Messages</h3>
                @if ($whatsappNumber)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsappNumber) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                        WhatsApp
                    </a>
                @endif
            </div>

            <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
                @forelse ($fileRequest->messages as $message)
                    @if ($message->is_system)
                        <div class="text-center">
                            <span class="text-xs text-gray-400 dark:text-gray-500">{{ $message->body }} &middot; {{ $message->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    @else
                        <div class="flex flex-col {{ $message->sender_user_id === auth()->id() ? 'items-end' : 'items-start' }}">
                            <div class="max-w-xs sm:max-w-sm rounded-lg px-4 py-2 text-sm {{ $message->sender_user_id === auth()->id() ? 'bg-orange-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100' }}">
                                {{ $message->body }}
                            </div>
                            <span class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                {{ $message->sender?->first_name ?? \App\Models\Setting::brandName() }} &middot; {{ $message->created_at->format('d/m/Y H:i') }}
                            </span>
                        </div>
                    @endif
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">No messages yet.</p>
                @endforelse
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 px-5 py-4">
                <form method="POST" action="{{ route('client.messages.store', $fileRequest) }}" class="flex items-start gap-2">
                    @csrf
                    <textarea name="body" rows="2" placeholder="Type a message..." required class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm"></textarea>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700">
                        Send
                    </button>
                </form>
                <x-input-error :messages="$errors->get('body')" class="mt-1" />
            </div>
        </div>
    </div>
</x-layouts.client>
