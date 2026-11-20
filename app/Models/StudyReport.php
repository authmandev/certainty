<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\StudyReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An immutable version of a finalized study: the input and calculation
 * snapshots the report was built from, and its PDF (BRD FR-036). The PDF is
 * generated after finalization, so `pdf_path` stays null until it exists.
 *
 * @property int $id
 * @property int $study_id
 * @property int $version
 * @property array<string, mixed> $input_snapshot
 * @property array<string, mixed> $calculation_snapshot
 * @property string $calculation_version
 * @property int $finalized_by
 * @property string $disk
 * @property string|null $pdf_path
 * @property CarbonImmutable|null $generated_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Study $study
 * @property-read User $finalizer
 */
class StudyReport extends Model
{
    /** @use HasFactory<StudyReportFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'input_snapshot' => 'array',
            'calculation_snapshot' => 'array',
            'generated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Study, $this>
     */
    public function study(): BelongsTo
    {
        return $this->belongsTo(Study::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function finalizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    /**
     * The file name customers see when downloading, such as CS-000042-v1.pdf.
     */
    public function downloadName(): string
    {
        return "{$this->study->reference}-v{$this->version}.pdf";
    }
}
