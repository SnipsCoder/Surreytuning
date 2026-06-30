<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SettingsSeeder::class,
            OpeningHoursSeeder::class,
            PortalStatusSeeder::class,
            FileStagesSeeder::class,
            TuningToolsSeeder::class,
            AdminUserSeeder::class,
            BoschEcuSeeder::class,
        ]);
    }
}
