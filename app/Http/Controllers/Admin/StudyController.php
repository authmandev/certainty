<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Studies\ExtendStudyAction;
use App\Actions\Studies\ReopenStudyAction;
use App\Enums\PaymentStatus;
use App\Enums\StudyStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExtendStudyRequest;
use App\Http\Requests\Admin\ReopenStudyRequest;
use App\Http\Resources\AuditLogResource;
use App\Http\Resources\StudyPaymentResource;
use App\Http\Resources\StudyReportResource;
use App\Http\Resources\StudyResource;
use App\Models\ReferenceOption;
use App\Models\Study;
use App\Services\Calculation\FeasibilityCalculationService;
use App\Services\Studies\CalculateStudyProgressService;
use App\Services\Studies\StudyInputSchema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class StudyController extends Controller
{
    /**
     * List every study with status, payment and text filters (BRD FR-039).
     */
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(StudyStatus::class)],
            'payment' => ['nullable', Rule::enum(PaymentStatus::class)],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $studies = Study::query()
            ->with(['user', 'payment'])
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $this->whereStatus($query, StudyStatus::from($status)))
            ->when($filters['payment'] ?? null, fn (Builder $query, string $payment) => $query->whereHas('payment', fn (Builder $query) => $query->where('status', $payment)))
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where(fn (Builder $query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('project_name', 'like', "%{$search}%")
                ->orWhere('reference', 'like', "%{$search}%")
                ->orWhereHas('user', fn (Builder $query) => $query->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))))
            ->latest('updated_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/studies/Index', [
            'studies' => StudyResource::collection($studies),
            'filters' => [
                'status' => $filters['status'] ?? null,
                'payment' => $filters['payment'] ?? null,
                'search' => $filters['search'] ?? null,
            ],
            'statuses' => collect(StudyStatus::cases())->map(fn (StudyStatus $status): array => ['value' => $status->value, 'label' => $status->label()]),
            'paymentStatuses' => collect(PaymentStatus::cases())->map(fn (PaymentStatus $status): array => ['value' => $status->value, 'label' => $status->label()]),
        ]);
    }

    /**
     * Inspect a study read-only: its input, live results, payment, report
     * versions, extensions and action history.
     */
    public function show(Study $study, CalculateStudyProgressService $progress, FeasibilityCalculationService $calculator): Response
    {
        $study->load(['user', 'payment.receipts.reviewer', 'input', 'reports', 'extensions.approver', 'auditLogs.actor']);
        $input = StudyInputSchema::normalize($study->input->input_data ?? []);

        return Inertia::render('admin/studies/Show', [
            'study' => new StudyResource($study),
            'customer' => ['id' => $study->user->id, 'name' => $study->user->name, 'email' => $study->user->email],
            'timestamps' => [
                'created_at' => $study->created_at?->toIso8601String(),
                'expires_at' => $study->expires_at->toIso8601String(),
                'finalized_at' => $study->finalized_at?->toIso8601String(),
            ],
            'input' => $input,
            'progress' => $progress->calculate($study, $input),
            'labels' => ReferenceOption::query()->get(['group', 'code', 'label_ar'])
                ->groupBy(fn (ReferenceOption $option): string => $option->group->value)
                ->map(fn ($options) => $options->pluck('label_ar', 'code')),
            'preview' => fn (): ?array => $calculator->preview($study),
            'payment' => $study->payment ? new StudyPaymentResource($study->payment) : null,
            'reports' => StudyReportResource::collection($study->reports),
            'extensions' => $study->extensions->map(fn ($extension): array => [
                'id' => $extension->id,
                'previous_expires_at' => $extension->previous_expires_at->toIso8601String(),
                'new_expires_at' => $extension->new_expires_at->toIso8601String(),
                'extension_days' => $extension->extension_days,
                'reason' => $extension->reason,
                'approver' => $extension->approver->name,
                'created_at' => $extension->created_at?->toIso8601String(),
            ]),
            'auditLogs' => AuditLogResource::collection($study->auditLogs),
            'canExtend' => $study->status !== StudyStatus::Finalized,
            'canReopen' => $study->status === StudyStatus::Finalized,
        ]);
    }

    /**
     * Extend a draft's deadline by the number of days the administrator enters.
     */
    public function extend(ExtendStudyRequest $request, Study $study, ExtendStudyAction $extend): RedirectResponse
    {
        $extend->handle($study, $request->user(), $request->integer('days'), $request->string('reason')->trim()->toString());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Study extended.')]);

        return to_route('admin.studies.show', $study);
    }

    /**
     * Reopen a finalized study for correction.
     */
    public function reopen(ReopenStudyRequest $request, Study $study, ReopenStudyAction $reopen): RedirectResponse
    {
        $reopen->handle($study, $request->user(), $request->integer('days'), $request->string('reason')->trim()->toString());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Study reopened. The customer can edit it again.')]);

        return to_route('admin.studies.show', $study);
    }

    /**
     * Filter by the status customers see: a draft past its deadline counts as expired.
     *
     * @param  Builder<Study>  $query
     * @return Builder<Study>
     */
    private function whereStatus(Builder $query, StudyStatus $status): Builder
    {
        return match ($status) {
            StudyStatus::Draft => $query->where('status', StudyStatus::Draft)->where('expires_at', '>', now()),
            StudyStatus::Expired => $query->where(fn (Builder $query) => $query
                ->where('status', StudyStatus::Expired)
                ->orWhere(fn (Builder $query) => $query->where('status', StudyStatus::Draft)->where('expires_at', '<=', now()))),
            StudyStatus::Finalized => $query->where('status', StudyStatus::Finalized),
        };
    }
}
