<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreWinolsBundleRequest;
use App\Models\WinolsBundle;
use Illuminate\Http\RedirectResponse;

class WinolsBundleController extends Controller
{
    public function index()
    {
        return view('owner.winols-bundles.index', [
            'winolsBundles' => WinolsBundle::orderBy('credits')->get(),
        ]);
    }

    public function store(StoreWinolsBundleRequest $request): RedirectResponse
    {
        WinolsBundle::create($request->validated());

        return back()->with('success', 'WinOLS bundle created.');
    }

    public function update(StoreWinolsBundleRequest $request, WinolsBundle $winolsBundle): RedirectResponse
    {
        $winolsBundle->update($request->validated());

        return back()->with('success', 'WinOLS bundle updated.');
    }

    public function destroy(WinolsBundle $winolsBundle): RedirectResponse
    {
        if ($winolsBundle->creditTransactions()->exists()) {
            return back()->with('error', 'Cannot delete this bundle: it has been purchased before.');
        }

        $winolsBundle->delete();

        return back()->with('success', 'WinOLS bundle deleted.');
    }
}
