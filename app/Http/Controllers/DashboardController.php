<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Show the customer dashboard, sending administrators to the admin panel.
     */
    public function __invoke(Request $request): Response|RedirectResponse
    {
        if ($request->user()->isAdmin()) {
            return to_route('admin.dashboard');
        }

        return Inertia::render('Dashboard');
    }
}
