<?php

namespace App\Http\Resources;

use App\Enums\PaymentStatus;
use App\Enums\StudyStatus;
use App\Models\StudyPayment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin StudyPayment
 */
class StudyPaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference_number' => $this->reference_number,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],
            'accepts_receipts' => $this->acceptsReceipts(),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'customer' => $this->whenLoaded('user', fn (): array => [
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),
            'study' => $this->whenLoaded('study', fn (): array => [
                'id' => $this->study->id,
                'reference' => $this->study->reference,
                'title' => $this->study->title,
                'status' => [
                    'value' => $this->study->currentStatus()->value,
                    'label' => $this->study->currentStatus()->label(),
                ],
                'completion_percentage' => $this->study->completion_percentage,
                'report_url' => $this->study->status === StudyStatus::Finalized && $this->status === PaymentStatus::Approved
                    ? route('studies.report', $this->study)
                    : null,
            ]),
            'latest_receipt' => $this->whenLoaded('latestReceipt', fn (): ?PaymentReceiptResource => $this->latestReceipt ? new PaymentReceiptResource($this->latestReceipt) : null),
            'receipts' => PaymentReceiptResource::collection($this->whenLoaded('receipts')),
        ];
    }
}
