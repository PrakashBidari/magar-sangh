<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class GalleryVideoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'youtube_embed_url' => 'https://www.youtube.com/embed/aqz-KE-bpKQ',
            'thumbnail_url' => 'https://img.youtube.com/vi/aqz-KE-bpKQ/hqdefault.jpg',
        ];
    }
}
