<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\MembershipType> */
class MembershipTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name_en' => fake()->unique()->words(2, true).' Member',
            'name_np' => 'सदस्य',
            'duration_unit' => 'years',
            'duration_value' => 5,
            'fee' => null,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    public function lifetime(): static
    {
        return $this->state(['duration_unit' => 'lifetime', 'duration_value' => null]);
    }

    public function paid(float $fee = 500): static
    {
        return $this->state(['fee' => $fee]);
    }
}
