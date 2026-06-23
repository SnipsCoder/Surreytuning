<?php

namespace Database\Seeders;

use App\Models\OpeningHour;
use Illuminate\Database\Seeder;

class OpeningHoursSeeder extends Seeder
{
    public function run(): void
    {
        for ($day = 0; $day <= 6; $day++) {
            $isOpen = $day <= 4;

            OpeningHour::create([
                'day_of_week' => $day,
                'open_time' => '09:00:00',
                'close_time' => '17:30:00',
                'is_open' => $isOpen,
            ]);
        }
    }
}
