<?php

namespace Database\Factories;

use App\Models\Study;
use App\Models\StudyExtension;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudyExtension>
 */
class StudyExtensionFactory extends Factory
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
            'previous_expires_at' => now(),
            'extension_days' => 14,
            'new_expires_at' => now()->addDays(14),
            'reason' => 'طلب العميل وقتًا إضافيًا.',
            'approved_by' => User::factory()->admin(),
        ];
    }
}
