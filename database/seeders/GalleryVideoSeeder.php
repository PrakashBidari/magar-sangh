<?php

namespace Database\Seeders;

use App\Models\GalleryVideo;
use Illuminate\Database\Seeder;

class GalleryVideoSeeder extends Seeder
{
    public function run(): void
    {
        GalleryVideo::factory(9)->create();
    }
}
