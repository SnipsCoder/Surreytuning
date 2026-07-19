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
        $selectedFuel = $request->filled('fuel') ? $request->input('fuel') : null;

        // Distinct makes for the first dropdown.
        $makes = VehicleStat::query()
            ->select('make')
            ->distinct()
            ->orderBy('make')
            ->pluck('make');

        // Models for the chosen make (empty until a make is picked).
        $models = $selectedMake
            ? VehicleStat::query()
                ->where('make', $selectedMake)
                ->select('model')
                ->distinct()
                ->orderBy('model')
                ->pluck('model')
            : collect();

        // Only load figures once a make is selected — keeps the page light.
        $stats = null;
        if ($selectedMake) {
            $stats = VehicleStat::query()
                ->where('make', $selectedMake)
                ->when($selectedModel, fn ($q) => $q->where('model', $selectedModel))
                ->when($selectedFuel, fn ($q) => $q->where('fuel', $selectedFuel))
                ->orderBy('model')
                ->orderBy('year_from')
                ->orderBy('engine')
                ->paginate(25)
                ->withQueryString();
        }

        return view('owner.vehicle-stats.index', compact(
            'makes',
            'models',
            'stats',
            'selectedMake',
            'selectedModel',
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
