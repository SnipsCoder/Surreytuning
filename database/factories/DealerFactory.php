<?php

namespace Database\Factories;

use App\Enums\DealerStatus;
use App\Models\Dealer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dealer>
 */
class DealerFactory extends Factory
{
    protected $model = Dealer::class;

    public function definition(): array
    {
        return [
            'company_name' => fake()->company(),
            'country' => 'United Kingdom',
            'status' => DealerStatus::Approved->value,
            'approved_at' => now(),
        ];
    }
}
