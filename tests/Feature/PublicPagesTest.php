<?php

use Inertia\Testing\AssertableInertia as Assert;

test('the landing page shows the configured study price', function () {
    config([
        'certainty.study_price' => 950,
        'certainty.currency' => 'SAR',
        'certainty.draft_duration_days' => 45,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/Landing')
            ->where('price', 950)
            ->where('currency', 'SAR')
            ->where('draftDurationDays', 45),
        );
});

test('guests can read the legal pages', function (string $routeName, string $component) {
    $this->get(route($routeName))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component($component));
})->with([
    'privacy policy' => ['privacy', 'public/Privacy'],
    'terms and conditions' => ['terms', 'public/Terms'],
]);
