<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Study;
use App\Models\StudyPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudyPayment>
 */
class StudyPaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'study_id' => Study::factory(),
            'user_id' => fn (array $attributes) => Study::query()->whereKey($attributes['study_id'])->value('user_id'),
            'reference_number' => fn () => 'PAY-'.fake()->unique()->numerify('######'),
            'amount' => config('certainty.study_price'),
            'currency' => config('certainty.currency'),
            'status' => PaymentStatus::Pending,
        ];
    }
}
