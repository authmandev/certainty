<?php

namespace App\Http\Controllers;

use App\Actions\Studies\CreateStudyAction;
use App\Actions\Studies\UpdateStudyDraftAction;
use App\Enums\ReferenceGroup;
use App\Http\Requests\StoreStudyRequest;
use App\Http\Requests\UpdateStudyDraftRequest;
use App\Http\Resources\StudyResource;
use App\Models\ReferenceOption;
use App\Models\Study;
use App\Services\Calculation\FeasibilityCalculationService;
use App\Services\Studies\CalculateStudyProgressService;
use App\Services\Studies\StudyInputSchema;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class StudyController extends Controller
{
    /**
     * Show the form for starting a new study.
     */
    public function create(): Response
    {
        Gate::authorize('create', Study::class);

        return Inertia::render('studies/Create', [
            'price' => (float) config('certainty.study_price'),
            'currency' => config('certainty.currency'),
            'draftDurationDays' => (int) config('certainty.draft_duration_days'),
        ]);
    }

    /**
     * Create a draft study and its pending payment.
     */
    public function store(StoreStudyRequest $request, CreateStudyAction $createStudy): RedirectResponse
    {
        /** @var array{title: string, project_name: string} $attributes */
        $attributes = $request->validated();

        $study = $createStudy->handle($request->user(), $attributes);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Study created.')]);

        return to_route('studies.edit', $study);
    }

    /**
     * Show a study owned by the current customer.
     */
    public function show(Study $study): Response
    {
        Gate::authorize('view', $study);

        return Inertia::render('studies/Show', [
            'study' => new StudyResource($study->load('payment')),
        ]);
    }

    /**
     * Show the multi-step editor for a draft study.
     */
    public function edit(Study $study, CalculateStudyProgressService $progress, FeasibilityCalculationService $calculator): Response|RedirectResponse
    {
        Gate::authorize('view', $study);

        if (! $study->isEditable()) {
            Inertia::flash('toast', ['type' => 'error', 'message' => __('This study can no longer be edited.')]);

            return to_route('studies.show', $study);
        }

        $study->load(['payment', 'input']);
        $input = StudyInputSchema::normalize($study->input->input_data ?? []);

        return Inertia::render('studies/Edit', [
            'study' => new StudyResource($study),
            'input' => $input,
            'progress' => $progress->calculate($study, $input),
            'preview' => fn (): ?array => $calculator->preview($study),
            'options' => $this->formOptions(),
        ]);
    }

    /**
     * Save the draft. Saving never finalizes or locks the study; the editor
     * reloads the progress and the live preview afterwards.
     */
    public function update(UpdateStudyDraftRequest $request, Study $study, UpdateStudyDraftAction $updateDraft): RedirectResponse
    {
        $updateDraft->handle($study, $request->validated());

        return back();
    }

    /**
     * Dropdown values for the study editor.
     *
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        $references = ReferenceOption::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['group', 'code', 'label_ar'])
            ->groupBy(fn (ReferenceOption $option): string => $option->group->value)
            ->map(fn ($options) => $options->map(fn (ReferenceOption $option): array => [
                'value' => $option->code,
                'label' => $option->label_ar,
            ])->values());

        return [
            'currencies' => collect(Config::array('certainty.study_currencies'))
                ->map(fn (string $label, string $code): array => ['value' => $code, 'label' => "{$label} ({$code})"])
                ->values(),
            'decimals' => StudyInputSchema::DECIMAL_PLACES,
            'vatPercents' => range(0, StudyInputSchema::MAX_VAT_PERCENT),
            'studyYears' => StudyInputSchema::STUDY_YEARS,
            'sectors' => $references->get(ReferenceGroup::Sector->value, []),
            'projectTypes' => $references->get(ReferenceGroup::ProjectType->value, []),
            'assetCategories' => $references->get(ReferenceGroup::AssetCategory->value, []),
            'fixedCostCategories' => $references->get(ReferenceGroup::FixedCostCategory->value, []),
            'jobCategories' => $references->get(ReferenceGroup::JobCategory->value, []),
        ];
    }
}
