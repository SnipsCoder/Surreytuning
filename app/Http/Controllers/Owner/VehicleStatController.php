<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreVehicleStatRequest;
use App\Models\VehicleStat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleStatController extends Controller
{
    public function index(Request $request): View
    {
        $selectedMake = $request->filled('make') ? $request->input('make') : null;
        $selectedModel = $request->filled('model') ? $request->input('model') : null;
        $selectedEngine = $request->filled('engine') ? $request->input('engine') : null;
        $selectedFuel = $request->filled('fuel') ? $request->input('fuel') : null;

        // Distinct makes for the first dropdown.
        $makes = VehicleStat::query()
            ->select('make')
            ->distinct()
            ->orderBy('make')
            ->pluck('make');

        // Cascading lookup data so Make → Model → Engine can rebuild client-side,
        // matching the dealer-facing Vehicle Stats lookup.
        $modelsByMake = VehicleStat::query()
            ->select('make', 'model')
            ->distinct()
            ->orderBy('make')
            ->orderBy('model')
            ->get()
            ->groupBy('make')
            ->map(fn ($group) => $group->pluck('model')->unique()->values());

        $enginesByMakeModel = VehicleStat::query()
            ->select('make', 'model', 'engine')
            ->distinct()
            ->orderBy('make')
            ->orderBy('model')
            ->orderBy('engine')
            ->get()
            ->groupBy(fn ($stat) => $stat->make.'|'.$stat->model)
            ->map(fn ($group) => $group->pluck('engine')->unique()->values());

        $stats = VehicleStat::query()
            ->when($selectedMake, fn ($q) => $q->where('make', 'like', "%{$selectedMake}%"))
            ->when($selectedModel, fn ($q) => $q->where('model', 'like', "%{$selectedModel}%"))
            ->when($selectedEngine, fn ($q) => $q->where('engine', $selectedEngine))
            ->when($selectedFuel, fn ($q) => $q->where('fuel', $selectedFuel))
            ->orderBy('make')
            ->orderBy('model')
            ->orderBy('year_from')
            ->orderBy('engine')
            ->paginate(25)
            ->withQueryString();

        return view('owner.vehicle-stats.index', compact(
            'makes',
            'modelsByMake',
            'enginesByMakeModel',
            'stats',
            'selectedMake',
            'selectedModel',
            'selectedEngine',
            'selectedFuel',
        ));
    }

    public function create(): View
    {
        return view('owner.vehicle-stats.create');
    }

    public function store(StoreVehicleStatRequest $request): RedirectResponse
    {
        VehicleStat::create($request->validated());

        return redirect()->route('vehicle-stats.index')->with('success', 'Vehicle stat created.');
    }

    public function edit(VehicleStat $vehicleStat): View
    {
        return view('owner.vehicle-stats.edit', compact('vehicleStat'));
    }

    public function update(StoreVehicleStatRequest $request, VehicleStat $vehicleStat): RedirectResponse
    {
        $vehicleStat->update($request->validated());

        return redirect()->route('vehicle-stats.index')->with('success', 'Vehicle stat updated.');
    }

    public function destroy(VehicleStat $vehicleStat): RedirectResponse
    {
        $vehicleStat->delete();

        return redirect()->route('vehicle-stats.index')->with('success', 'Vehicle stat deleted.');
    }
}
