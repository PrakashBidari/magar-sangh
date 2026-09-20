<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SisterOrganizationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name_np' => 'नेपाल मगर संघ',
            'name_en' => fake()->company(),
            'logo_url' => 'https://placehold.co/160x160/001F5B/FFFFFF?text=Logo',
            'blurb' => fake()->sentence(18),
            'link' => fake()->url(),
            'sort_order' => fake()->numberBetween(1, 10),
        ];
    }
}
