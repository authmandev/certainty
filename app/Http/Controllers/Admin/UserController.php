<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdminUserResource;
use App\Http\Resources\StudyResource;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * List users with a name or email search (BRD FR-038).
     */
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'role' => ['nullable', Rule::enum(UserRole::class)],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $users = User::query()
            ->withCount('studies')
            ->when($filters['role'] ?? null, fn (Builder $query, string $role) => $query->where('role', $role))
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where(fn (Builder $query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/users/Index', [
            'users' => AdminUserResource::collection($users),
            'filters' => [
                'role' => $filters['role'] ?? null,
                'search' => $filters['search'] ?? null,
            ],
        ]);
    }

    /**
     * Show a user's profile with their studies and payment statuses.
     */
    public function show(User $user): Response
    {
        $user->loadCount('studies');

        return Inertia::render('admin/users/Show', [
            'user' => new AdminUserResource($user),
            'studies' => StudyResource::collection($user->studies()->with('payment')->latest()->get()),
        ]);
    }
}
