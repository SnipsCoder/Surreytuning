<?php

namespace App\Http\Controllers\Owner;

use App\Enums\PortalStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\PortalStatus;
use Illuminate\Http\Request;
class PortalStatusController extends Controller
{
    public function update(Request $request)
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', array_map(fn ($case) => $case->value, PortalStatusEnum::cases()))],
        ]);

        $portalStatus = PortalStatus::current();
        $portalStatus->update([
            'status' => $validated['status'],
            'updated_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Portal status updated.');
    }
}
