<?php

namespace Database\Seeders;

use App\Models\HeroSlide;
use Illuminate\Database\Seeder;

class HeroSlideSeeder extends Seeder
{
    public function run(): void
    {
        foreach (range(0, 2) as $i) {
            HeroSlide::firstOrCreate(
                ['image_url' => "https://picsum.photos/seed/hero-{$i}/1600/900"],
                [
                    'title_np' => 'हाम्रो भाषा, हाम्रो संस्कृति, हाम्रो पहिचान – हाम्रो गौरव',
                    'title_en' => 'Our Language, Our Culture, Our Identity – Our Pride',
                    'sort_order' => $i,
                    'is_active' => true,
                ]
            );
        }
    }
}
