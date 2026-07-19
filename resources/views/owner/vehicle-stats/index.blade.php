<x-layouts.owner>
    <x-page-header title="Vehicle Stats" subtitle="Performance figures by make/model">
        <a href="{{ route('vehicle-stats.create') }}"
            class="px-4 py-2 rounded-md bg-brand text-white text-sm font-medium hover:bg-brand-dark">
            Add Vehicle Stat
        </a>
    </x-page-header>

    {{-- Make → Model → Engine → Fuel selector. Changing an earlier field resets the
         ones after it, then auto-submits. Mirrors the dealer Vehicle Stats lookup. --}}
    <form method="GET" action="{{ route('vehicle-stats.index') }}"
          class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div>
            <label class="block text-sm font-medium text-slate-400">Make</label>
            <select name="make"
                    onchange="this.form.model.value=''; this.form.engine.value=''; this.form.submit()"
                    class="mt-1 block w-full rounded-lg border border-[#2a2a2a] bg-[#0d0d0d] text-gray-100 text-sm px-2 py-2 focus:border-brand/50 focus:ring-0">
                <option value="">Select make…</option>
                @foreach ($makes as $make)
                    <option value="{{ $make }}" @selected($selectedMake === $make)>{{ $make }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-400">Model</label>
            <select name="model"
                    onchange="this.form.engine.value=''; this.form.submit()"
                    @disabled(! $selectedMake)
                    class="mt-1 block w-full rounded-lg border border-[#2a2a2a] bg-[#0d0d0d] text-gray-100 text-sm px-2 py-2 focus:border-brand/50 focus:ring-0 disabled:opacity-50">
                <option value="">{{ $selectedMake ? 'All models' : 'Select a make first' }}</option>
                @foreach ($models as $model)
                    <option value="{{ $model }}" @selected($selectedModel === $model)>{{ $model }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-400">Engine</label>
            <select name="engine"
                    onchange="this.form.submit()"
                    @disabled(! $selectedModel)
                    class="mt-1 block w-full rounded-lg border border-[#2a2a2a] bg-[#0d0d0d] text-gray-100 text-sm px-2 py-2 focus:border-brand/50 focus:ring-0 disabled:opacity-50">
                <option value="">{{ $selectedModel ? 'All engines' : 'Select a model first' }}</option>
                @foreach (($enginesByMakeModel[$selectedMake.'|'.$selectedModel] ?? collect()) as $engine)
                    <option value="{{ $engine }}" @selected($selectedEngine === $engine)>{{ $engine }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-400">Fuel</label>
            <select name="fuel"
                    onchange="this.form.submit()"
                    class="mt-1 block w-full rounded-lg border border-[#2a2a2a] bg-[#0d0d0d] text-gray-100 text-sm px-2 py-2 focus:border-brand/50 focus:ring-0">
                <option value="">All fuels</option>
                @foreach (\App\Enums\FuelType::cases() as $fuel)
                    @continue($fuel === \App\Enums\FuelType::Hybrid)
                    <option value="{{ $fuel->value }}" @selected($selectedFuel === $fuel->value)>{{ $fuel->label() }}</option>
                @endforeach
            </select>
        </div>
    </form>

    @if (! $selectedMake)
        <div class="rounded-xl border border-dashed border-gray-700/50 bg-[#1e293b] px-4 py-12 text-center">
            <p class="text-sm text-slate-400">Select a make above to view its performance figures.</p>
        </div>
    @elseif ($stats->isEmpty())
        <div class="rounded-xl border border-gray-700/50 bg-[#1e293b] px-4 py-12 text-center">
            <p class="text-sm text-slate-400">
                No vehicle stats for {{ $selectedMake }}{{ $selectedModel ? ' · '.$selectedModel : '' }}{{ $selectedEngine ? ' · '.$selectedEngine : '' }}.
            </p>
        </div>
    @else
        <div class="flex items-center justify-between mb-3">
            <p class="text-sm text-slate-400">
                {{ number_format($stats->total()) }} {{ \Illuminate\Support\Str::plural('result', $stats->total()) }}
                for <span class="font-medium text-white">{{ $selectedMake }}</span>{{ $selectedModel ? ' · '.$selectedModel : '' }}{{ $selectedEngine ? ' · '.$selectedEngine : '' }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-700/50 bg-[#1e293b] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-[#0d0d0d]/50 text-left text-xs uppercase tracking-wide text-slate-400">
                        <tr>
                            <th class="px-4 py-2 font-medium">Model</th>
                            <th class="px-4 py-2 font-medium">Years</th>
                            <th class="px-4 py-2 font-medium">Engine</th>
                            <th class="px-4 py-2 font-medium">BHP</th>
                            <th class="px-4 py-2 font-medium">Torque</th>
                            <th class="px-4 py-2 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700/50">
                        @foreach ($stats as $v)
                            <tr class="hover:bg-white/5">
                                <td class="px-4 py-2 text-white font-medium">
                                    {{ $v->model }}
                                    @if ($v->generation)
                                        <span class="block text-xs font-normal text-slate-500">{{ $v->generation }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-slate-400 whitespace-nowrap">
                                    @if ($v->year_from && $v->year_to)
                                        {{ $v->year_from }}–{{ $v->year_to }}
                                    @elseif ($v->year_from)
                                        {{ $v->year_from }}+
                                    @elseif ($v->year_to)
                                        Up to {{ $v->year_to }}
                                    @else
                                        &mdash;
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-gray-300">
                                    {{ $v->engine }}
                                    @if ($v->stage)
                                        <span class="ml-1 inline-block rounded bg-brand/15 text-brand text-xs px-1.5 py-0.5 align-middle">{{ $v->stage }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-gray-300 whitespace-nowrap">
                                    {{ $v->bhp_before }} <span class="text-slate-500">→</span>
                                    <span class="font-medium text-white">{{ $v->bhp_after }}</span>
                                </td>
                                <td class="px-4 py-2 text-gray-300 whitespace-nowrap">
                                    {{ $v->torque_before_nm }} <span class="text-slate-500">→</span>
                                    <span class="font-medium text-white">{{ $v->torque_after_nm }}</span>
                                    <span class="text-xs text-slate-500">Nm</span>
                                </td>
                                <td class="px-4 py-2">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('vehicle-stats.edit', $v) }}" class="text-brand hover:underline">Edit</a>
                                        <form method="POST" action="{{ route('vehicle-stats.destroy', $v) }}" class="inline" onsubmit="return confirm('Delete this vehicle stat?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:underline">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $stats->links() }}
        </div>
    @endif
</x-layouts.owner>
