<?php

namespace Database\Factories;

use App\Enums\FileRequestStatus;
use App\Enums\FuelType;
use App\Enums\TransmissionType;
use App\Models\Dealer;
use App\Models\FileRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FileRequest>
 */
class FileRequestFactory extends Factory
{
    protected $model = FileRequest::class;

    public function definition(): array
    {
        return [
            'request_number' => fake()->unique()->numberBetween(1, 999999),
            'dealer_id' => Dealer::factory(),
            'submitted_by_user_id' => User::factory(),
            'file_type' => 'ecu',
            'status' => FileRequestStatus::Pending->value,
            'make' => fake()->randomElement(['BMW', 'Audi', 'Volkswagen']),
            'model' => fake()->word(),
            'engine' => '2.0 TDI',
            'year' => fake()->year(),
            'fuel' => FuelType::Diesel->value,
            'transmission' => TransmissionType::Manual->value,
        ];
    }
}
