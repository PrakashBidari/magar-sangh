<?php

namespace Database\Factories;

use App\Models\CommitteeType;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommitteeMemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'committee_type_id' => CommitteeType::factory(),
            'name' => fake()->name('male'),
            'phone' => '98'.fake()->numerify('########'),
            'photo_url' => 'https://i.pravatar.cc/300?img=' . fake()->numberBetween(1, 70),
            'position_np' => fake()->randomElement(['केन्द्रीय सदस्य', 'सचिव', 'कोषाध्यक्ष', 'उपाध्यक्ष']),
            'position_en' => fake()->randomElement(['Central Member', 'Secretary', 'Treasurer', 'Vice President']),
            'term_label' => null,
            'term_start' => null,
            'term_end' => null,
            'is_past_president' => false,
            'is_current' => true,
            'sort_order' => fake()->numberBetween(1, 50),
        ];
    }

    /** A former member of the committee, with a random past term. */
    public function past(): self
    {
        return $this->state(function () {
            $startYear = fake()->numberBetween(2065, 2075);

            return [
                'term_label' => $this->toNepaliNumber($startYear) . ' – ' . $this->toNepaliNumber($startYear + 4),
                'is_current' => false,
            ];
        });
    }

    public function pastPresident(int $index = 0): self
    {
        $startYear = 2060 - ($index * 3);
        $endYear = $startYear + 2;

        return $this->state(fn () => [
            'position_np' => 'पूर्व अध्यक्ष',
            'position_en' => 'Former President',
            'term_label' => $this->toNepaliNumber($startYear) . ' – ' . $this->toNepaliNumber($endYear),
            'is_past_president' => true,
            'is_current' => false,
            'show_on_homepage' => true,
        ]);
    }

    private function toNepaliNumber(int $number): string
    {
        $map = ['0', '१', '२', '३', '४', '५', '६', '७', '८', '९'];

        return collect(str_split((string) $number))->map(fn ($d) => $map[(int) $d])->implode('');
    }
}
