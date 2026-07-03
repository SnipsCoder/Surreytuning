<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DtcLibrarySeeder extends Seeder
{
    public function run(): void
    {
        // Tenancy suffixes storage_path() with the tenant ID, so use base_path to reach the shared import directory
        $path = base_path('storage/app/dtc-import/DTC_Codes_With_Remedies_Formatted.xlsx');

        if (! file_exists($path)) {
            $this->command->warn('DTC import file not found: storage/app/dtc-import/DTC_Codes_With_Remedies_Formatted.xlsx — skipping.');

            return;
        }

        $this->command->info('Loading spreadsheet...');

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();

        DB::table('dtc_library')->truncate();

        $batch = [];
        $count = 0;
        $skipped = 0;
        $highestRow = $sheet->getHighestDataRow();

        // Row 1 is headers — data starts at row 2
        for ($row = 2; $row <= $highestRow; $row++) {
            $code = trim((string) $sheet->getCell('B'.$row)->getValue());
            $description = trim((string) $sheet->getCell('C'.$row)->getValue());
            $causes = trim((string) $sheet->getCell('Q'.$row)->getValue());
            $remedies = trim((string) $sheet->getCell('S'.$row)->getValue());
            $severity = trim((string) $sheet->getCell('T'.$row)->getValue());

            if ($code === '' || $description === '') {
                $skipped++;

                continue;
            }

            $batch[] = [
                'code' => $code,
                'description' => $description,
                'possible_causes' => $causes !== '' ? $causes : null,
                'possible_remedies' => $remedies !== '' ? $remedies : null,
                'severity_estimate' => $severity !== '' ? $severity : null,
            ];

            if (count($batch) === 500) {
                DB::table('dtc_library')->insert($batch);
                $count += 500;
                $batch = [];
                $this->command->info("  {$count} rows imported...");
            }
        }

        if ($batch) {
            DB::table('dtc_library')->insert($batch);
            $count += count($batch);
        }

        $this->command->info("DTC Library seeder complete: {$count} records imported, {$skipped} rows skipped.");
    }
}
