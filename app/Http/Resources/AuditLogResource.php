<?php

namespace App\Http\Resources;

use App\Models\StudyAuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin StudyAuditLog
 */
class AuditLogResource extends JsonResource
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
            'action' => [
                'value' => $this->action->value,
                'label' => $this->action->label(),
            ],
            'actor' => $this->whenLoaded('actor', fn (): ?array => $this->actor ? [
                'name' => $this->actor->name,
                'is_admin' => $this->actor->isAdmin(),
            ] : null),
            'study' => $this->whenLoaded('study', fn (): array => [
                'id' => $this->study->id,
                'reference' => $this->study->reference,
                'title' => $this->study->title,
            ]),
            'old_values' => $this->old_values,
            'new_values' => $this->new_values,
            'reason' => $this->reason,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
