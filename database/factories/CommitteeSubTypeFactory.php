<?php

namespace Database\Factories;

use App\Models\CommitteeType;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommitteeSubTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'committee_type_id' => CommitteeType::factory(),
            'name_np' => 'काठमाडौं',
            'name_en' => 'Kathmandu',
            'sort_order' => 0,
        ];
    }
}
