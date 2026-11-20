<?php

namespace App\Http\Requests;

use App\Enums\ReferenceGroup;
use App\Services\Studies\StudyInputSchema;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Validator;

/**
 * Validates a draft save. Every input field is optional so partial drafts can
 * be saved (BRD FR-010); values that are present must have the right type and
 * range (FR-012). Completeness is judged separately by the progress rules.
 */
class UpdateStudyDraftRequest extends FormRequest
{
    private const int MAX_AMOUNT = 1_000_000_000_000;

    private const int MAX_TEXT = 5000;

    /**
     * Determine if the user is authorized to make this request.
     *
     * Returns the policy response itself so other customers get a 404, not a 403.
     */
    public function authorize(): Response
    {
        return Gate::inspect('update', $this->route('study'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $amount = ['nullable', 'numeric', 'min:0', 'max:'.self::MAX_AMOUNT];
        $percent = ['nullable', 'numeric', 'min:0', 'max:100'];
        $text = ['nullable', 'string', 'max:'.self::MAX_TEXT];
        $name = ['nullable', 'string', 'max:255'];
        $rowKey = ['nullable', 'string', 'max:36'];

        return [
            'title' => ['required', 'string', 'max:255'],
            'project_name' => ['required', 'string', 'max:255'],

            'settings' => ['required', 'array'],
            'settings.currency' => ['nullable', Rule::in(array_keys(config('certainty.study_currencies')))],
            'settings.decimals' => ['nullable', 'integer', Rule::in(StudyInputSchema::DECIMAL_PLACES)],
            'settings.vat_percent' => ['nullable', 'integer', 'min:0', 'max:'.StudyInputSchema::MAX_VAT_PERCENT],
            'settings.study_years' => ['nullable', 'integer', Rule::in(StudyInputSchema::STUDY_YEARS)],

            'project' => ['required', 'array'],
            'project.start_date' => ['nullable', 'date'],
            'project.description' => $text,
            'project.organizational_feasibility' => $text,
            'project.sector_code' => ['nullable', 'string', $this->referenceCode(ReferenceGroup::Sector)],
            'project.project_type_code' => ['nullable', 'string', $this->referenceCode(ReferenceGroup::ProjectType)],
            'project.project_type_other' => $name,

            'owners' => ['present', 'array', 'max:20'],
            'owners.*.key' => $rowKey,
            'owners.*.name' => $name,
            'owners.*.share' => $percent,
            'owners.*.experience' => $text,

            'market' => ['required', 'array'],
            'market.name' => $name,
            'market.size' => $amount,
            'market.description' => $text,
            'market.products' => $text,
            'market.target_customers' => $text,
            'market.suppliers' => $text,
            'market.marketing_plan' => $text,
            'market.competition_analysis' => $text,

            'competitors' => ['present', 'array', 'max:30'],
            'competitors.*.key' => $rowKey,
            'competitors.*.name' => $name,
            'competitors.*.share' => $percent,
            'competitors.*.characteristics' => $text,

            'assets' => ['present', 'array', 'max:50'],
            'assets.*.key' => $rowKey,
            'assets.*.category_code' => ['nullable', 'string', $this->referenceCode(ReferenceGroup::AssetCategory)],
            'assets.*.name' => $name,
            'assets.*.value' => $amount,
            'assets.*.life_span' => ['nullable', 'integer', 'min:0', 'max:100'],
            'assets.*.depreciation_rate' => $percent,
            'assets.*.includes_vat' => ['boolean'],

            'loans' => ['present', 'array', 'max:20'],
            'loans.*.key' => $rowKey,
            'loans.*.lender' => $name,
            'loans.*.amount' => $amount,
            'loans.*.interest_rate' => $percent,
            'loans.*.years' => ['nullable', 'integer', 'min:1', 'max:50'],

            'fixed_costs' => ['present', 'array', 'max:50'],
            'fixed_costs.*.key' => $rowKey,
            'fixed_costs.*.category_code' => ['nullable', 'string', $this->referenceCode(ReferenceGroup::FixedCostCategory)],
            'fixed_costs.*.name' => $name,
            'fixed_costs.*.monthly_amount' => $amount,
            'fixed_costs.*.includes_vat' => ['boolean'],

            'employees' => ['present', 'array', 'max:50'],
            'employees.*.key' => $rowKey,
            'employees.*.job_code' => ['nullable', 'string', $this->referenceCode(ReferenceGroup::JobCategory)],
            'employees.*.title' => $name,
            'employees.*.count' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'employees.*.monthly_salary' => $amount,

            'products' => ['present', 'array', 'max:50'],
            'products.*.key' => $rowKey,
            'products.*.name' => $name,
            'products.*.unit_cost' => $amount,
            'products.*.unit_price' => $amount,
            'products.*.monthly_quantity' => $amount,
            'products.*.monthly_capacity' => $amount,
            'products.*.annual_growth' => ['nullable', 'numeric', 'min:-100', 'max:1000'],

            'swot' => ['required', 'array'],
            'swot.strengths' => $text,
            'swot.weaknesses' => $text,
            'swot.opportunities' => $text,
            'swot.threats' => $text,
        ];
    }

    /**
     * Get the "after" validation callables for the request.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $totalShare = $this->collect('owners')->sum(fn (mixed $owner): float => is_array($owner) && is_numeric($owner['share'] ?? null) ? (float) $owner['share'] : 0);

                if ($totalShare > 100) {
                    $validator->errors()->add('owners', __('Owner shares must not exceed 100% in total.'));
                }
            },
        ];
    }

    private function referenceCode(ReferenceGroup $group): Exists
    {
        return Rule::exists('reference_options', 'code')
            ->where('group', $group->value)
            ->where('is_active', true);
    }
}
