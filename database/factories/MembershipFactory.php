<?php

namespace Database\Factories;

use App\Models\MembershipType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Membership> */
class MembershipFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'membership_type_id' => MembershipType::factory(),
            'status' => 'pending',
            'full_name' => fake()->firstName(),
            'surname' => 'Magar',
            'date_of_birth' => fake()->dateTimeBetween('-60 years', '-18 years'),
            'permanent_address' => fake()->streetAddress(),
            'current_address' => fake()->streetAddress(),
            'province' => 'Lumbini',
            'district' => 'Rolpa',
            'municipality' => 'Rolpa Municipality',
            'ward_no' => (string) fake()->numberBetween(1, 15),
            'mobile' => '98'.fake()->numerify('########'),
            'email' => fake()->safeEmail(),
            'occupation' => 'Teacher',
            'photo_url' => 'https://picsum.photos/seed/'.fake()->unique()->numberBetween(1, 9999).'/300/380',
            'applied_at' => today(),
        ];
    }

    public function approved(): static
    {
        return $this->afterCreating(fn ($membership) => $membership->load('type')->approve(User::factory()->create()));
    }
}
