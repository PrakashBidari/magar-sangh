<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        Event::factory()->create([
            'title' => 'Nepal Magar Cultural Festival 2083',
            'slug' => Str::slug('Nepal Magar Cultural Festival 2083'),
            'location' => 'Kathmandu, Nepal',
            'event_date' => '2026-10-15',
            'event_time' => '10:00:00',
            'image_url' => 'https://picsum.photos/seed/event-festival/900/600',
        ]);

        Event::factory(11)->create();
    }
}
