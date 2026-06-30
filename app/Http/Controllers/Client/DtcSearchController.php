<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\DtcLibrary;
use Illuminate\Http\Request;

class DtcSearchController extends Controller
{
    public function index(Request $request)
    {
        return view('client.dtc-search.index');
    }

    public function search(Request $request)
    {
        $query = $request->string('q')->trim()->toString();

        if ($query === '') {
            return response()->json(['data' => [], 'total' => 0]);
        }

        $results = DtcLibrary::query()
            ->where(function ($builder) use ($query) {
                $builder->where('code', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->orderBy('code')
            ->limit(50)
            ->get(['code', 'description', 'possible_causes', 'possible_remedies', 'severity_estimate']);

        return response()->json([
            'data' => $results,
            'total' => $results->count(),
        ]);
    }
}
