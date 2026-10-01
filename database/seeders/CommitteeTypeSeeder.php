<?php

namespace Database\Seeders;

use App\Models\CommitteeType;
use Illuminate\Database\Seeder;

class CommitteeTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['केन्द्रीय समिति', 'Central Committee'],
            ['सल्लाहकार समिति', 'Advisory Committee'],
            ['महिला समिति', 'Women Committee'],
        ];

        foreach ($types as $i => [$np, $en]) {
            CommitteeType::create(['name_np' => $np, 'name_en' => $en, 'sort_order' => $i + 1]);
        }
    }
}
