<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OfficeAssistantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'photo_url' => 'https://i.pravatar.cc/300?u='.fake()->unique()->uuid(),
            'phone' => '98'.fake()->numerify('########'),
            'email' => fake()->safeEmail(),
            'sort_order' => 0,
        ];
    }
}
