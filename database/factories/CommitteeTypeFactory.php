<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CommitteeTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name_np' => 'केन्द्रीय समिति',
            'name_en' => 'Central Committee',
            'sort_order' => 0,
        ];
    }
}
