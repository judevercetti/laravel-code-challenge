<?php

namespace Database\Factories;

use App\Models\Loan;
use App\Models\ReceivedRepayment;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReceivedRepaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ReceivedRepayment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $amount = $this->faker->numberBetween(1000, 10000);

        return [
            'amount' => $amount,
            'currency_code' => Loan::CURRENCY_VND,
            'received_at' => $this->faker->date(),
            'loan_id' => fn () => Loan::factory()->create(),
        ];
    }
}

