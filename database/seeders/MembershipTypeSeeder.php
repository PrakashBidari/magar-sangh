<?php

namespace Database\Seeders;

use App\Models\MembershipType;
use Illuminate\Database\Seeder;

class MembershipTypeSeeder extends Seeder
{
    /** Starting set; durations and fees are edited from Dashboard > Membership > Membership Types. */
    public function run(): void
    {
        $types = [
            ['Central Committee Member', 'केन्द्रीय समिति सदस्य', 'years', 5],
            ['Province Member', 'प्रदेश सदस्य', 'years', 5],
            ['Special Member', 'विशेष सदस्य', 'years', 5],
            ['Lifetime Member', 'आजीवन सदस्य', MembershipType::LIFETIME, null],
            ['District Member', 'जिल्ला सदस्य', 'years', 5],
            ['Municipality Member', 'नगरपालिका सदस्य', 'years', 5],
        ];

        foreach ($types as $i => [$en, $np, $unit, $value]) {
            MembershipType::firstOrCreate(
                ['name_en' => $en],
                ['name_np' => $np, 'duration_unit' => $unit, 'duration_value' => $value, 'sort_order' => $i + 1, 'is_active' => true]
            );
        }
    }
}
