<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id' => \App\Models\Booking::factory(),
            'payment_method' => $this->faker->randomElement(['credit_card', 'debit_card', 'paypal', 'bank_transfer']),
            'amount_paid' => $this->faker->randomFloat(2, 100, 2000),
            'payment_date' => $this->faker->dateTimeThisYear(),
            'status' => $this->faker->randomElement(['paid', 'failed', 'refunded']),
            'transaction_id' => $this->faker->uuid(),
        ];
    }
}
