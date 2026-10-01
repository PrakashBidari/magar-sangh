<?php

namespace Database\Seeders;

use App\Models\CommitteeMember;
use App\Models\CommitteeType;
use Illuminate\Database\Seeder;

class CommitteeMemberSeeder extends Seeder
{
    public function run(): void
    {
        [$central, $advisory, $women] = CommitteeType::orderBy('sort_order')->take(3)->get();

        // Central committee: present office bearers and members...
        CommitteeMember::factory()->for($central)->create([
            'name' => 'कुल बहादुर थापा मगर',
            'position_np' => 'अध्यक्ष',
            'position_en' => 'President',
            'sort_order' => 0,
            'photo_url' => 'https://i.pravatar.cc/300?img=52',
            'show_on_homepage' => true,
        ]);

        $positions = ['वरिष्ठ उपाध्यक्ष', 'उपाध्यक्ष', 'महासचिव', 'सचिव', 'कोषाध्यक्ष', 'सह-कोषाध्यक्ष'];
        foreach ($positions as $i => $position) {
            CommitteeMember::factory()->for($central)->create([
                'position_np' => $position,
                'position_en' => null,
                'sort_order' => $i + 1,
            ]);
        }

        CommitteeMember::factory(6)->for($central)->create(['position_np' => 'केन्द्रीय सदस्य', 'position_en' => 'Central Member']);

        // ...and past presidents (shown on the homepage).
        for ($i = 0; $i < 7; $i++) {
            CommitteeMember::factory()->for($central)->pastPresident($i)->create(['sort_order' => 100 + $i]);
        }

        // Advisory committee: present and past advisors.
        CommitteeMember::factory(4)->for($advisory)->create(['position_np' => 'सल्लाहकार', 'position_en' => 'Advisor']);
        CommitteeMember::factory(3)->for($advisory)->past()->create(['position_np' => 'सल्लाहकार', 'position_en' => 'Advisor']);

        // Women committee: present and past members.
        CommitteeMember::factory()->for($women)->create(['name' => fn () => fake()->name('female'), 'position_np' => 'संयोजक', 'position_en' => 'Coordinator', 'sort_order' => 0]);
        CommitteeMember::factory(4)->for($women)->create(['name' => fn () => fake()->name('female'), 'position_np' => 'सदस्य', 'position_en' => 'Member']);
        CommitteeMember::factory(2)->for($women)->past()->create(['name' => fn () => fake()->name('female'), 'position_np' => 'संयोजक', 'position_en' => 'Coordinator']);
    }
}
