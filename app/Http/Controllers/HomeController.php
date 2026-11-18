<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Show the public landing page.
     */
    public function __invoke(): Response
    {
        return Inertia::render('public/Landing', [
            'price' => (float) config('certainty.study_price'),
            'currency' => config('certainty.currency'),
            'draftDurationDays' => (int) config('certainty.draft_duration_days'),
            'contact' => config('certainty.contact'),
        ]);
    }
}
