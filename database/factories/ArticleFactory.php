<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ArticleFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(7);

        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . fake()->unique()->numberBetween(1000, 9999),
            'author' => fake()->name(),
            'excerpt' => fake()->sentence(15),
            'body' => '<p>' . fake()->paragraphs(6, true) . '</p>',
            'image_url' => 'https://picsum.photos/seed/article-' . fake()->unique()->numberBetween(1, 5000) . '/900/600',
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
