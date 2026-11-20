<?php

namespace Database\Factories;

use App\Models\Study;
use App\Models\StudyReport;
use App\Services\Studies\StudyInputSchema;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudyReport>
 */
class StudyReportFactory extends Factory
{
    /**
     * Define the model's default state: a report whose PDF is not generated yet.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'study_id' => Study::factory()->finalized(),
            'version' => 1,
            'input_snapshot' => StudyInputSchema::defaults(),
            'calculation_snapshot' => [],
            'calculation_version' => config('certainty.calculation_version'),
            'finalized_by' => fn (array $attributes) => Study::query()->whereKey($attributes['study_id'])->value('user_id'),
            'disk' => 'local',
            'pdf_path' => null,
            'generated_at' => null,
        ];
    }

    /**
     * A report whose PDF has been generated.
     */
    public function generated(): static
    {
        return $this->state(fn (array $attributes): array => [
            'pdf_path' => 'reports/'.fake()->uuid().'.pdf',
            'generated_at' => now(),
        ]);
    }
}
