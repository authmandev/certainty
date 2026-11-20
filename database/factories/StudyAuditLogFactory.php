<?php

namespace Database\Factories;

use App\Enums\AuditAction;
use App\Models\Study;
use App\Models\StudyAuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudyAuditLog>
 */
class StudyAuditLogFactory extends Factory
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
            'actor_id' => User::factory()->admin(),
            'action' => AuditAction::DraftExtended,
            'old_values' => null,
            'new_values' => null,
            'reason' => null,
        ];
    }
}
