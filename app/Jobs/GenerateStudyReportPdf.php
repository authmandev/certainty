<?php

namespace App\Jobs;

use App\Models\StudyReport;
use App\Services\Reports\StudyReportPdfGenerator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Renders a finalized report's PDF in the background. The download route
 * renders it on demand too, so a failed or slow job never blocks access.
 */
class GenerateStudyReportPdf implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    /**
     * @var list<int>
     */
    public array $backoff = [10, 60];

    public function __construct(public StudyReport $report) {}

    /**
     * Execute the job.
     */
    public function handle(StudyReportPdfGenerator $generator): void
    {
        $generator->ensureGenerated($this->report);
    }
}
