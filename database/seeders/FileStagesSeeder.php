<?php

namespace Database\Seeders;

use App\Models\FileStage;
use Illuminate\Database\Seeder;

class FileStagesSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            ['name' => 'Stage 1 Remap', 'sort_order' => 1],
            ['name' => 'Stage 2 Remap', 'sort_order' => 2],
            ['name' => 'Stage 3 Remap', 'sort_order' => 3],
            ['name' => 'DPF Off', 'sort_order' => 4],
            ['name' => 'EGR Off', 'sort_order' => 5],
            ['name' => 'Adblue Off', 'sort_order' => 6],
        ];

        foreach ($stages as $stage) {
            FileStage::create([
                'name' => $stage['name'],
                'price_net' => 0,
                'sort_order' => $stage['sort_order'],
                'is_active' => true,
            ]);
        }
    }
}
