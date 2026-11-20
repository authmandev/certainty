<?php

namespace App\Services\Reports;

use App\Models\StudyReport;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\LaravelPdf\Enums\Format;
use Spatie\LaravelPdf\Facades\Pdf;

/**
 * Renders a report version to PDF once and stores it privately (BRD FR-034,
 * FR-035). The content comes only from the report's snapshots, so the same
 * version always produces the same report.
 */
class StudyReportPdfGenerator
{
    public function __construct(private StudyReportPresenter $presenter) {}

    /**
     * Generate the PDF unless it already exists. A lock keeps the queued job
     * and an on-demand download from rendering the same report twice.
     */
    public function ensureGenerated(StudyReport $report): StudyReport
    {
        if ($report->pdf_path !== null) {
            return $report;
        }

        return Cache::lock("study-report-pdf:{$report->id}", 180)->block(120, function () use ($report): StudyReport {
            $report->refresh();

            if ($report->pdf_path !== null) {
                return $report;
            }

            $report->loadMissing('study.user');
            $path = "reports/{$report->study_id}/".Str::uuid7().'.pdf';

            $content = Pdf::view('reports.study', $this->presenter->present($report))
                ->format(Format::A4)
                ->margins(top: 14, right: 12, bottom: 16, left: 12, unit: 'mm')
                ->footerView('reports.footer', [
                    'reference' => $report->study->reference,
                    'version' => $report->version,
                ])
                ->meta(
                    title: $report->study->title,
                    author: (string) config('certainty.reports.company_name'),
                    subject: 'دراسة جدوى اقتصادية',
                )
                ->generatePdfContent();

            Storage::disk($report->disk)->put($path, $content);

            $report->forceFill(['pdf_path' => $path, 'generated_at' => now()])->save();

            return $report;
        });
    }
}
