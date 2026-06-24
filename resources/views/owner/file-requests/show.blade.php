<x-layouts.owner>
    <div x-data="{ tab: 'message' }">
        <x-page-header :title="$fileRequest->request_number_formatted" :subtitle="$fileRequest->make.' '.$fileRequest->model.' • '.$fileRequest->dealer?->company_name">
            <div class="flex items-center gap-3">
                <x-status-badge :status="$fileRequest->status->label()" :colour="$fileRequest->status->colour()" />
                <a href="{{ route('file-requests.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">Back to all requests</a>
            </div>
        </x-page-header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left panel: details + status/stage/assignment + message thread --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">Vehicle &amp; Job Details</h3>
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div><dt class="text-gray-500 dark:text-gray-400">Registration</dt><dd class="text-gray-900 dark:text-gray-100">{{ $fileRequest->registration ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">VIN</dt><dd class="text-gray-900 dark:text-gray-100">{{ $fileRequest->vin_number ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">Engine</dt><dd class="text-gray-900 dark:text-gray-100">{{ $fileRequest->engine ?? '—' }} {{ $fileRequest->engine_code }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">Year</dt><dd class="text-gray-900 dark:text-gray-100">{{ $fileRequest->year ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">Fuel</dt><dd class="text-gray-900 dark:text-gray-100">{{ $fileRequest->fuel?->value ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">Transmission</dt><dd class="text-gray-900 dark:text-gray-100">{{ $fileRequest->transmission?->value ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">BHP / Torque (before)</dt><dd class="text-gray-900 dark:text-gray-100">{{ $fileRequest->bhp_before ?? '—' }} bhp / {{ $fileRequest->torque_before_nm ?? '—' }} Nm</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">Tool</dt><dd class="text-gray-900 dark:text-gray-100">{{ $fileRequest->tool?->name ?? '—' }}</dd></div>
                    </dl>

                    @if ($fileRequest->client_notes)
                        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <dt class="text-gray-500 dark:text-gray-400 text-sm mb-1">Client Notes</dt>
                            <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $fileRequest->client_notes }}</dd>
                        </div>
                    @endif
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">Message Thread</h3>
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @forelse ($fileRequest->messages as $message)
                            <div class="rounded-md p-3 text-sm {{ $message->is_internal ? 'bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800' : 'bg-gray-50 dark:bg-gray-700/50' }}">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ $message->sender?->full_name ?? 'System' }}
                                        @if ($message->is_internal)
                                            <span class="text-xs font-normal text-yellow-700 dark:text-yellow-400">(internal note)</span>
                                        @endif
                                    </span>
                                    <span class="text-xs text-gray-400">{{ $message->created_at->format('d M Y H:i') }}</span>
                                </div>
                                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $message->body }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400 dark:text-gray-500 text-center py-6">No messages yet.</p>
                        @endforelse
                    </div>

                    @if ($fileRequest->attachments->isNotEmpty())
                        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2">Attachments</h4>
                            <ul class="space-y-1">
                                @foreach ($fileRequest->attachments as $attachment)
                                    <li class="text-sm text-gray-700 dark:text-gray-300">
                                        {{ $attachment->original_filename }}
                                        <span class="text-xs text-gray-400">({{ $attachment->attachment_type->value }} • {{ $attachment->uploader?->full_name }})</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Right panel: action tabs --}}
            <div class="space-y-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">Status &amp; Assignment</h3>

                    <form method="POST" action="{{ route('owner.file-requests.status', $fileRequest) }}" class="mb-3">
                        @csrf
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
                        <select name="status" onchange="this.form.submit()" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm">
                            @foreach ($statuses as $status)
                                <option value="{{ $status->value }}" @selected($fileRequest->status === $status)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </form>

                    <form method="POST" action="{{ route('owner.file-requests.assign', $fileRequest) }}">
                        @csrf
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Technician</label>
                        <select name="assigned_technician_id" onchange="this.form.submit()" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm">
                            <option value="">Unassigned</option>
                            @foreach ($technicians as $technician)
                                <option value="{{ $technician->id }}" @selected($fileRequest->assigned_technician_id === $technician->id)>{{ $technician->full_name }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="flex flex-wrap border-b border-gray-200 dark:border-gray-700 text-xs font-medium">
                        <button type="button" x-on:click="tab = 'message'" :class="tab === 'message' ? 'text-indigo-600 border-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'text-gray-500 border-transparent dark:text-gray-400'" class="px-3 py-2 border-b-2">Message</button>
                        <button type="button" x-on:click="tab = 'respond'" :class="tab === 'respond' ? 'text-indigo-600 border-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'text-gray-500 border-transparent dark:text-gray-400'" class="px-3 py-2 border-b-2">Respond</button>
                        <button type="button" x-on:click="tab = 'charge'" :class="tab === 'charge' ? 'text-indigo-600 border-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'text-gray-500 border-transparent dark:text-gray-400'" class="px-3 py-2 border-b-2">Add Charge</button>
                        <button type="button" x-on:click="tab = 'credit'" :class="tab === 'credit' ? 'text-indigo-600 border-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'text-gray-500 border-transparent dark:text-gray-400'" class="px-3 py-2 border-b-2">Add Credit</button>
                        <button type="button" x-on:click="tab = 'note'" :class="tab === 'note' ? 'text-indigo-600 border-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'text-gray-500 border-transparent dark:text-gray-400'" class="px-3 py-2 border-b-2">Internal Note</button>
                        <button type="button" x-on:click="tab = 'void'" :class="tab === 'void' ? 'text-red-600 border-red-600 dark:text-red-400 dark:border-red-400' : 'text-gray-500 border-transparent dark:text-gray-400'" class="px-3 py-2 border-b-2">Void</button>
                    </div>

                    <div class="p-4">
                        {{-- Message tab --}}
                        <div x-show="tab === 'message'" x-cloak>
                            <form method="POST" action="{{ route('owner.messages.store', $fileRequest) }}" class="space-y-3">
                                @csrf
                                <input type="hidden" name="is_internal" value="0">
                                <textarea name="body" rows="4" required maxlength="5000" placeholder="Write a message to the client..." class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm"></textarea>
                                <button type="submit" class="w-full px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-500">Send Message</button>
                            </form>
                        </div>

                        {{-- Respond tab --}}
                        <div x-show="tab === 'respond'" x-cloak>
                            <form method="POST" action="{{ route('owner.file-requests.respond', $fileRequest) }}" enctype="multipart/form-data" class="space-y-3">
                                @csrf
                                <textarea name="message" rows="3" placeholder="Optional message to accompany the file..." class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm"></textarea>
                                <input type="file" name="file" class="w-full text-sm text-gray-700 dark:text-gray-300">
                                <button type="submit" class="w-full px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-500">Send Response</button>
                            </form>
                        </div>

                        {{-- Add Charge tab --}}
                        <div x-show="tab === 'charge'" x-cloak>
                            <form method="POST" action="{{ route('owner.file-requests.charge', $fileRequest) }}" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Description</label>
                                    <input type="text" name="description" required maxlength="255" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Amount (net, £)</label>
                                    <input type="number" name="amount_net" step="0.01" min="0.01" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm">
                                </div>
                                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                    <input type="checkbox" name="apply_vat" value="1" checked class="rounded border-gray-300 dark:border-gray-700">
                                    Apply VAT
                                </label>
                                <button type="submit" class="w-full px-4 py-2 rounded-md bg-gray-900 dark:bg-gray-700 text-white text-sm font-medium hover:bg-gray-800">Add Charge</button>
                            </form>
                        </div>

                        {{-- Add Credit tab --}}
                        <div x-show="tab === 'credit'" x-cloak>
                            <form method="POST" action="{{ route('owner.file-requests.credit', $fileRequest) }}" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Credit Type</label>
                                    <select name="credit_type" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm">
                                        <option value="slave">Slave Credits</option>
                                        <option value="evc">EVC Credits</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Amount</label>
                                    <input type="number" name="amount" step="0.01" min="0.01" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Reason</label>
                                    <input type="text" name="reason" required maxlength="255" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm">
                                </div>
                                <button type="submit" class="w-full px-4 py-2 rounded-md bg-green-600 text-white text-sm font-medium hover:bg-green-500">Add Credit</button>
                            </form>
                        </div>

                        {{-- Internal Note tab --}}
                        <div x-show="tab === 'note'" x-cloak>
                            <form method="POST" action="{{ route('owner.messages.store', $fileRequest) }}" class="space-y-3">
                                @csrf
                                <input type="hidden" name="is_internal" value="1">
                                <textarea name="body" rows="4" required maxlength="5000" placeholder="Internal note (not visible to client)..." class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm"></textarea>
                                <button type="submit" class="w-full px-4 py-2 rounded-md bg-yellow-600 text-white text-sm font-medium hover:bg-yellow-500">Add Internal Note</button>
                            </form>
                        </div>

                        {{-- Void tab --}}
                        <div x-show="tab === 'void'" x-cloak>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Voiding this request will close it permanently and remove it from active workflows.</p>
                            <button type="button" x-on:click="$dispatch('open-modal', 'void-confirm')" class="w-full px-4 py-2 rounded-md bg-red-600 text-white text-sm font-medium hover:bg-red-500">
                                Void This Request
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <x-modal id="void-confirm" title="Void File Request">
            <form method="POST" action="{{ route('owner.file-requests.void', $fileRequest) }}" class="space-y-3">
                @csrf
                <p class="text-sm text-gray-600 dark:text-gray-300">This action cannot be undone. Please provide a reason for voiding this request.</p>
                <textarea name="void_reason" rows="3" required maxlength="500" placeholder="Reason for voiding..." class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm"></textarea>
                <div class="flex justify-end gap-2">
                    <button type="button" x-on:click="$dispatch('close-modal', 'void-confirm')" class="px-4 py-2 rounded-md border border-gray-300 dark:border-gray-700 text-sm font-medium text-gray-600 dark:text-gray-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-md bg-red-600 text-white text-sm font-medium hover:bg-red-500">Confirm Void</button>
                </div>
            </form>
        </x-modal>
    </div>
</x-layouts.owner>
