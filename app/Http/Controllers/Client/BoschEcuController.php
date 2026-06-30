<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\BoschEcu;
use Illuminate\Http\Request;

class BoschEcuController extends Controller
{
    public function index(Request $request)
    {
        $manufacturerNumber = $request->string('manufacturer_number')->trim()->toString();
        $carProducer = $request->string('car_producer')->trim()->toString();

        $hasSearched = $manufacturerNumber !== '' || $carProducer !== '';

        $results = null;

        if ($hasSearched) {
            $results = BoschEcu::query()
                ->when($manufacturerNumber !== '', fn ($query) => $query->where('manufacturer_number', 'like', "%{$manufacturerNumber}%"))
                ->when($carProducer !== '', fn ($query) => $query->where('car_producer', 'like', "%{$carProducer}%"))
                ->orderBy('car_producer')
                ->paginate(25)
                ->withQueryString();
        }

        return view('client.bosch-ecu.index', [
            'results' => $results,
            'hasSearched' => $hasSearched,
            'manufacturerNumber' => $manufacturerNumber,
            'carProducer' => $carProducer,
        ]);
    }
}
