<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class GalleryPhotoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'image_url' => 'https://picsum.photos/seed/gallery-' . fake()->unique()->numberBetween(1, 5000) . '/800/600',
            'category' => fake()->randomElement(['Cultural Festival', 'Committee Meeting', 'Dance Program', 'Community Event']),
        ];
    }
}
