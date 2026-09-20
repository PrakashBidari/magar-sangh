<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class NotificationItemFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(6);

        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . fake()->unique()->numberBetween(1000, 9999),
            'body' => '<p>' . fake()->paragraphs(2, true) . '</p>',
            'published_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
