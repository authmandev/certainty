<?php

namespace App\Http\Resources;

use App\Models\StudyReport;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin StudyReport
 */
class StudyReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'version' => $this->version,
            'is_generated' => $this->pdf_path !== null,
            'finalized_at' => $this->created_at?->toIso8601String(),
            'generated_at' => $this->generated_at?->toIso8601String(),
            'view_url' => route('studies.report', ['study' => $this->study_id, 'version' => $this->version]),
            'download_url' => route('studies.report', ['study' => $this->study_id, 'version' => $this->version, 'download' => 1]),
        ];
    }
}
