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
            ['name' => 'BFlash', 'category' => 'other', 'sort_order' => 9],
            ['name' => 'ByteShooter', 'category' => 'other', 'sort_order' => 10],
            ['name' => 'CMD BDM', 'category' => 'bdm', 'sort_order' => 11],
            ['name' => 'Eprom Chip - Checksum Needed', 'category' => 'other', 'sort_order' => 12],
            ['name' => 'Flex', 'category' => 'other', 'sort_order' => 13],
            ['name' => 'Genius', 'category' => 'other', 'sort_order' => 14],
            ['name' => 'Kess3 Boot', 'category' => 'boot', 'sort_order' => 15],
            ['name' => 'Kess2 OBD', 'category' => 'obd', 'sort_order' => 16],
            ['name' => 'K-Tag', 'category' => 'other', 'sort_order' => 17],
            ['name' => 'MPPS', 'category' => 'other', 'sort_order' => 18],
            ['name' => 'Powergate', 'category' => 'other', 'sort_order' => 19],
            ['name' => 'Transdata', 'category' => 'other', 'sort_order' => 20],
            ['name' => 'X17', 'category' => 'other', 'sort_order' => 21],
            ['name' => 'Other', 'category' => 'other', 'sort_order' => 22],
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
