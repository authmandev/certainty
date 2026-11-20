<?php

namespace App\Actions\Studies;

use App\Models\Study;
use App\Services\Studies\CalculateStudyProgressService;
use App\Services\Studies\StudyInputSchema;
use App\Services\Studies\StudyProgress;
use Illuminate\Support\Facades\DB;

class UpdateStudyDraftAction
{
    public function __construct(private CalculateStudyProgressService $progress) {}

    /**
     * Save the customer's draft input and refresh the stored completion percentage.
     *
     * @param  array<string, mixed>  $attributes  validated draft payload
     */
    public function handle(Study $study, array $attributes): StudyProgress
    {
        return DB::transaction(function () use ($study, $attributes): StudyProgress {
            $study->fill([
                'title' => $attributes['title'],
                'project_name' => $attributes['project_name'],
            ]);

            $input = StudyInputSchema::normalize($attributes);
            $progress = $this->progress->calculate($study, $input);

            $study->completion_percentage = $progress->percentage;
            $study->save();

            $study->input()->updateOrCreate([], [
                'input_data' => $input,
                'schema_version' => StudyInputSchema::VERSION,
            ]);

            return $progress;
        });
    }
}
