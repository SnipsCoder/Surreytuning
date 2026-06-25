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
        $vehicleStats = VehicleStat::query()
            ->when($request->filled('make'), fn ($query) => $query->where('make', 'like', "%{$request->input('make')}%"))
            ->when($request->filled('model'), fn ($query) => $query->where('model', 'like', "%{$request->input('model')}%"))
            ->when($request->filled('fuel'), fn ($query) => $query->where('fuel', $request->input('fuel')))
            ->orderBy('make')
            ->orderBy('model')
            ->paginate(25)
            ->withQueryString();

        return view('owner.vehicle-stats.index', compact('vehicleStats'));
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
