<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleStatsSeeder extends Seeder
{
    /**
     * Import the full vehicle stats dataset from database/data/vehicle_stats.csv.
     * Truncates and reloads, so it is safe to re-run for a full refresh.
     */
    public function run(): void
    {
        // base_path, not storage_path — tenancy suffixes storage paths per tenant,
        // but this CSV is shared source data that ships with the codebase.
        $path = base_path('database/data/vehicle_stats.csv');

        if (! file_exists($path)) {
            $this->command->warn("Vehicle stats CSV not found at {$path} — skipping.");

            return;
        }

        $validFuels = ['petrol', 'diesel', 'electric', 'hybrid'];

        DB::table('vehicle_stats')->truncate();

        $handle = fopen($path, 'r');
        fgetcsv($handle); // skip header row

        $now = now();
        $batch = [];
        $count = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            // Columns: make, model, year_from, year_to, generation, engine,
            //          fuel, bhp_before, bhp_after, torque_before_nm,
            //          torque_after_nm, stage, notes
            if (count($row) < 12) {
                $skipped++;

                continue;
            }

            $row = array_map(fn ($v) => trim((string) $v), $row);

            $fuel = strtolower($row[6]);

            if ($row[0] === '' || $row[1] === '' || ! in_array($fuel, $validFuels, true)) {
                $skipped++;

                continue;
            }

            $batch[] = [
                'make' => $row[0],
                'model' => $row[1],
                'year_from' => $row[2] !== '' ? (int) $row[2] : null,
                'year_to' => $row[3] !== '' ? (int) $row[3] : null,
                'generation' => $row[4] !== '' ? $row[4] : null,
                'engine' => $row[5],
                'fuel' => $fuel,
                'bhp_before' => $row[7] !== '' ? $row[7] : 0,
                'bhp_after' => $row[8] !== '' ? $row[8] : 0,
                'torque_before_nm' => $row[9] !== '' ? $row[9] : 0,
                'torque_after_nm' => $row[10] !== '' ? $row[10] : 0,
                'stage' => $row[11] !== '' ? (int) $row[11] : 1,
                'notes' => ($row[12] ?? '') !== '' ? $row[12] : null,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($batch) === 500) {
                DB::table('vehicle_stats')->insert($batch);
                $count += 500;
                $batch = [];
                $this->command->info("  {$count} rows imported...");
            }
        }

        fclose($handle);

        if ($batch !== []) {
            DB::table('vehicle_stats')->insert($batch);
            $count += count($batch);
        }

        $this->command->info("Vehicle stats seeder complete: {$count} records imported, {$skipped} rows skipped.");
    }
}
