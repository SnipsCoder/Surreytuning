<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BoschEcuSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/bosch_ecus.csv');

        if (! file_exists($path)) {
            $this->command->warn('Bosch ECU data file not found: database/data/bosch_ecus.csv — skipping.');

            return;
        }

        $handle = fopen($path, 'r');
        $headers = fgetcsv($handle); // skip header row

        DB::table('bosch_ecus')->truncate();

        $batch = [];
        $count = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 3) {
                continue;
            }

            $batch[] = [
                'manufacturer_number' => trim($row[0]),
                'model' => trim($row[1]),
                'car_producer' => trim($row[2]),
                'image_path' => isset($row[3]) ? trim($row[3]) ?: null : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) === 500) {
                DB::table('bosch_ecus')->insert($batch);
                $count += 500;
                $batch = [];
            }
        }

        if ($batch) {
            DB::table('bosch_ecus')->insert($batch);
            $count += count($batch);
        }

        fclose($handle);

        $this->command->info("Bosch ECU seeder: imported {$count} records.");
    }
}
