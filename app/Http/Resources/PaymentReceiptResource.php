<?php

namespace App\Http\Resources;

use App\Models\PaymentReceipt;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PaymentReceipt
 */
class PaymentReceiptResource extends JsonResource
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
            'file_name' => $this->file_name,
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'url' => route('receipts.show', $this->resource),
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],
            'submitted_at' => $this->submitted_at->toIso8601String(),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'reviewer' => $this->whenLoaded('reviewer', fn (): ?array => $this->reviewer ? ['name' => $this->reviewer->name] : null),
            'rejection_reason' => $this->rejection_reason,
        ];
    }
}
