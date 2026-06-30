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
            ->when($request->filled('fuel'), fn ($query) => $query->where('fuel', $request->input('fuel')))
            ->orderBy('make')
            ->orderBy('model')
            ->paginate(25)
            ->withQueryString();

        return view('client.vehicle-stats.index', compact('vehicleStats'));
    }
}
