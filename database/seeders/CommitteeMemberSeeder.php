<?php

namespace Database\Seeders;

use App\Models\CommitteeMember;
use Illuminate\Database\Seeder;

class CommitteeMemberSeeder extends Seeder
{
    public function run(): void
    {
        CommitteeMember::factory()->create([
            'name' => 'कुल बहादुर थापा मगर',
            'position_np' => 'अध्यक्ष',
            'position_en' => 'President',
            'is_current' => true,
            'is_past_president' => false,
            'sort_order' => 0,
            'photo_url' => 'https://i.pravatar.cc/300?img=52',
        ]);

        $positions = ['वरिष्ठ उपाध्यक्ष', 'उपाध्यक्ष', 'महासचिव', 'सचिव', 'कोषाध्यक्ष', 'सह-कोषाध्यक्ष'];
        foreach ($positions as $i => $position) {
            CommitteeMember::factory()->create([
                'position_np' => $position,
                'is_current' => true,
                'is_past_president' => false,
                'sort_order' => $i + 1,
            ]);
        }

        CommitteeMember::factory(10)->create(['is_current' => true, 'is_past_president' => false]);

        for ($i = 0; $i < 7; $i++) {
            CommitteeMember::factory()->pastPresident($i)->create();
        }
    }
}
