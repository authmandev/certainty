<?php

namespace Database\Factories;

use App\Enums\StudyStatus;
use App\Models\Study;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Study>
 */
class StudyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'reference' => fn () => 'CS-'.fake()->unique()->numerify('######'),
            'title' => fake()->sentence(3),
            'project_name' => fake()->company(),
            'status' => StudyStatus::Draft,
            'expires_at' => now()->addDays(config('certainty.draft_duration_days')),
            'calculation_version' => config('certainty.calculation_version'),
        ];
    }

    /**
     * Indicate that the draft deadline has passed.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }

    /**
     * Indicate that the study has been finalized.
     */
    public function finalized(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StudyStatus::Finalized,
            'finalized_at' => now(),
            'current_version' => 1,
        ]);
    }
}
