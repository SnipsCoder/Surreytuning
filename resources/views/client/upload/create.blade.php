<x-layouts.client>
    <x-page-header title="New File Request" subtitle="Submit a vehicle file for tuning" />

    @if (session('warning'))
        <div class="mb-4 rounded-md bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 px-4 py-3 text-sm text-yellow-800 dark:text-yellow-300">
            {{ session('warning') }}
        </div>
    @endif

    <div
        x-data="{
            step: 1,
            fileOptions: [],
            dtcCodes: [],
            newDtc: '',
            fileName: '',
            prices: {{ $fileOptions->mapWithKeys(fn ($o) => [$o->id => (float) $o->price_net])->toJson() }},
            get optionsTotal() {
                return this.fileOptions.reduce((sum, id) => sum + (this.prices[id] || 0), 0).toFixed(2);
            },
            addDtc() {
                if (this.newDtc.trim() !== '') {
                    this.dtcCodes.push(this.newDtc.trim().toUpperCase());
                    this.newDtc = '';
                }
            },
            removeDtc(index) {
                this.dtcCodes.splice(index, 1);
            },
            next() { if (this.step < 3) this.step++; },
            back() { if (this.step > 1) this.step--; },
        }"
        class="bg-white dark:bg-gray-800 rounded-lg shadow"
    >
        <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
            <ol class="flex items-center gap-4 text-sm">
                <li :class="step === 1 ? 'text-brand font-semibold' : 'text-gray-500 dark:text-gray-400'">1. Vehicle Details</li>
                <li class="text-gray-300 dark:text-gray-600">&rarr;</li>
                <li :class="step === 2 ? 'text-brand font-semibold' : 'text-gray-500 dark:text-gray-400'">2. Service Selection</li>
                <li class="text-gray-300 dark:text-gray-600">&rarr;</li>
                <li :class="step === 3 ? 'text-brand font-semibold' : 'text-gray-500 dark:text-gray-400'">3. File Upload</li>
            </ol>
        </div>

        <form method="POST" action="{{ route('client.upload.store') }}" enctype="multipart/form-data" class="px-6 py-6">
            @csrf

            {{-- STEP 1: VEHICLE DETAILS --}}
            <div x-show="step === 1" x-cloak class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="make" value="Make *" />
                        <x-text-input id="make" name="make" type="text" class="mt-1 block w-full" :value="old('make')" x-bind:required="step === 1" />
                        <x-input-error :messages="$errors->get('make')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="model" value="Model *" />
                        <x-text-input id="model" name="model" type="text" class="mt-1 block w-full" :value="old('model')" x-bind:required="step === 1" />
                        <x-input-error :messages="$errors->get('model')" class="mt-1" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="year" value="Year *" />
                        <x-text-input id="year" name="year" type="number" min="1990" max="2030" class="mt-1 block w-full" :value="old('year')" x-bind:required="step === 1" />
                        <x-input-error :messages="$errors->get('year')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="registration" value="Registration (optional)" />
                        <x-text-input id="registration" name="registration" type="text" class="mt-1 block w-full" :value="old('registration')" />
                        <x-input-error :messages="$errors->get('registration')" class="mt-1" />
                    </div>
                </div>

                <div>
                    <x-input-label for="vin_number" value="VIN Number (optional)" />
                    <x-text-input id="vin_number" name="vin_number" type="text" class="mt-1 block w-full" :value="old('vin_number')" />
                    <x-input-error :messages="$errors->get('vin_number')" class="mt-1" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="engine" value="Engine *" />
                        <x-text-input id="engine" name="engine" type="text" class="mt-1 block w-full" :value="old('engine')" x-bind:required="step === 1" />
                        <x-input-error :messages="$errors->get('engine')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="engine_code" value="Engine Code (optional)" />
                        <x-text-input id="engine_code" name="engine_code" type="text" class="mt-1 block w-full" :value="old('engine_code')" />
                        <x-input-error :messages="$errors->get('engine_code')" class="mt-1" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="fuel" value="Fuel Type *" />
                        <select id="fuel" name="fuel" x-bind:required="step === 1" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-brand focus:ring-brand">
                            <option value="">Select...</option>
                            @foreach (\App\Enums\FuelType::cases() as $fuel)
                                @continue(in_array($fuel, [\App\Enums\FuelType::Electric, \App\Enums\FuelType::Hybrid]))
                                <option value="{{ $fuel->value }}" @selected(old('fuel') === $fuel->value)>{{ $fuel->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('fuel')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="transmission" value="Transmission *" />
                        <select id="transmission" name="transmission" x-bind:required="step === 1" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-brand focus:ring-brand">
                            <option value="">Select...</option>
                            @foreach (\App\Enums\TransmissionType::cases() as $transmission)
                                <option value="{{ $transmission->value }}" @selected(old('transmission') === $transmission->value)>{{ $transmission->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('transmission')" class="mt-1" />
                    </div>
                </div>

                <div>
                    <x-input-label for="bhp_before" value="BHP Before (optional)" />
                    <x-text-input id="bhp_before" name="bhp_before" type="number" step="0.1" min="0" class="mt-1 block w-full" :value="old('bhp_before')" />
                    <x-input-error :messages="$errors->get('bhp_before')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="file_type" value="File Type *" />
                    <select id="file_type" name="file_type" x-bind:required="step === 1" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-brand focus:ring-brand">
                        <option value="ecu" @selected(old('file_type', 'ecu') === 'ecu')>ECU</option>
                        <option value="tcu" @selected(old('file_type') === 'tcu')>TCU</option>
                        <option value="adblue" @selected(old('file_type') === 'adblue')>AdBlue</option>
                    </select>
                    <x-input-error :messages="$errors->get('file_type')" class="mt-1" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="torque_before_nm" value="Torque Before (Nm, optional)" />
                        <x-text-input id="torque_before_nm" name="torque_before_nm" type="number" step="0.1" min="0" class="mt-1 block w-full" :value="old('torque_before_nm')" />
                        <x-input-error :messages="$errors->get('torque_before_nm')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="ecu_model_no" value="ECU Model No (optional)" />
                        <x-text-input id="ecu_model_no" name="ecu_model_no" type="text" class="mt-1 block w-full" :value="old('ecu_model_no')" />
                        <x-input-error :messages="$errors->get('ecu_model_no')" class="mt-1" />
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="button" x-on:click="next()" class="inline-flex items-center px-4 py-2 bg-brand border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#c42910]">
                        Next: Service Selection
                    </button>
                </div>
            </div>

            {{-- STEP 2: SERVICE SELECTION --}}
            <div x-show="step === 2" x-cloak class="space-y-6">
                <div>
                    <x-input-label for="file_stage_id" value="File Stage *" />
                    <select id="file_stage_id" name="file_stage_id" x-bind:required="step === 2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-brand focus:ring-brand">
                        <option value="">Select...</option>
                        @foreach ($fileStages as $stage)
                            <option value="{{ $stage->id }}" @selected(old('file_stage_id') == $stage->id)>{{ $stage->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('file_stage_id')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="tool_id" value="Tuning Tool *" />
                    <select id="tool_id" name="tool_id" x-bind:required="step === 2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-brand focus:ring-brand">
                        <option value="">Select...</option>
                        @foreach ($tools as $tool)
                            <option value="{{ $tool->id }}" @selected(old('tool_id') == $tool->id)>{{ $tool->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('tool_id')" class="mt-1" />
                </div>

                <div>
                    <x-input-label value="Additional Options" />
                    <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @forelse ($fileOptions as $option)
                            <label class="flex items-center gap-2 rounded-md border border-gray-200 dark:border-gray-700 px-4 py-2">
                                <input type="checkbox" name="file_options[]" value="{{ $option->id }}" x-model="fileOptions" class="rounded border-gray-300 text-brand focus:ring-brand">
                                <span class="text-sm text-gray-900 dark:text-gray-100">{{ $option->name }}</span>
                                <span class="ml-auto text-sm font-medium text-gray-500 dark:text-gray-400">&pound;{{ number_format($option->price_net, 2) }}</span>
                            </label>
                        @empty
                            <p class="sm:col-span-2 rounded-md border border-dashed border-gray-200 dark:border-gray-700 px-4 py-3 text-sm text-gray-500 dark:text-gray-400">No additional options are currently available.</p>
                        @endforelse
                    </div>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Options total: <span class="font-semibold text-gray-900 dark:text-gray-100">&pound;<span x-text="optionsTotal"></span></span></p>
                </div>

                <div>
                    <x-input-label value="DTC Codes (optional)" />
                    <div class="mt-2 flex gap-2">
                        <input type="text" x-model="newDtc" x-on:keydown.enter.prevent="addDtc()" placeholder="e.g. P0420" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-brand focus:ring-brand">
                        <button type="button" x-on:click="addDtc()" class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-200 dark:hover:bg-gray-600">
                            Add
                        </button>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <template x-for="(code, index) in dtcCodes" :key="index">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                <span x-text="code"></span>
                                <input type="hidden" name="dtc_codes[]" :value="code">
                                <button type="button" x-on:click="removeDtc(index)" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">&times;</button>
                            </span>
                        </template>
                    </div>
                </div>

                <div class="flex justify-between pt-2">
                    <button type="button" x-on:click="back()" class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-200 dark:hover:bg-gray-600">
                        Back
                    </button>
                    <button type="button" x-on:click="next()" class="inline-flex items-center px-4 py-2 bg-brand border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#c42910]">
                        Next: File Upload
                    </button>
                </div>
            </div>

            {{-- STEP 3: FILE UPLOAD --}}
            <div x-show="step === 3" x-cloak class="space-y-4" x-data="{ dragging: false }">
                <div>
                    <x-input-label for="file" value="Tune File *" />
                    <div
                        x-on:dragover.prevent="dragging = true"
                        x-on:dragleave.prevent="dragging = false"
                        x-on:drop.prevent="dragging = false; if ($event.dataTransfer.files.length) { $refs.fileInput.files = $event.dataTransfer.files; fileName = $event.dataTransfer.files[0].name; }"
                        x-on:click="$refs.fileInput.click()"
                        :class="dragging ? 'border-brand bg-brand/10 dark:bg-brand/20' : 'border-gray-300 dark:border-gray-600'"
                        class="mt-1 flex flex-col items-center justify-center w-full rounded-md border-2 border-dashed px-6 py-8 text-center cursor-pointer transition-colors"
                    >
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            <span class="font-semibold text-brand dark:text-brand">Click to choose a file</span> or drag and drop it here
                        </p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-text="fileName ? 'Selected: ' + fileName : 'No file chosen'"></p>
                        <input
                            id="file"
                            name="file"
                            type="file"
                            x-ref="fileInput"
                            x-on:change="fileName = $event.target.files.length ? $event.target.files[0].name : ''"
                            required
                            class="hidden"
                        >
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Any binary tune file accepted &mdash; max 50MB</p>
                    <x-input-error :messages="$errors->get('file')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="client_notes" value="Notes (optional)" />
                    <textarea id="client_notes" name="client_notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-brand focus:ring-brand">{{ old('client_notes') }}</textarea>
                    <x-input-error :messages="$errors->get('client_notes')" class="mt-1" />
                </div>

                <div class="rounded-md bg-gray-50 dark:bg-gray-900/50 px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                    <p class="font-medium text-gray-900 dark:text-gray-100 mb-1">Ready to submit</p>
                    <p>File selected: <span x-text="fileName || 'No file selected'"></span></p>
                </div>

                <div class="flex justify-between pt-2">
                    <button type="button" x-on:click="back()" class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-200 dark:hover:bg-gray-600">
                        Back
                    </button>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-brand border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#c42910]">
                        Submit File Request
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.client>
