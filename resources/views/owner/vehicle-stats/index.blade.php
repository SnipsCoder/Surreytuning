<x-layouts.owner>
    <x-page-header title="Vehicle Stats" subtitle="Performance figures by make/model">
        <a href="{{ route('vehicle-stats.create') }}"
            class="px-4 py-2 rounded-md bg-brand text-white text-sm font-medium hover:bg-brand-dark">
            Add Vehicle Stat
        </a>
    </x-page-header>

    <div class="bg-[#1e293b] border border-white/5 rounded-xl p-5 mb-4">
        <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-3">Vehicle Stats Lookup</h3>
        <form method="GET" action="{{ route('vehicle-stats.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Make</label>
                <select name="make" id="filter-make" style="color:#f1f5f9"
                    class="block w-full rounded-lg border border-white/10 bg-[#0d0d0d] text-slate-100 text-sm px-3 py-2 focus:border-brand/50 focus:ring-0">
                    <option value="" style="background-color:#1e293b;color:#ffffff">All makes</option>
                    @foreach ($makes as $make)
                        <option value="{{ $make }}" @selected($selectedMake === $make) style="background-color:#1e293b;color:#ffffff">{{ $make }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Model</label>
                <select name="model" id="filter-model" style="color:#f1f5f9"
                    class="block w-full rounded-lg border border-white/10 bg-[#0d0d0d] text-slate-100 text-sm px-3 py-2 focus:border-brand/50 focus:ring-0">
                    <option value="" style="background-color:#1e293b;color:#ffffff">All models</option>
                    @foreach (($modelsByMake[$selectedMake] ?? collect()) as $model)
                        <option value="{{ $model }}" @selected($selectedModel === $model) style="background-color:#1e293b;color:#ffffff">{{ $model }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Engine</label>
                <select name="engine" id="filter-engine" style="color:#f1f5f9"
                    class="block w-full rounded-lg border border-white/10 bg-[#0d0d0d] text-slate-100 text-sm px-3 py-2 focus:border-brand/50 focus:ring-0">
                    <option value="" style="background-color:#1e293b;color:#ffffff">All engines</option>
                    @foreach (($enginesByMakeModel[$selectedMake.'|'.$selectedModel] ?? collect()) as $engine)
                        <option value="{{ $engine }}" @selected($selectedEngine === $engine) style="background-color:#1e293b;color:#ffffff">{{ $engine }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Fuel</label>
                <select name="fuel" style="color:#f1f5f9"
                    class="block w-full rounded-lg border border-white/10 bg-[#0d0d0d] text-slate-100 text-sm px-3 py-2 focus:border-brand/50 focus:ring-0">
                    <option value="" style="background-color:#1e293b;color:#ffffff">All fuel types</option>
                    @foreach (\App\Enums\FuelType::cases() as $fuel)
                        <option value="{{ $fuel->value }}" @selected($selectedFuel === $fuel->value) style="background-color:#1e293b;color:#ffffff">{{ $fuel->label() }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit"
                class="flex items-center justify-center gap-2 w-full py-2.5 bg-brand hover:bg-brand-dark text-white text-sm font-semibold rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Filter
            </button>
        </form>
    </div>

    <script>
        (function () {
                const modelsByMake = @json($modelsByMake);
                const enginesByMakeModel = @json($enginesByMakeModel);
                const makeSelect = document.getElementById('filter-make');
                const modelSelect = document.getElementById('filter-model');
                const engineSelect = document.getElementById('filter-engine');
                if (!makeSelect || !modelSelect || !engineSelect) return;

                function rebuildEngines(keepCurrent) {
                    const current = keepCurrent ? engineSelect.value : '';
                    const engines = enginesByMakeModel[makeSelect.value + '|' + modelSelect.value] || [];
                    engineSelect.innerHTML = '<option value="" style="background-color:#1e293b;color:#ffffff">All engines</option>';
                    engines.forEach(function (engine) {
                        const opt = document.createElement('option');
                        opt.value = engine;
                        opt.textContent = engine;
                        opt.style.backgroundColor = '#1e293b';
                        opt.style.color = '#ffffff';
                        if (engine === current) opt.selected = true;
                        engineSelect.appendChild(opt);
                    });
                }

                makeSelect.addEventListener('change', function () {
                    const current = modelSelect.value;
                    const models = modelsByMake[this.value] || [];
                    modelSelect.innerHTML = '<option value="" style="background-color:#1e293b;color:#ffffff">All models</option>';
                    models.forEach(function (model) {
                        const opt = document.createElement('option');
                        opt.value = model;
                        opt.textContent = model;
                        opt.style.backgroundColor = '#1e293b';
                        opt.style.color = '#ffffff';
                        if (model === current) opt.selected = true;
                        modelSelect.appendChild(opt);
                    });
                    rebuildEngines(false);
                });

                modelSelect.addEventListener('change', function () {
                    rebuildEngines(false);
                });
            })();
        </script>

    @if (! request()->hasAny(['make', 'model', 'engine', 'fuel']))
        <div class="rounded-xl border border-dashed border-gray-700/50 bg-[#1e293b] px-4 py-12 text-center">
            <p class="text-sm text-slate-400">Use the filters above to search vehicle stats.</p>
            <p class="text-xs text-slate-600 mt-1">Select a make, model, engine, or fuel type, then press Filter.</p>
        </div>
    @elseif ($stats->isEmpty())
        <div class="rounded-xl border border-gray-700/50 bg-[#1e293b] px-4 py-12 text-center">
            <p class="text-sm text-slate-400">
                No vehicle stats for {{ $selectedMake }}{{ $selectedModel ? ' · '.$selectedModel : '' }}{{ $selectedEngine ? ' · '.$selectedEngine : '' }}.
            </p>
            <p class="text-xs text-slate-600 mt-1">Try adjusting or clearing your filters.</p>
        </div>
    @else
        <div class="bg-[#1e293b] border border-white/5 rounded-xl overflow-hidden">
            <div class="px-5 py-3 border-b border-white/5 flex items-center justify-between">
                <h3 class="text-base font-semibold text-slate-300 uppercase tracking-widest">Results</h3>
                <span class="text-sm text-slate-400">{{ $stats->total() }} {{ \Illuminate\Support\Str::plural('match', $stats->total()) }}</span>
            </div>

            <div class="divide-y divide-white/5">
                @foreach ($stats as $v)
                    <div class="px-6 py-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-4xl font-extrabold tracking-tight text-white">{{ $v->make }} {{ $v->model }}</p>
                                <p class="text-lg text-slate-400 mt-2">
                                    {{ $v->year_from }}–{{ $v->year_to }} · {{ $v->engine }} · {{ $v->fuel->label() }}
                                </p>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <span class="inline-flex items-center rounded-md bg-brand/10 text-brand text-base font-semibold px-3 py-1.5">Stage {{ $v->stage }}</span>
                                <a href="{{ route('vehicle-stats.edit', $v) }}" class="text-sm text-brand hover:underline">Edit</a>
                                <form method="POST" action="{{ route('vehicle-stats.destroy', $v) }}" class="inline" onsubmit="return confirm('Delete this vehicle stat?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm text-red-400 hover:underline">Delete</button>
                                </form>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-5">
                            <div class="rounded-lg bg-[#0d0d0d] border border-white/5 px-5 py-4">
                                <p class="text-sm font-medium text-slate-500 uppercase tracking-wide">BHP</p>
                                <p class="text-3xl font-bold text-slate-200 mt-1.5">
                                    {{ (int) $v->bhp_before }} <span class="text-slate-600">→</span>
                                    <span class="text-brand">{{ (int) $v->bhp_after }}</span>
                                    <span class="text-lg font-semibold text-slate-500">(+{{ (int) ($v->bhp_after - $v->bhp_before) }})</span>
                                </p>
                            </div>
                            <div class="rounded-lg bg-[#0d0d0d] border border-white/5 px-5 py-4">
                                <p class="text-sm font-medium text-slate-500 uppercase tracking-wide">Torque (Nm)</p>
                                <p class="text-3xl font-bold text-slate-200 mt-1.5">
                                    {{ (int) $v->torque_before_nm }} <span class="text-slate-600">→</span>
                                    <span class="text-brand">{{ (int) $v->torque_after_nm }}</span>
                                    <span class="text-lg font-semibold text-slate-500">(+{{ (int) ($v->torque_after_nm - $v->torque_before_nm) }})</span>
                                </p>
                            </div>
                        </div>

                        @if ($v->notes)
                            <p class="text-base text-slate-400 mt-5">{{ $v->notes }}</p>
                        @endif
                    </div>
                @endforeach
            </div>

            @if ($stats->hasPages())
                <div class="px-5 py-3 border-t border-white/5">
                    {{ $stats->links() }}
                </div>
            @endif
        </div>
    @endif
</x-layouts.owner>
