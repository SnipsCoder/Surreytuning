<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\VehicleStat;
use Illuminate\Http\Request;

class VehicleStatController extends Controller
{
    public function index(Request $request)
    {
        $vehicleStats = VehicleStat::query()
            ->when($request->filled('make'), fn ($query) => $query->where('make', 'like', "%{$request->input('make')}%"))
            ->when($request->filled('model'), fn ($query) => $query->where('model', 'like', "%{$request->input('model')}%"))
            ->when($request->filled('engine'), fn ($query) => $query->where('engine', $request->input('engine')))
            ->when($request->filled('fuel'), fn ($query) => $query->where('fuel', $request->input('fuel')))
            ->orderBy('make')
            ->orderBy('model')
            ->paginate(25)
            ->withQueryString();

        $makes = VehicleStat::query()->select('make')->distinct()->orderBy('make')->pluck('make');

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

        return view('client.vehicle-stats.index', compact('vehicleStats', 'makes', 'modelsByMake', 'enginesByMakeModel'));
    }
}
