<?php

namespace App\Http\Requests;

use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

/**
 * Finalization needs the customer's explicit confirmation (BRD FR-016).
 */
class FinalizeStudyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Returns the policy response itself so other customers get a 404, not a 403.
     */
    public function authorize(): Response
    {
        return Gate::inspect('finalize', $this->route('study'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'confirmed' => ['accepted'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'confirmed.accepted' => __('Please confirm that the study data is final.'),
        ];
    }
}
