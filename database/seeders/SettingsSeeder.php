<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        Setting::firstOrCreate(['id' => 1], [
            'vat_rate' => 20.00,
            'invoice_start_number' => 10000,
            'invoice_reference_prefix' => 'INV',
            'theme_colour' => '#e63012',
        ]);
    }
}
