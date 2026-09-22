<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement([Transaction::INCOME, Transaction::EXPENSE]);

        return [
            'date' => fake()->dateTimeBetween('-3 months', 'now'),
            'type' => $type,
            'category' => fake()->randomElement(config('accounting.categories.'.$type)),
            'description' => fake()->sentence(4),
            'amount' => fake()->randomElement([250, 500, 1200, 2500, 4000, 7500, 15000, 30000]),
            'payment_method' => fake()->randomElement(config('accounting.payment_methods')),
            'reference_no' => fake()->boolean(60) ? strtoupper(fake()->bothify('TX-####??')) : null,
            'remarks' => null,
        ];
    }

    public function income(): static
    {
        return $this->state(fn () => ['type' => Transaction::INCOME, 'category' => 'Donation']);
    }

    public function expense(): static
    {
        return $this->state(fn () => ['type' => Transaction::EXPENSE, 'category' => 'Rent']);
    }
}
