<?php

namespace App\Http\Resources;

use App\Models\Study;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Study
 */
class StudyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $status = $this->currentStatus();

        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'title' => $this->title,
            'project_name' => $this->project_name,
            'status' => [
                'value' => $status->value,
                'label' => $status->label(),
            ],
            'payment' => $this->whenLoaded('payment', fn (): ?array => $this->payment ? [
                'reference_number' => $this->payment->reference_number,
                'amount' => $this->payment->amount,
                'currency' => $this->payment->currency,
                'status' => [
                    'value' => $this->payment->status->value,
                    'label' => $this->payment->status->label(),
                ],
            ] : null),
            'customer' => $this->whenLoaded('user', fn (): array => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),
            'completion_percentage' => $this->completion_percentage,
            'is_editable' => $this->isEditable(),
            'days_remaining' => $this->daysRemaining(),
            'expires_at' => $this->expires_at->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
