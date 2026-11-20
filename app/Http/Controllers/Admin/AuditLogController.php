<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\AuditLogResource;
use App\Models\StudyAuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    /**
     * The platform-wide action history (BRD FR-042), newest first.
     */
    public function __invoke(Request $request): Response
    {
        $filters = $request->validate([
            'action' => ['nullable', Rule::enum(AuditAction::class)],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $logs = StudyAuditLog::query()
            ->with(['actor', 'study'])
            ->when($filters['action'] ?? null, fn (Builder $query, string $action) => $query->where('action', $action))
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where(fn (Builder $query) => $query
                ->whereHas('study', fn (Builder $query) => $query->where('reference', 'like', "%{$search}%")->orWhere('title', 'like', "%{$search}%"))
                ->orWhereHas('actor', fn (Builder $query) => $query->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))))
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        return Inertia::render('admin/audit/Index', [
            'logs' => AuditLogResource::collection($logs),
            'filters' => [
                'action' => $filters['action'] ?? null,
                'search' => $filters['search'] ?? null,
            ],
            'actions' => collect(AuditAction::cases())->map(fn (AuditAction $action): array => ['value' => $action->value, 'label' => $action->label()]),
        ]);
    }
}
