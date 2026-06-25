<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\UpdateBrandingRequest;
use App\Http\Requests\Owner\UpdateOpeningHoursRequest;
use App\Http\Requests\Owner\UpdateSettingsRequest;
use App\Models\OpeningHour;
use App\Models\Setting;

class SettingsController extends Controller
{
    public function index()
    {
        return view('owner.settings.index', [
            'settings' => Setting::get(),
            'openingHours' => OpeningHour::orderBy('day_of_week')->get(),
        ]);
    }

    public function update(UpdateSettingsRequest $request)
    {
        $data = $request->validated();
        $data['dealer_auto_onboard'] = $request->boolean('dealer_auto_onboard');

        Setting::get()->update($data);

        return back()
            ->with('success', 'Settings updated.')
            ->with('active_tab', $request->input('active_tab', 'account'));
    }

    public function updateHours(UpdateOpeningHoursRequest $request)
    {
        foreach ($request->validated('hours') as $hour) {
            OpeningHour::where('id', $hour['id'])->update([
                'is_open' => $hour['is_open'] ?? false,
                'open_time' => $hour['open_time'],
                'close_time' => $hour['close_time'],
            ]);
        }

        return back()
            ->with('success', 'Opening hours updated.')
            ->with('active_tab', 'hours');
    }

    public function updateBranding(UpdateBrandingRequest $request)
    {
        $settings = Setting::get();
        $data = $request->validated();

        foreach (['logo_light', 'logo_dark', 'login_background'] as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('branding', 'r2');
            } else {
                unset($data[$field]);
            }
        }

        $settings->update($data);

        return back()
            ->with('success', 'Branding updated.')
            ->with('active_tab', 'branding');
    }
}
