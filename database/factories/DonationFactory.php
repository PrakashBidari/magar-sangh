<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DonationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'donor_name' => fake()->name(),
            'donor_image_url' => fake()->boolean(60)
                ? 'https://i.pravatar.cc/150?img=' . fake()->numberBetween(1, 70)
                : null,
            'amount' => fake()->randomElement([500, 1000, 1500, 2000, 2500, 5000, 7500, 10000, 15000, 25000, 50000, 100000]),
            'address' => fake()->city() . ', Nepal',
            'donate_date' => fake()->dateTimeBetween('-2 years', 'now'),
        ];
    }
}
