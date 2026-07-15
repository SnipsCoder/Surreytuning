<?php

namespace Database\Seeders;

use App\Models\FileOption;
use Illuminate\Database\Seeder;

class FileOptionsSeeder extends Seeder
{
    public function run(): void
    {
        $options = [
            ['name' => 'AdBlue',            'sort_order' => 1],
            ['name' => 'DPF',               'sort_order' => 2],
            ['name' => 'EGR',               'sort_order' => 3],
            ['name' => 'OPF',               'sort_order' => 4],
            ['name' => 'CAT',               'sort_order' => 5],
            ['name' => 'Pop and Bang',      'sort_order' => 6],
            ['name' => 'HardCut',           'sort_order' => 7],
            ['name' => 'DTC',               'sort_order' => 8],
            ['name' => 'V-Max',             'sort_order' => 9],
            ['name' => 'Torque Monitoring', 'sort_order' => 10],
            ['name' => 'Swirl Flaps',       'sort_order' => 11],
            ['name' => 'Exhaust Flap',      'sort_order' => 12],
        ];

        foreach ($options as $opt) {
            // Skip if already present so the seeder is safe to re-run on any tenant.
            if (FileOption::where('name', $opt['name'])->exists()) {
                continue;
            }

            FileOption::create([
                'file_stage_id' => null,
                'name' => $opt['name'],
                'description' => null,
                'price_net' => 0,
                'vat_applicable' => false,
                'is_required' => false,
                'sort_order' => $opt['sort_order'],
                'is_active' => true,
            ]);
        }
    }
}
