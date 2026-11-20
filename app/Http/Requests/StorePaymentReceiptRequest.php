<?php

namespace App\Http\Requests;

use App\Models\Study;
use App\Models\StudyPayment;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\File;

/**
 * Validates a bank-transfer receipt upload (BRD FR-020, NFR-003). The file
 * type is checked against both the extension and the file's contents.
 */
class StorePaymentReceiptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Returns the policy response itself so other customers get a 404, not a 403.
     */
    public function authorize(): Response
    {
        $payment = $this->payment();

        return $payment === null
            ? Response::denyAsNotFound()
            : Gate::inspect('submitReceipt', $payment);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $extensions = Config::array('certainty.receipts.extensions');

        return [
            'receipt' => [
                'required',
                File::types($extensions)
                    ->extensions($extensions)
                    ->max(Config::integer('certainty.receipts.max_kilobytes')),
            ],
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
            'receipt.mimes' => __('The receipt must be a JPG, PNG or PDF file.'),
            'receipt.extensions' => __('The receipt must be a JPG, PNG or PDF file.'),
            'receipt.max' => __('The receipt must not be larger than :size MB.', [
                'size' => (int) round(Config::integer('certainty.receipts.max_kilobytes') / 1024),
            ]),
        ];
    }

    /**
     * The payment of the study in the route.
     */
    public function payment(): ?StudyPayment
    {
        $study = $this->route('study');

        return $study instanceof Study ? $study->payment : null;
    }
}
