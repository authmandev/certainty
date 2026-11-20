<?php

namespace App\Http\Controllers;

use App\Actions\Studies\FinalizeStudyAction;
use App\Http\Requests\FinalizeStudyRequest;
use App\Models\Study;
use App\Services\Reports\StudyReportPdfGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class StudyReportController extends Controller
{
    /**
     * Finalize the study after the customer's explicit confirmation.
     */
    public function store(FinalizeStudyRequest $request, Study $study, FinalizeStudyAction $finalize): RedirectResponse
    {
        $finalize->handle($study, $request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Study finalized. Your report is being prepared.')]);

        return to_route('studies.show', $study);
    }

    /**
     * Stream a report version's PDF, rendering it first if the queued job has
     * not finished yet. Defaults to the latest version.
     */
    public function show(Request $request, Study $study, StudyReportPdfGenerator $generator): StreamedResponse|RedirectResponse
    {
        Gate::authorize('downloadReport', $study->load('payment'));

        $version = $request->integer('version') ?: $study->current_version;
        $report = $study->reports()->where('version', $version)->firstOrFail();

        try {
            $report = $generator->ensureGenerated($report);
        } catch (Throwable $exception) {
            report($exception);
            Inertia::flash('toast', ['type' => 'error', 'message' => __('The report could not be generated right now. Please try again in a few minutes.')]);

            return back();
        }

        /** @var string $path */
        $path = $report->pdf_path;
        $disposition = $request->boolean('download') ? 'attachment' : 'inline';

        return Storage::disk($report->disk)->response($path, $report->downloadName(), [
            'Content-Type' => 'application/pdf',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ], $disposition);
    }
}
