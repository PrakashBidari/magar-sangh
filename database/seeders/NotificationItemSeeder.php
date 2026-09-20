<?php

namespace Database\Seeders;

use App\Models\NotificationItem;
use Illuminate\Database\Seeder;

class NotificationItemSeeder extends Seeder
{
    public function run(): void
    {
        NotificationItem::factory(15)->create();
    }
}
