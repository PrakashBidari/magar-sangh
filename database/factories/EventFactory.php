<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EventFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . fake()->unique()->numberBetween(1000, 9999),
            'description' => '<p>' . fake()->paragraphs(4, true) . '</p>',
            'image_url' => 'https://picsum.photos/seed/event-' . fake()->unique()->numberBetween(1, 5000) . '/900/600',
            'location' => fake()->randomElement(['Kathmandu, Nepal', 'Pokhara, Nepal', 'Butwal, Nepal', 'Palpa, Nepal']),
            'event_date' => fake()->dateTimeBetween('-2 months', '+6 months'),
            'event_time' => fake()->time('H:i:s'),
        ];
    }
}
