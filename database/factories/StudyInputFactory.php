<?php

namespace Database\Factories;

use App\Models\Study;
use App\Models\StudyInput;
use App\Services\Studies\StudyInputSchema;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudyInput>
 */
class StudyInputFactory extends Factory
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
            'input_data' => StudyInputSchema::defaults(),
            'schema_version' => StudyInputSchema::VERSION,
        ];
    }
}
