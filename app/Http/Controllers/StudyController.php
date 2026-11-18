<?php

namespace App\Http\Controllers;

use App\Actions\Studies\CreateStudyAction;
use App\Http\Requests\StoreStudyRequest;
use App\Http\Resources\StudyResource;
use App\Models\Study;
use Illuminate\Http\RedirectResponse;
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

        return to_route('studies.show', $study);
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
}
