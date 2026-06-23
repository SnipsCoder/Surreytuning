<?php

namespace Database\Seeders;

use App\Models\TuningTool;
use Illuminate\Database\Seeder;

class TuningToolsSeeder extends Seeder
{
    public function run(): void
    {
        $tools = [
            ['name' => 'Autotuner OBD', 'category' => 'obd', 'sort_order' => 1],
            ['name' => 'Autotuner Bench', 'category' => 'bench', 'sort_order' => 2],
            ['name' => 'Autotuner Boot', 'category' => 'boot', 'sort_order' => 3],
            ['name' => 'Kess3 OBD', 'category' => 'obd', 'sort_order' => 4],
            ['name' => 'Kess3 Bench', 'category' => 'bench', 'sort_order' => 5],
            ['name' => 'CMD OBD', 'category' => 'obd', 'sort_order' => 6],
            ['name' => 'CMD Bench', 'category' => 'bench', 'sort_order' => 7],
            ['name' => 'CMD Boot', 'category' => 'boot', 'sort_order' => 8],
        ];

        foreach ($tools as $tool) {
            TuningTool::create([
                'name' => $tool['name'],
                'category' => $tool['category'],
                'sort_order' => $tool['sort_order'],
                'is_active' => true,
            ]);
        }
    }
}
