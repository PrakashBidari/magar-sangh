<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CommitteeMemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name('male'),
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
        ]);
    }

    private function toNepaliNumber(int $number): string
    {
        $map = ['0', '१', '२', '३', '४', '५', '६', '७', '८', '९'];

        return collect(str_split((string) $number))->map(fn ($d) => $map[(int) $d])->implode('');
    }
}
