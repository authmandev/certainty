<?php

namespace App\Http\Controllers;

use App\Http\Resources\StudyResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Show the customer's studies, sending administrators to the admin panel.
     */
    public function __invoke(Request $request): Response|RedirectResponse
    {
        if ($request->user()->isAdmin()) {
            return to_route('admin.dashboard');
        }

        $studies = $request->user()
            ->studies()
            ->with('payment')
            ->latest()
            ->latest('id')
            ->get();

        return Inertia::render('Dashboard', [
            'studies' => StudyResource::collection($studies),
        ]);
    }
}
