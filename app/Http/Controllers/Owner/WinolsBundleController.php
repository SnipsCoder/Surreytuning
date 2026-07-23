<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreWinolsBundleRequest;
use App\Models\WinolsBundle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

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
        $data = $request->validated();
        unset($data['image']);
        // Unchecked checkboxes submit nothing, so validated() omits them.
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('winols-bundles', 'public');
        }

        WinolsBundle::create($data);

        return back()->with('success', 'WinOLS bundle created.');
    }

    public function update(StoreWinolsBundleRequest $request, WinolsBundle $winolsBundle): RedirectResponse
    {
        $data = $request->validated();
        unset($data['image']);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            if ($winolsBundle->image_path) {
                Storage::disk('public')->delete($winolsBundle->image_path);
            }
            $data['image_path'] = $request->file('image')->store('winols-bundles', 'public');
        }

        $winolsBundle->update($data);

        return back()->with('success', 'WinOLS bundle updated.');
    }

    public function destroy(WinolsBundle $winolsBundle): RedirectResponse
    {
        if ($winolsBundle->creditTransactions()->exists()) {
            return back()->with('error', 'Cannot delete this bundle: it has been purchased before.');
        }

        if ($winolsBundle->image_path) {
            Storage::disk('public')->delete($winolsBundle->image_path);
        }

        $winolsBundle->delete();

        return back()->with('success', 'WinOLS bundle deleted.');
    }
}
