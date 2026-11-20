<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Enums\ReceiptStatus;
use App\Models\PaymentReceipt;
use App\Models\StudyPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentReceipt>
 */
class PaymentReceiptFactory extends Factory
{
    /**
     * Define the model's default state: a receipt waiting for review.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'study_payment_id' => StudyPayment::factory()->state(['status' => PaymentStatus::UnderReview]),
            'uploaded_by' => fn (array $attributes) => StudyPayment::query()->whereKey($attributes['study_payment_id'])->value('user_id'),
            'disk' => 'local',
            'file_path' => fn () => 'receipts/'.fake()->uuid().'.pdf',
            'file_name' => 'receipt.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => fake()->numberBetween(10_000, 500_000),
            'status' => ReceiptStatus::Submitted,
            'submitted_at' => now(),
        ];
    }
}
