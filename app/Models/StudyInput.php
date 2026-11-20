<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\StudyInputFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The current editable input set of a study, following StudyInputSchema.
 *
 * @property int $id
 * @property int $study_id
 * @property array<string, mixed> $input_data
 * @property int $schema_version
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Study $study
 */
#[Fillable(['input_data', 'schema_version'])]
class StudyInput extends Model
{
    /** @use HasFactory<StudyInputFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'input_data' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Study, $this>
     */
    public function study(): BelongsTo
    {
        return $this->belongsTo(Study::class);
    }
}
