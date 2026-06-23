<?php

namespace Database\Seeders;

use App\Models\PortalStatus;
use Illuminate\Database\Seeder;

class PortalStatusSeeder extends Seeder
{
    public function run(): void
    {
        PortalStatus::firstOrCreate(['id' => 1], [
            'status' => 'available',
        ]);
    }
}
